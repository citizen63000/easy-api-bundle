<?php

namespace EasyApiBundle\Util\Tests;

use EasyApiBundle\Services\JWS\JWSProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

trait ApiTestRequesterTrait
{
    /**
     * @var string
     */
    protected static $jwtTokenAuthorizationHeaderPrefix;

    /**
     * @var JWSProvider
     */
    protected static $jwsProvider;

    /**
     * @var bool
     */
    protected static $useProfiler = false;

    /**
     * Initialize parameters to make requests.
     */
    protected static function initializeRequester(): void
    {
        self::initializeCache();
        self::$jwtTokenAuthorizationHeaderPrefix = self::$container->getParameter('jwt_token_authorization_header_prefix');
        self::$jwsProvider = self::$container->get('app.jwt_authentication.jws_provider');
    }

    /**
     * @return mixed
     */
    protected static function getAuthorizationTokenPrefix(): string
    {
        return self::$jwtTokenAuthorizationHeaderPrefix;
    }

    /**
     * Executes a request with a method, an url, a token, a content body and a format.
     *
     * @param string $method HTTP method
     * @param string|array $route Route to call
     * @param mixed $content Content body if needed
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param string $formatIn Input data format <=> Content-type header, see {@link Format} (Default : JSON)
     * @param string $formatOut Output data format <=> Accept header, see {@link Format} (Default : JSON)
     * @param array $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     * @param bool $useHttpClient use httpClient instead of Symfony\Bundle\FrameworkBundle\Client
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     *
     * @see https://github.com/DarwinOnLine/symfony-flex-api/blob/master/symfony/tests/AbstractApiTest.php
     * @see https://github.com/DarwinOnLine/symfony-flex-api/blob/master/symfony/src/Utils/ApiOutput.php
     */
    public static function executeRequest(string $method, $route, $content = null, bool $withToken = true,
                                          $formatIn = Format::JSON, $formatOut = Format::JSON,
                                          array $extraHttpHeaders = [], bool $useHttpClient = false)
    {
        // legacy http client
        if ($useHttpClient) {
            return static::executeHttpRequest($method, $route, $content, $withToken, $formatIn, $formatOut, $extraHttpHeaders);
        }

        //Headers initialization
        $server = [];

        if (null !== $formatIn && !($content instanceof FileBag)) {
            $server['CONTENT_TYPE'] = $formatIn;
        }

        if (null !== $formatOut) {
            $server['HTTP_ACCEPT'] = $formatOut;
        }

        foreach ($extraHttpHeaders as $key => $value) {
            if ('content-type' === mb_strtolower($key)) {
                $server['CONTENT_TYPE'] = $value;
                continue;
            } elseif ('authorization' === mb_strtolower($key)) {
                $server['HTTP_AUTHORIZATION'] = $value;
            }
            $server['HTTP_'.mb_strtoupper(str_replace('-', '_', $key))] = $value;
        }

        // Token
        if (true === $withToken) {
            $server['HTTP_AUTHORIZATION'] = self::$jwtTokenAuthorizationHeaderPrefix.' '.static::getToken();
        }

        $body = null !== $content && !($content instanceof FileBag) ? Format::writeData($content, $formatIn) : null;
        $files = ($content instanceof FileBag) ? $content->getData() : [];
        $url = \is_string($route) && 0 === mb_strpos($route, 'http') ? $route : self::getUrl($route);

        //@todo see how is possible to use
        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = self::createClient(['debug' => static::$useProfiler]);
        if (static::$useProfiler) {
            $client->enableProfiler();
        }

        $client->request($method, $url, [], $files, $server, $body);

        putenv('SHELL_VERBOSITY'); // is set to 3 when kernel debug
        unset($_ENV['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);

        $profiler = $client->getProfile();
        if(!$profiler) {
            if(!static::$container->has('profiler')) {
                throw new \Exception('You must enable the profiler in the configuration to use it.');
            } else {
                throw new \Exception('Impossible to load the profiler in the client.');
            }
        }

        $output = new ApiOutput($client->getResponse(), $formatOut, $profiler);

        self::logDebug(
            "\e[33m[API]\e[0m\t🌐 [\e[33m".strtoupper($method)."\e[0m]".(strlen($method) > 3 ? "\t" : "\t\t")."\e[34m{$url}\e[0m"
            .((null !== $content && self::DEBUG_LEVEL_ADVANCED === static::$debugLevel) ? "\n\t\t\tSubmitted data : \e[33m{$body}\e[0m" : '')
            ."\n\t\t\tStatus : \e[33m".$output->getResponse()->getStatusCode()
            ."\e[0m\n\t\t\tResponse : \e[33m".$output->getData(true)."\e[0m"
        );

        return $output;
    }

    /**
     * Executes a request with a method, an url, a token, a content body and a format.
     *
     * @param string       $method           HTTP method
     * @param string|array $route            Route to call
     * @param mixed        $content          Content body if needed
     * @param bool         $withToken        Defines if a token is required or not (need to login first)
     * @param string       $formatIn         Input data format <=> Content-type header, see {@link Format} (Default : JSON)
     * @param string       $formatOut        Output data format <=> Accept header, see {@link Format} (Default : JSON)
     * @param array        $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function executeHttpRequest(string $method, $route, $content = null, bool $withToken = true,
                                              $formatIn = Format::JSON, $formatOut = Format::JSON, array $extraHttpHeaders = [])
    {
        //Headers initialization
        $httpHeaders = [];
        if (null !== $formatIn && !($content instanceof FileBag)) {
            $httpHeaders['Content-Type'] = $formatIn;
        }
        if (null !== $formatOut) {
            $httpHeaders['Accept'] = $formatOut;
        }
        $httpHeaders = array_merge($httpHeaders, $extraHttpHeaders);

        // Token
        if (true === $withToken) {
            $httpHeaders['Authorization'] = self::$jwtTokenAuthorizationHeaderPrefix.' '.static::getToken();
        }

        $options = [
            'headers' => $httpHeaders,
        ];

        $options = array_merge(self::$defaultOptions, $options);

        // Body
        if ($content instanceof FileBag) {
            $options['multipart'] = $content->getData();
        }

        if (null !== $content) {
            $options['body'] = Format::writeData($content, $formatIn);
        }

        $url = is_string($route) && 0 === mb_strpos($route, 'http') ? $route : self::getUrlForHttpClient($route);
        $output = ApiOutput::createFromResponseInterface(self::getHttpClient()->request($method, $url, $options), $formatOut);

        self::logDebug(
            "\e[33m[API]\e[0m\t🌐 [\e[33m".strtoupper($method)."\e[0m]".(strlen($method) > 3 ? "\t" : "\t\t")."\e[34m".$url."\e[0m"
            .((null !== $content && self::DEBUG_LEVEL_ADVANCED === static::$debugLevel) ? "\n\t\t\tSubmitted data : \e[33m{$options['body']}\e[0m" : '')
            ."\n\t\t\tStatus : \e[33m".$output->getResponse()->getStatusCode()
            ."\e[0m\n\t\t\tResponse : \e[33m".$output->getData(true)."\e[0m"
        );

        return $output;
    }

    /**
     * Gets URI from Symfony route.
     *
     * @param string|array $route
     * @param int          $referenceType
     *
     * @return string
     */
    protected static function getUrl($route, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH)
    {
        if (is_array($route)) {
            $routeName = $route['name'] ?? '';
            $routeParams = $route['params'] ?? [];
            $url = $route['url'] ?? null;
        } else {
            $routeName = $route;
            $routeParams = [];
            $url = null;
        }

        return $url ?? self::$router->generate($routeName, $routeParams, $referenceType);
    }

    /**
     * @return Client
     */
    protected static function getHttpClient()
    {
        if (null === self::$httpClient) {
            self::$httpClient = new Client(['verify' => false]);
        }

        return self::$httpClient;
    }

    /**
     * @param $route
     *
     * @return string
     */
    protected static function getUrlForHttpClient($route)
    {
        $oldBaseUrl = self::$router->getContext()->getBaseUrl();

        if (in_array(strtolower(self::$env), ['dev', 'test'])) {
            self::$router->getContext()->setBaseUrl('/app_'.strtolower(self::$env).'.php');
        }

        $url = self::getUrl($route, UrlGeneratorInterface::ABSOLUTE_URL);

        if (in_array(strtolower(self::$env), ['dev', 'test'])) {
            self::$router->getContext()->setBaseUrl($oldBaseUrl);
        }

        return $url;
    }

    /**
     * Login via API with a specific user and password.
     *
     * @param string $username
     * @param string $password
     * @param bool   $useCache
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    protected static function loginHttp(string $username, string $password, bool $useCache = true)
    {
        if (null === static::$context) {
            throw new \Exception('The API context must be defined');
        }

        $cachedToken = self::getCachedData("test.token.{$username}_{$password}");
        if (!$cachedToken->isHit() || self::isTokenExpired($cachedToken->get()) || !$useCache) {
            $credentials = [
                'username' => $username,
                'password' => $password,
            ];

            self::logDebug("\e[32m[USR]\e[0m🔑 Log in with : \e[32m{$username}\e[0m // \e[32m{$password}\e[0m", self::DEBUG_LEVEL_ADVANCED);
            $response = self::executeRequest('POST', [
                'name' => 'fos_user_security_check',
                'params' => ['context' => static::$context],
            ], $credentials, false);
            $tokenAuth = $response->getData();

            if (null === $tokenAuth) {
                throw new \Exception('Tests : Token is null');
            } elseif (Response::HTTP_UNAUTHORIZED === $response->getStatusCode()) {
                self::logError('Unable to get token : Bad credentials');
                throw new \Exception('Tests : Unable to get token : Bad credentials');
            }
            self::logDebug("\e[35m[TKN]\e[0m\t\e[92m✔\e[0m Generated Token : \e[35m{$tokenAuth['token']}\e[0m");

            $cachedToken->set($tokenAuth['token']);
            self::$cache->save($cachedToken);
        }

        return $cachedToken->get();
    }

    /**
     * Get authentication token.
     *
     * @todo possible improvement : cache the generated datetime for the token and compare with this date
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected static function getToken(): string
    {
        if (null === static::$token) {
            $cachedToken = self::getCachedData('test.token.'.self::$user);
            if (!$cachedToken->isHit() || self::isTokenExpired($cachedToken->get()) || !static::$useCache) {
                static::$token = static::loginHttp(self::$user, self::$password, false);
                $cachedToken->set(static::$token);
                self::$cache->save($cachedToken);
            } else {
                static::$token = $cachedToken->get();
            }
        }

        return static::$token;
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    private static function isTokenExpired(string $token): bool
    {
        return self::$jwsProvider->load($token)->isExpired();
    }

    /**
     * Executes GET request for an URL with a token to get.
     *
     * @param string|array $route            Route to perform the GET
     * @param bool         $withToken        Defines if a token is required or not (need to login first)
     * @param string       $formatOut        Output data format <=> Accept header (Default : JSON)
     * @param array        $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpGet($route, bool $withToken = true,
                                   $formatOut = Format::JSON, array $extraHttpHeaders = [])
    {
        return self::executeRequest('GET', $route, null, $withToken, null, $formatOut, $extraHttpHeaders);
    }

    /**
     * Executes POST request for an URL with a token to get.
     *
     * @param string|array $route            Route to perform the POST
     * @param mixed        $content          Content to submit
     * @param bool         $withToken        Defines if a token is required or not (need to login first)
     * @param string       $formatIn         Input data format <=> Content-type header (Default : JSON)
     * @param string       $formatOut        Output data format <=> Accept header (Default : JSON)
     * @param array        $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     * @param bool         $useHttpClient
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpPost($route, $content = [], bool $withToken = true,
                                    $formatIn = Format::JSON, $formatOut = Format::JSON, array $extraHttpHeaders = [], $useHttpClient = false)
    {
        return self::executeRequest('POST', $route, $content, $withToken, $formatIn, $formatOut, $extraHttpHeaders, $useHttpClient);
    }

    /**
     * Executes PUT request for an URL with a token to get.
     *
     * @param string|array $route            Route to perform the POST
     * @param mixed        $content          Content to submit
     * @param bool         $withToken        Defines if a token is required or not (need to login first)
     * @param string       $formatIn         Input data format <=> Content-type header (Default : JSON)
     * @param string       $formatOut        Output data format <=> Accept header (Default : JSON)
     * @param array        $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpPut($route, $content = [], bool $withToken = true,
                                   $formatIn = Format::JSON, $formatOut = Format::JSON, array $extraHttpHeaders = [])
    {
        return self::executeRequest('PUT', $route, $content, $withToken, $formatIn, $formatOut, $extraHttpHeaders);
    }

    /**
     * Executes DELETE request for an URL with a token to get.
     *
     * @param string|array $route            Route to perform the DELETE
     * @param bool         $withToken        Defines if a token is required or not (need to login first)
     * @param array        $extraHttpHeaders
     *
     * @return ApiOutput
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpDelete($route, bool $withToken = true, array $extraHttpHeaders = [])
    {
        return self::executeRequest('DELETE', $route, null, $withToken, Format::JSON, Format::JSON, $extraHttpHeaders);
    }

    /**
     * Execute command nativly by changing current directory to be on root project directory
     *
     * @param string $commandName ex "generator:entity:full"
     * @param array $arguments ex : "['customer_task', 'CustomerTask', 'APITaskBundle', 'Task', '--no-dump', '--target' => '{ti}']"
     *
     * @return CommandOutput
     *
     * @throws \Exception
     */
    public static function execCommand(string $commandName, array $arguments = [])
    {
        $convertedArguments = [];
        foreach ($arguments as $k => $v) {
            if('--env' !== $k || 'test' === $v) {
                if(!is_int($k)) {
                    $convertedArguments[] = "{$k}='{$v}'";
                } else {
                    $convertedArguments[] = $v;
                }
            } else {
                throw new \Exception("--env option must be test");
            }
        }

        if(!in_array('--env=test', $convertedArguments)) {
            $convertedArguments[] = '--env=test';
        }

        $strArguments = implode(' ', $convertedArguments);
        $projectDir = self::$container->getParameter('kernel.project_dir');
        exec("cd {$projectDir} && bin/console {$commandName} {$strArguments} 2>&1", $output, $returnCode);

        $commandOutput = new CommandOutput();
        $commandOutput->setStatusCode($returnCode);
        $commandOutput->setData(implode("\n", $output));

        return $commandOutput;
    }

    /**
     * Call command by using Symfony (be careful the current directory is not the root directory of the project
     *
     * @param string $commandName ex: "generator:entity:full"
     * @param array $arguments ex: "['table_name' => 'customer_task', 'entity_name'=> 'CustomerTask', ... '--no-dump','--target' => '{ti}']"
     *
     * @return CommandOutput
     *
     * @throws \Exception
     */
    public static function callCommand(string $commandName, array $arguments = [])
    {
        $application = new Application(static::getKernel());
        $application->find($commandName);
        $application->setAutoExit(false);

        foreach ($arguments as $k => $v) {
            if(is_int($k) && '--' !== substr($v, 0, 2)) {
                throw new \Exception("you must pass the parameter name for value {$v}");
            }
        }

        $input = new ArrayInput(array_merge(['command' => $commandName], $arguments));

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $statusCode = $application->run($input, $output);

        $commandOutput = new CommandOutput();
        $commandOutput->setStatusCode($statusCode);
        $commandOutput->setData($output->fetch());

        return $commandOutput;
    }
}