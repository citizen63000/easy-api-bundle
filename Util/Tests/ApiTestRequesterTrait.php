<?php

namespace EasyApiBundle\Util\Tests;

use EasyApiBundle\Services\JWS\JWSProvider;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

trait ApiTestRequesterTrait
{
    /** @var string */
    protected static $jwtTokenAuthorizationHeaderPrefix;

    /** @var JWSProvider */
    protected static $jwsProvider;

    /** @var bool */
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
     * @param string $token
     * @return string
     */
    protected static function getAuthorizationStringFromToken(string $token): string
    {
        return self::getAuthorizationTokenPrefix() . " {$token}";
    }

    /**
     * Executes a request with a method, an url, a token, a content body and a format.
     *
     * @param string $method HTTP method
     * @param string|array $route Route to call
     * @param array|string $content Content body if needed
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param string $formatIn Input data format <=> Content-type header, see {@link Format} (Default : JSON)
     * @param string $formatOut Output data format <=> Accept header, see {@link Format} (Default : JSON)
     * @param array $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     *
     * @throws \Exception
     *
     * @see https://github.com/DarwinOnLine/symfony-flex-api/blob/master/symfony/tests/AbstractApiTest.php
     * @see https://github.com/DarwinOnLine/symfony-flex-api/blob/master/symfony/src/Utils/ApiOutput.php
     */
    public static function executeRequest(
        string $method,
        $route,
        $content = null,
        ?bool $withToken = true,
        ?string $formatIn = Format::JSON,
        ?string $formatOut = Format::JSON,
        ?array $extraHttpHeaders = []
    ): ApiOutput
    {
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
            $server['HTTP_AUTHORIZATION'] = static::getAuthorizationStringFromToken(static::getToken());
        }

        $body = null !== $content && !($content instanceof FileBag) ? Format::writeData($content, $formatIn) : null;
        $files = ($content instanceof FileBag) ? $content->getFiles() : [];
        $url = \is_string($route) && 0 === mb_strpos($route, 'http') ? $route : self::getUrl($route);

        $client = self::createClient(['debug' => static::$useProfiler]);
        if (static::$useProfiler) {
            $client->enableProfiler();
        }

        $requestBeginTime = microtime(true);
        $client->request($method, $url, [], $files, $server, $body);
        $requestTotalTime = microtime(true) - $requestBeginTime;

        putenv('SHELL_VERBOSITY'); // is set to 3 when kernel debug
        unset($_ENV['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);

        $profiler = $client->getProfile();
        if (!$profiler) {
            if (!static::$container->has('profiler')) {
                throw new \Exception('You must enable the profiler in the configuration to use it.');
            } else {
                throw new \Exception('Impossible to load the profiler in the client.');
            }
        }

        $output = new ApiOutput($client->getResponse(), $formatOut, $profiler);
        $profilerLink = static::getProfilerLink($output);

        self::logDebug(
            "\e[33m[API]\e[0m\t🌐 [\e[33m".strtoupper($method)."\e[0m]".(strlen($method) > 3 ? "\t" : "\t\t")."\e[34m{$url}\e[0m"
            .(self::DEBUG_LEVEL_ADVANCED === static::$debugLevel ? "\n\t\t\tHeaders : \e[33m".json_encode($server, true)."\e[0m" : '')
            .((null !== $content && self::DEBUG_LEVEL_ADVANCED === static::$debugLevel) ? "\n\t\t\tSubmitted data : \e[33m{$body}\e[0m" : '')
            ."\n\t\t\tStatus : \e[33m{$output->getResponse()->getStatusCode()}\e[0m\n\t\t\tResponse : \e[33m{$output->getData(true)}\e[0m\n\t\t\tRequest time : {$requestTotalTime} seconds{$profilerLink}"
        );

        return $output;
    }

    /**
     * @param ApiOutput $output
     * @return string
     */
    protected static function getProfilerLink(ApiOutput $output): string
    {
        if (true === static::$debug && $token = $output->getHeaders()->get('x-debug-token')) {
            return "\e[0m\n\t\t\tProfiler : \e[33m"
                .static::getDomainUrl()
                .'/app_'
                .self::$container->getParameter('kernel.environment')
                .'.php'
                .self::$router->generate('_profiler', ['token' => $token])
                ."\e[0m"
            ;
        }

        return '';
    }

    /**
     * Gets URI from Symfony route.
     *
     * @param string|array $route
     * @param int          $referenceType
     *
     * @return string|null
     */
    protected static function getUrl($route, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH): ?string
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
     * Login via API with a specific user and password.
     *
     * @param string $username
     * @param string $password
     * @param bool $useCache
     * @param bool $useDefaultTokens
     *
     * @return string
     *
     * @throws \Exception
     */
    protected static function loginHttp(string $username, string $password, bool $useCache = true, bool $useDefaultTokens = true): string
    {
        // use default tokens to speedup login or if using external authentication
        if ($useDefaultTokens && isset(static::$defaultTokens[$username])) {
            if (!self::isTokenExpired(static::$defaultTokens[$username])) {
                return static::$defaultTokens[$username];
            }
        }

        // use token in cache or generate it
        $cachedToken = self::getCachedData("test.token.{$username}_{$password}");
        if (!$cachedToken->isHit() || self::isTokenExpired($cachedToken->get()) || !$useCache) {

            $credentials = ['username' => $username, 'password' => $password];

            self::logDebug("\e[32m[USR]\e[0m🔑 Log in with : \e[32m{$username}\e[0m // \e[32m{$password}\e[0m", self::DEBUG_LEVEL_ADVANCED);
            $response = self::executeRequest('POST', ['name' => 'fos_user_security_check'], $credentials, false);
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
     * @return string
     * @throws \Exception
     * @todo possible improvement : cache the generated datetime for the token and compare with this date
     *
     */
    protected static function getToken(): string
    {
        if (null === static::$token) {
            $cachedToken = static::getCachedData('test.token.'.static::$user);
            if (!$cachedToken->isHit() || self::isTokenExpired($cachedToken->get()) || !static::$useCache) {
                static::$token = static::loginHttp(static::$user, static::$password, false);
                $cachedToken->set(static::$token);
                static::$cache->save($cachedToken);
            } else {
                static::$token = $cachedToken->get();
            }
        }

        return static::$token;
    }

    /**
     * @param string $token
     * @return bool
     */
    protected static function isTokenExpired(string $token): bool
    {
        return self::$jwsProvider->load($token)->isExpired();
    }

    /**
     * Executes GET request for an URL with a token to get.
     *
     * @param string|array $route Route to perform the GET
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param string $formatOut Output data format <=> Accept header (Default : JSON)
     * @param array $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpGet($route, bool $withToken = true, string $formatOut = Format::JSON, array $extraHttpHeaders = []): ApiOutput
    {
        return self::executeRequest('GET', $route, null, $withToken, null, $formatOut, $extraHttpHeaders);
    }

    /**
     * @param $route
     * @param $userLogin
     * @param $userPassword
     * @param string $formatOut
     * @param array $extraHttpHeaders
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpGetWithLogin($route, $userLogin, $userPassword, string $formatOut = Format::JSON, array $extraHttpHeaders = []): ApiOutput
    {
        if (null !== $userLogin && null === $userPassword) {
            throw new \Exception('ApiTestRequesterTrait::httpGetWithLogin : $userPassword parameter cannot be null if $userLogin parameters is not null');
        }

        $userLogin = $userLogin ?? static::$user;
        $userPassword = $userPassword ?? static::$password;
        $token = self::loginHttp($userLogin, $userPassword);

        return static::httpGet($route, false, $formatOut, $extraHttpHeaders + ['Authorization' => static::getAuthorizationStringFromToken($token)]);
    }

    /**
     * Executes POST request for an URL with a token to get.
     *
     * @param string|array $route Route to perform the POST
     * @param mixed $content Content to submit
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param string $formatIn Input data format <=> Content-type header (Default : JSON)
     * @param string $formatOut Output data format <=> Accept header (Default : JSON)
     * @param array $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpPost($route, $content = [], bool $withToken = true, string $formatIn = Format::JSON, string $formatOut = Format::JSON, array $extraHttpHeaders = []): ApiOutput
    {
        return self::executeRequest('POST', $route, $content, $withToken, $formatIn, $formatOut, $extraHttpHeaders);
    }

    /**
     * @param $route
     * @param $userLogin
     * @param $userPassword
     * @param array|string $content
     * @param array $extraHttpHeaders
     * @param string $formatIn
     * @param string $formatOut
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpPostWithLogin($route, $userLogin, $userPassword, $content = [], array $extraHttpHeaders = [], string $formatIn = Format::JSON, string $formatOut = Format::JSON): ApiOutput
    {
        if (null !== $userLogin && null === $userPassword) {
            throw new \Exception('ApiTestRequesterTrait::httpPostWithLogin : $userPassword parameter cannot be null if $userLogin parameters is not null');
        }

        $userLogin = $userLogin ?? static::$user;
        $userPassword = $userPassword ?? static::$password;
        $token = self::loginHttp($userLogin, $userPassword);

        return static::httpPost($route, $content, false, $formatIn, $formatOut, $extraHttpHeaders + ['Authorization' => static::getAuthorizationStringFromToken($token)]);
    }

    /**
     * Executes PUT request for an URL with a token to get.
     *
     * @param string|array $route Route to perform the POST
     * @param mixed $content Content to submit
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param string $formatIn Input data format <=> Content-type header (Default : JSON)
     * @param string $formatOut Output data format <=> Accept header (Default : JSON)
     * @param array $extraHttpHeaders Extra HTTP headers to use (can override Accept and Content-Type
     *                                       defined by formatIn and formatOut if necessary)
     *
     * @return ApiOutput
     *
     * @throws \Exception
     */
    public static function httpPut($route, $content = [], bool $withToken = true, string $formatIn = Format::JSON, string $formatOut = Format::JSON, array $extraHttpHeaders = []): ApiOutput
    {
        return self::executeRequest('PUT', $route, $content, $withToken, $formatIn, $formatOut, $extraHttpHeaders);
    }

    /**
     * @param $route
     * @param $userLogin
     * @param $userPassword
     * @param array|string $content
     * @param array $extraHttpHeaders
     * @param string $formatIn
     * @param string $formatOut
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpPutWithLogin($route, $userLogin, $userPassword, $content = [], array $extraHttpHeaders = [], $formatIn = Format::JSON, $formatOut = Format::JSON): ApiOutput
    {
        if (null !== $userLogin && null === $userPassword) {
            throw new \Exception('ApiTestRequesterTrait::httpPutWithLogin : $userPassword parameter cannot be null if $userLogin parameters is not null');
        }

        $userLogin = $userLogin ?? static::$user;
        $userPassword = $userPassword ?? static::$password;
        $token = self::loginHttp($userLogin, $userPassword);

        return static::httpPut($route, $content, false, $formatIn, $formatOut, $extraHttpHeaders + ['Authorization' => static::getAuthorizationStringFromToken($token)]);
    }

    /**
     * Executes DELETE request for an URL with a token to get.
     *
     * @param string|array $route Route to perform the DELETE
     * @param bool $withToken Defines if a token is required or not (need to login first)
     * @param array $extraHttpHeaders
     *
     * @return ApiOutput
     *
     * @throws \Exception
     */
    public static function httpDelete($route, bool $withToken = true, array $extraHttpHeaders = []): ApiOutput
    {
        return self::executeRequest('DELETE', $route, null, $withToken, Format::JSON, Format::JSON, $extraHttpHeaders);
    }

    /**
     * @param $route
     * @param $userLogin
     * @param $userPassword
     * @param array $extraHttpHeaders
     * @return ApiOutput
     * @throws \Exception
     */
    public static function httpDeleteWithLogin($route, $userLogin, $userPassword, array $extraHttpHeaders = []): ApiOutput
    {
        if (null === $userPassword && null !== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        $userLogin = $userLogin ?? static::$user;
        $userPassword = $userPassword ?? static::$password;
        $token = self::loginHttp($userLogin, $userPassword);

        return static::httpDelete($route, false, $extraHttpHeaders + ['Authorization' => static::getAuthorizationStringFromToken($token)]);
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
    public static function execCommand(string $commandName, array $arguments = []): CommandOutput
    {
        $convertedArguments = [];
        foreach ($arguments as $k => $v) {
            if ('--env' !== $k || 'test' === $v) {
                if (!is_int($k)) {
                    $convertedArguments[] = "{$k}='{$v}'";
                } else {
                    $convertedArguments[] = $v;
                }
            } else {
                throw new \Exception("--env option must be test");
            }
        }

        if (!in_array('--env=test', $convertedArguments)) {
            $convertedArguments[] = '--env=test';
        }

        $strArguments = implode(' ', $convertedArguments);
        $projectDir = self::$container->getParameter('kernel.project_dir');

        $requestBeginTime = microtime(true);
        $command = "bin/console {$commandName} {$strArguments}";
        exec("cd {$projectDir} && {$command} 2>&1", $output, $returnCode);
        $requestTotalTime = microtime(true) - $requestBeginTime;

        $commandOutput = new CommandOutput();
        $commandOutput->setStatusCode($returnCode);
        $strOutput = implode("\n", $output);
        $commandOutput->setData($strOutput);

        self::logDebug(
            "\e[33m[API]\e[0m\t🌐 [\e[33m".strtoupper('Exec Command')."\e[0m]\t\t\e[34m{$command}\e[0m"
            ."\n\t\t\tReturn code : \e[33m{$returnCode}\e[0m\n\t\t\tOutput : \e[33m".$strOutput."\e[0m\n\t\t\tExecution time : {$requestTotalTime} seconds\n"
        );

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
    public static function callCommand(string $commandName, array $arguments = []): CommandOutput
    {
        $application = new Application(static::getKernel());
        $application->find($commandName);
        $application->setAutoExit(false);

        foreach ($arguments as $k => $v) {
            if (is_int($k) && '--' !== substr($v, 0, 2)) {
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