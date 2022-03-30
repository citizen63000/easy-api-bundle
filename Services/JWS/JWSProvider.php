<?php

namespace EasyApiBundle\Services\JWS;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Namshi\JOSE\JWS;
use Symfony\Component\Security\Core\User\UserInterface;

class JWSProvider implements JWSProviderInterface
{
    /** @var KeyLoaderInterface */
    protected KeyLoaderInterface $keyLoader;

    /** @var string */
    protected string $cryptoEngine;

    /** @var string */
    protected string $signatureAlgorithm;

    /** @var int */
    protected int $ttl;

    /** @var string */
    protected string $authorizationHeaderPrefix;

    /** @var TokenStorageInterface */
    protected TokenStorageInterface $tokenStorage;

    /** @var EntityManager */
    protected EntityManager $em;

    /** @var string */
    protected string $userClass;

    /** @var string */
    protected string $userIdentityField;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string $cryptoEngine
     * @param string $signatureAlgorithm
     * @param string $authorizationHeaderPrefix
     */
    public function __construct(KeyLoaderInterface $keyLoader, string $cryptoEngine, string $signatureAlgorithm, int $ttl, string $authorizationHeaderPrefix, TokenStorageInterface $tokenStorage, EntityManager $entityManager, string $userClass, string $userIdentityField)
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
     * {@inheritdoc}
     */
    public function load($token): LoadedJWS
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
    private function isAlgorithmSupportedForEngine(string $cryptoEngine, string $signatureAlgorithm): bool
    {
        return class_exists("Namshi\\JOSE\\Signer\\{$cryptoEngine}\\{$signatureAlgorithm}");
    }

    /**
     * @param array $payload
     * @param array $header
     * @return null
     */
    public function create(array $payload, array $header = [])
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

        if ($user instanceof UserInterface) {
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
}