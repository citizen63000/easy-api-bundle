<?php

namespace EasyApiBundle\Services\JWS;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Namshi\JOSE\JWS;

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
     * @param int $ttl
     * @param string $authorizationHeaderPrefix
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager $entityManager
     * @param string $userClass
     * @param string $userIdentityField
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
     * @see citizen63000/easy-api-jwt-authentication if you need generate tokens
     * @param array $payload
     * @param array $header
     * @return null
     */
    public function create(array $payload, array $header = [])
    {
        return null;
    }
}