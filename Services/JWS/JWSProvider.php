<?php

namespace EasyApiBundle\Services\JWS;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Namshi\JOSE\JWS;

class JWSProvider implements JWSProviderInterface
{
    /** @var KeyLoaderInterface */
    protected KeyLoaderInterface $keyLoader;

    /** @var string */
    protected string $cryptoEngine;

    /** @var string */
    protected string $signatureAlgorithm;

    /** @var string */
    protected string $authorizationHeaderPrefix;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string $cryptoEngine
     * @param string $signatureAlgorithm
     * @param string $authorizationHeaderPrefix
     */
    public function __construct(KeyLoaderInterface $keyLoader, string $cryptoEngine, string $signatureAlgorithm, string $authorizationHeaderPrefix)
    {
        $cryptoEngine = 'openssl' === $cryptoEngine ? 'OpenSSL' : 'SecLib';

        if (!$this->isAlgorithmSupportedForEngine($cryptoEngine, $signatureAlgorithm)) {
            throw new \InvalidArgumentException("The algorithm '{$signatureAlgorithm}' is not supported for {$cryptoEngine}");
        }

        $this->keyLoader = $keyLoader;
        $this->cryptoEngine = $cryptoEngine;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->authorizationHeaderPrefix = $authorizationHeaderPrefix;
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