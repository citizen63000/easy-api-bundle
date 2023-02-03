<?php

namespace EasyApiBundle\Services\JWS;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Namshi\JOSE\JWS;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JWSProvider implements JWSProviderInterface
{
    protected KeyLoaderInterface $keyLoader;

    protected string $cryptoEngine;

    protected string $signatureAlgorithm;

    protected int $ttl;

    protected string $authorizationHeaderPrefix;

    protected TokenStorageInterface $tokenStorage;

    protected EntityManager $em;

    protected string $userClass;

    protected string $userIdentityField;

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

    private function isAlgorithmSupportedForEngine(string $cryptoEngine, string $signatureAlgorithm): bool
    {
        return class_exists("Namshi\\JOSE\\Signer\\{$cryptoEngine}\\{$signatureAlgorithm}");
    }

    public function create(array $payload, array $header = []): ?CreatedJWS
    {
        $jws = new JWS([
            'alg' => $this->signatureAlgorithm,
            'typ' => $this->authorizationHeaderPrefix,
        ], $this->cryptoEngine);

        $token = $this->tokenStorage->getToken();
        $user = (null !== $token) ? $token->getUser() : null;

        if (null === $user || (is_string($user) && 'anon.' === $user)) {
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
