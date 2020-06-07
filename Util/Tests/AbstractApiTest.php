<?php

namespace EasyApiBundle\Util\Tests;

use GuzzleHttp\Client;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractApiTest extends WebTestCase
{
    use ApiTestRequesterTrait;
    use ApiTestDataLoaderTrait;

    protected static $debug = false;

    // region Constants

    public const USER_TEST_USERNAME = '[API-TESTS]';
    public const USER_TEST_EMAIL = 'api-tests@example.com';
    public const USER_TEST_PASSWORD = 'IloveToBreakYourHopes!';

    public const USER_ADMIN_TEST_USERNAME = '[API-TESTS-ADMIN]';
    public const USER_ADMIN_TEST_EMAIL = 'api-tests-admin@example.com';
    public const USER_ADMIN_TEST_PASSWORD = 'IloveToBreakYourHopes!';

    public const TOKEN_ROUTE_NAME = 'fos_user_security_check';

    public const DEBUG_LEVEL_SIMPLE = 1;
    public const DEBUG_LEVEL_ADVANCED = 2;

    public const ARTIFACT_DIR = DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'artifacts';

    public const initFiles = ['reset-all.yml', 'init.yml'];

    // endregion

    // region Settings

    protected static $artifactTestDir = null;

    protected static $additionalInitFiles = [];

    /**
     * @var int
     */
    protected static $debugLevel = self::DEBUG_LEVEL_ADVANCED;

    /**
     * @var int
     */
    protected static $debugTop = 0;

    /**
     * Symfony env, should be TEST.
     *
     * @var string
     */
    protected static $env = 'TEST';

    /**
     * Indicates if you want launch setup on all tests in your test class.
     *
     * @var bool
     */
    protected static $executeSetupOnAllTest = true;

    /**
     * Indicates if you want launch cleanup on all tests in your test class.
     *
     * @var bool
     */
    protected static $executeCleanupOnAllTest = true;

    /**
     * Indicates if you want launch cleanup on all tests in your test class.
     *
     * @var bool
     */
    protected static $executeCleanupAfterEachTest = false;

    /**
     * Indicates if the first launch need to launch.
     *
     * @var bool
     */
    protected static $launchFirstSetup = true;

    // endregion

    // region Parameters

    /**
     * User API username.
     *
     * @var string
     */
    protected static $user = self::USER_TEST_USERNAME;

    /**
     * User API password.
     *
     * @var string
     */
    protected static $password = self::USER_TEST_PASSWORD;

    /**
     * User API token.
     *
     * @var string
     */
    protected static $token;

    /**
     * API context.
     *
     * @var string
     */
    protected static $context;

    // endregion

    // region Utils

    /**
     * @var array
     */
    protected static $defaultOptions = [
        'exceptions' => false,
    ];

    /**
     * simulates a browser and makes requests to a Kernel object.
     *
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static $client;

    /**
     * HTTP client to call API.
     *
     * @var Client
     */
    protected static $httpClient;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected static $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $entityManager;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected static $router;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    protected static $application;

    /**
     * @var string
     */
    protected static $projectDir;

    /**
     * Check if engine is initialized.
     *
     * @return bool
     */
    final protected static function isInitialized(): bool
    {
        return
            null !== self::$client
            && null !== self::$container
            && null !== self::$entityManager
            && null !== self::$router
            && null !== self::$application
            ;
    }

    /**
     * Initialize engine.
     */
    final protected static function initialize(): void
    {
        self::logStep();
        self::$client = self::createClient(['debug' => false]);
        self::$container = self::$client->getContainer();
        self::$entityManager = self::$container->get('doctrine.orm.entity_manager');
        self::$router = self::$container->get('router');
        self::$application = new Application(self::$container->get('kernel'));
        self::$application->setAutoExit(false);
        self::$projectDir = self::$container->getParameter('kernel.project_dir');

        self::initializeLoader();
        self::initializeRequester();

        global $argv;
        if (in_array('--debug', $argv, true)) {
            self::$debug = true;
        }
    }

    /**
     * Show where you are (Class::method()).
     *
     * @param bool $debugNewLine Adds a new line before debug log
     */
    final protected static function logStep(bool $debugNewLine = false): void
    {
        if (true === static::$debug) {
            $backTrace = debug_backtrace()[1];
            self::logDebug(
                "\e[42;31m[STEP]\e[0m 👁️ \e[92m{$backTrace['class']}::{$backTrace['function']}()\e[0m", self::DEBUG_LEVEL_ADVANCED, $debugNewLine
            );
        }
    }

    /**
     * Show a debug line, if debug activated.
     *
     * @param string $message      The message to log
     * @param int    $debugLevel   Debug level
     * @param bool   $debugNewLine Adds a new line before debug log
     */
    final protected static function logDebug(string $message, int $debugLevel = self::DEBUG_LEVEL_SIMPLE, bool $debugNewLine = false): void
    {
        if (true === static::$debug && $debugLevel <= static::$debugLevel) {
            fwrite(STDOUT,
                ($debugNewLine ? "\n" : '')
                ."\e[33m🐞"
                .((self::DEBUG_LEVEL_ADVANCED === static::$debugLevel) ? ' ['.str_pad(++self::$debugTop, 3, '0', STR_PAD_LEFT).']' : '')
                ."\e[0m"
                ."{$message}\n"
            );
        }
    }

    /**
     * Show an error line.
     *
     * @param $message
     */
    final protected static function logError(string $message): void
    {
        fwrite(STDOUT, "\e[31m✘\e[91m {$message}\e[0m\n");
    }

    /**
     * Count entities.
     *
     * @param string $entityName
     * @param null   $condition
     * @param array  $parameters
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    final protected static function countEntities(string $entityName, $condition = null, $parameters = []): int
    {
        $qb = self::$entityManager->getRepository($entityName)
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
        ;
        if (null !== $condition) {
            $qb->where($condition);
        }
        if (null !== $parameters && !empty($parameters)) {
            $qb->setParameters($parameters);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $className
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected static function getLastEntityId(string $className): int
    {
        $tableName = self::$entityManager->getClassMetadata($className)->getTableName();
        $stmt = self::$entityManager->getConnection()->prepare("SELECT max(id) as id FROM {$tableName}");
        $stmt->execute();

        return (int) $stmt->fetchColumn(0);
    }

    /**
     * @param string $className
     *
     * @return int|null
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected static function getNextEntityId(string $className): ?int
    {
        return ($id = self::getLastEntityId($className)) ? ++$id : null;
    }

    // endregion

    // region User management

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::logStep();
        self::doSetup();

        self::$launchFirstSetup = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::logStep();

        if (true === static::$executeSetupOnAllTest || (false === static::$executeSetupOnAllTest && false === static::$launchFirstSetup)) {
            static::loadData();
        }

        if (true === static::$executeSetupOnAllTest && true === static::$launchFirstSetup) {
            self::doSetup();
        } elseif (true === static::$launchFirstSetup) {
            // If no reset rollback user test & its rights
            self::defineUserPassword();
        }

        static::$launchFirstSetup = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        self::logStep();
        if (true === static::$executeCleanupAfterEachTest) {
            self::doCleanup();
        }
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        self::logStep();
        if (false === static::$executeCleanupOnAllTest) {
            self::doCleanup();
        }
        self::$executeSetupOnAllTest = true;
        self::$executeCleanupOnAllTest = true;

        self::$token = null;
    }

    /**
     * Performs setup operations.
     */
    final protected function doSetup(): void
    {
        self::logStep();
        if (!self::isInitialized()) {
            self::initialize();
        } else {
            self::$entityManager = self::$container->get('doctrine.orm.entity_manager');
        }
    }

    /**
     * Performs cleanup operations.
     */
    final protected static function doCleanup(): void
    {
        self::logStep();
        if (self::isInitialized()) {
            self::loadYaml('reset-all.yml');
        }
    }

    /**
     * Define user & password for tests.
     *
     * @param string|null $user
     * @param string|null $password
     */
    protected static function defineUserPassword($user = null, $password = null): void
    {
        self::logStep();
        if (!self::$user || !$user && !$password) {
            self::$user = self::USER_TEST_USERNAME;
            self::$password = self::USER_TEST_PASSWORD;
        } else {
            self::logDebug("\e[32m[USR]\e[0m😀 New user : \e[32m{$user}\e[0m with password \e[32m{$password}\e[0m");
            self::$user = $user;
            self::$password = $password;
        }

        self::$token = null;
    }

    // endregion

    // region Requests management

    /**
     * Get FileBag for the filename.
     *
     * @param array $filenames
     *
     * @return FileBag
     */
    protected function getFileBag(array $filenames): FileBag
    {
        $fileDir = self::$container->getParameter('kernel.project_dir').
            DIRECTORY_SEPARATOR.'tests'.
            DIRECTORY_SEPARATOR.'artifacts'
        ;
        $fileBag = new FileBag();
        foreach ($filenames as $field => $filename) {
            $fileBag->addFile($field, $fileDir.DIRECTORY_SEPARATOR.$filename, true, $filename);
        }

        return $fileBag;
    }

    // endregion

    // region Assertions

    /**
     * Determine if two arrays are similar.
     *
     * @param array $a
     * @param array $b
     */
    protected static function assertArraysAreSimilar(array $a, array $b): void
    {
        sort($a);
        sort($b);

        static::assertEquals($a, $b);
    }

    /**
     * Determine if two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     */
    protected static function assertAssociativeArraysAreSimilar(array $a, array $b): void
    {
        // Indexes must match
        static::assertCount(count($a), $b, 'The array have not the same size');

        // Compare values
        foreach ($a as $k => $v) {
            static::assertTrue(isset($b[$k]), "The second array have not the key '{$k}'");
            static::assertEquals($v, $b[$k], "Values for '{$k}' key do not match");
        }
    }

    /**
     * Asserts an API problem standard error.
     *
     * @param int       $expectedStatus
     * @param array     $messages
     * @param ApiOutput $apiOutput
     */
    protected static function assertApiProblemError(ApiOutput $apiOutput, int $expectedStatus, array $messages): void
    {
        static::assertEquals($expectedStatus, $apiOutput->getStatusCode());
        $error = $apiOutput->getData();
        static::assertArrayHasKey('errors', $error);
        array_walk($messages, static function (&$message) { $message = ApiProblem::PREFIX.$message; });
        static::assertArraysAreSimilar($messages, $error['errors']);
    }

    /**
     * Asserts an API entity standard result.
     *
     * @param ApiOutput $apiOutput      API output
     * @param int       $expectedStatus Expected status
     * @param array     $data           Expected data (only field or with values
     * @param bool      $onlyFields     Check only fields (check values instead)
     */
    protected static function assertApiEntityResult(ApiOutput $apiOutput, int $expectedStatus, array $data, $onlyFields = true): void
    {
        static::assertEquals($expectedStatus, $apiOutput->getStatusCode());
        if (true === $onlyFields) {
            static::assertFields($data, $apiOutput->getData());
        } else {
            static::assertAssociativeArraysAreSimilar($data, $apiOutput->getData());
        }
    }

    /**
     * Asserts an API entity standard result.
     *
     * @param ApiOutput $apiOutput      API output
     * @param int       $expectedStatus Expected status
     * @param int       $count          List count
     * @param array     $fields         Expected fields
     */
    protected static function assertApiEntityListResult(ApiOutput $apiOutput, int $expectedStatus, int $count, array $fields): void
    {
        static::assertEquals($expectedStatus, $apiOutput->getStatusCode());
        $data = $apiOutput->getData();
        static::assertCount($count, $data, "Expected list size : {$count}, get ".count($data));
        foreach ($data as $entity) {
            static::assertFields($fields, $entity);
        }
    }

    /**
     * Asserts that entity contains exactly theses fields.
     *
     * @param array $fields Expected fields
     * @param array $entity JSON entity as array
     */
    protected static function assertFields(array $fields, array $entity): void
    {
        static::assertNotNull($entity, 'The entity should not be null !');
        static::assertCount(count($fields), $entity, 'Expected field count : '.count($fields).', get '.count($entity));
        foreach ($fields as $field) {
            static::assertArrayHasKey($field, $entity, "Entity must have this field : {$field}");
        }
    }

    // endregion

    /**
     * @return string
     */
    protected static function getArtifactsDir(): string
    {
        $artifactTestDir = static::$artifactTestDir ? DIRECTORY_SEPARATOR.static::$artifactTestDir : '';

        return self::$projectDir.self::ARTIFACT_DIR.$artifactTestDir;
    }

    /**
     * @param string $filename
     *
     * @return bool|string
     */
    protected static function getArtifactFileContent(string $filename)
    {
        return file_get_contents(static::getArtifactsDir().DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * @return string
     */
    protected static function getDomainUrl()
    {
        $scheme = self::$container->getParameter('router.request_context.scheme');
        $host = self::$container->getParameter('router.request_context.host');

        return "{$scheme}://{$host}";
    }

    /**
     * @return KernelInterface
     */
    protected static function getKernel()
    {
        if(null == static::$kernel) {
            static::$kernel = static::createKernel();
        }

        return static::$kernel;
    }
}
