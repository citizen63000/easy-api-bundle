<?php

namespace EasyApiBundle\Services\JWS;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Namshi\JOSE\JWS;

class JWSProvider implements JWSProviderInterface
{
    /** @var KeyLoaderInterface */
    private $keyLoader;

    /** @var string */
    private $cryptoEngine;

    /** @var string */
    private $signatureAlgorithm;

    /** @var int */
    private $ttl;

    /** @var string */
    private $authorizationHeaderPrefix;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityManager */
    private $em;

    /** @var string */
    private $userClass;

    /** @var string */
    private $userIdentityField;

    /**
     * @param KeyLoaderInterface    $keyLoader
     * @param string                $cryptoEngine
     * @param string                $signatureAlgorithm
     * @param int                   $ttl
     * @param string                $authorizationHeaderPrefix
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager         $entityManager
     *
     * @throws \InvalidArgumentException If the given algorithm is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, $cryptoEngine, $signatureAlgorithm, $ttl, $authorizationHeaderPrefix, TokenStorageInterface $tokenStorage, EntityManager $entityManager, string $userClass, string $userIdentityField)
    {
        $cryptoEngine = 'openssl' === $cryptoEngine ? 'OpenSSL' : 'SecLib';

        if (!$this->isAlgorithmSupportedForEngine($cryptoEngine, $signatureAlgorithm)) {
            throw new \InvalidArgumentException("The algorithm '{$signatureAlgorithm}' is not supported for {$cryptoEngine}");
        }

        $this->keyLoader = $keyLoader;
        $this->cryptoEngine = $cryptoEngine;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->ttl = $ttl;
        $this->authorizationHeaderPrefix = $authorizationHeaderPrefix;
        $this->tokenStorage = $tokenStorage;
        $this->em = $entityManager;
        $this->userClass = $userClass;
        $this->userIdentityField = $userIdentityField;
    }

    /**
     * @param User $user
     * @return CreatedJWS
     */
    public function generateTokenByUser(User $user)
    {
        $identityGetter = 'get'.ucfirst($this->userIdentityField);
        return $this->create(['roles' => $user->getRoles(), $this->userIdentityField => $user->$identityGetter()]);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload)
    {
        $jws = new JWS([
            'alg' => $this->signatureAlgorithm,
            'typ' => $this->authorizationHeaderPrefix,
        ], $this->cryptoEngine);

        $token = $this->tokenStorage->getToken();
        $user = (null !== $token) ? $token->getUser() : null;

        if (null === $user || (is_string($user) && $user === 'anon.')) {
            $user = $this->em->getRepository($this->userClass)->findOneBy([$this->userIdentityField => $payload[$this->userIdentityField]]);
        }

        if ($user instanceof User) {
            $jws->setPayload($payload + ['exp' => time() + $this->ttl, 'iat' => time(), 'displayName' => $user->__toString()]);
        } else {
            $jws->setPayload($payload);
        }

        $jws->sign(
            $this->keyLoader->loadKey('private'),
            $this->keyLoader->getPassphrase()
        );

        return new CreatedJWS($jws->getTokenString(), $jws->isSigned());
    }

    /**
     * {@inheritdoc}
     */
    public function load($token)
    {
        $jws = JWS::load($token, false, null, $this->cryptoEngine);

        return new LoadedJWS(
            $jws->getPayload(),
            $jws->verify($this->keyLoader->loadKey('public'), $this->signatureAlgorithm)
        );
    }

    /**
     * @param string $cryptoEngine
     * @param string $signatureAlgorithm
     *
     * @return bool
     */
    private function isAlgorithmSupportedForEngine($cryptoEngine, $signatureAlgorithm)
    {
        return class_exists("Namshi\\JOSE\\Signer\\{$cryptoEngine}\\{$signatureAlgorithm}");
    }
}