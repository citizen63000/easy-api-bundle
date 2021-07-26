<?php

namespace EasyApiBundle\Util\Tests;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractApiTest extends WebTestCase
{
    use ApiTestRequesterTrait;
    use ApiTestDataLoaderTrait;
    use ApiTestAssertionsTrait;
    use TestUtilsTrait;

    // region Constants

    protected const baseRouteName = null;
    protected const entityClass = null;
    protected const requiredFields = [];
    protected const defaultEntityId = 1;

    public const USER_TEST_ID = 1;
    public const USER_TEST_USERNAME = '[API-TESTS]';
    public const USER_TEST_EMAIL = 'api-tests@example.com';
    public const USER_TEST_PASSWORD = 'IloveToBreakYourHopes!';

    public const USER_ADMIN_TEST_ID = 2;
    public const USER_ADMIN_TEST_USERNAME = '[API-TESTS-ADMIN]';
    public const USER_ADMIN_TEST_EMAIL = 'api-tests-admin@example.com';
    public const USER_ADMIN_TEST_PASSWORD = 'IloveToBreakYourHopes!';

    public const USER_NORULES_ADMIN_TEST_ID = 3;
    public const USER_NORULES_TEST_USERNAME = '[API-TESTS-NO-RULES]';
    public const USER_NORULES_TEST_EMAIL = 'api-tests-no-rules@example.com';
    public const USER_NORULES_TEST_PASSWORD = 'u-norules-pwd';

    public const TOKEN_ROUTE_NAME = 'fos_user_security_check';

    public const DEBUG_LEVEL_SIMPLE = 1;
    public const DEBUG_LEVEL_ADVANCED = 2;

    public const ARTIFACT_DIR = DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'artifacts';

    protected const initFiles = ['init.yml'];

    /** @var string */
    public const regexp_uuid = '[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+';

    /** @var string */
    public const regexp_uid = '[a-zA-Z0-9]+';

    /** @var string[] */
    protected const assessableFunctions = [
        'assertDateTime',
        'assertDate',
        'assertDateTimeNow',
        'assertFileUrl',
        'assertFileName',
        'assertUUID',
    ];

    // endregion

    // region Settings

    protected static $artifactTestDir = null;

    /** @var array */
    protected static $additionalInitFiles = [];

    /** @var array */
    protected static $additionalAssessableFunctions = [];

    /**
     * @var bool
     * @todo set private in php 7.4
     */
    protected static $debug = false;

    /** @var int  */
    protected static $debugLevel = self::DEBUG_LEVEL_ADVANCED;

    /** @var bool  */
    protected static $showQuery = false;

    /** @var int  */
    protected static $debugTop = 0;

    /** @var array */
    public static $defaultTokens = [];

    /**
     * Symfony env, should be TEST.
     * @var string
     */
    protected static $env = 'TEST';

    /**
     * Indicates if you want launch setup on all tests in your test class.
     *
     * @var bool|null
     */
    protected static $executeSetupOnAllTest = null;

    /**
     * Indicates if you want launch setup on all tests in your test class.
     *
     * @var bool
     */
    protected static $loadDataOnSetup = null;

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

    // endregion

    // region Utils

    /**
     * simulates a browser and makes requests to a Kernel object.
     *
     * @var Client
     */
    protected static $client;

    /** @var Container */
    protected static $container;

    /**
     * @var EntityManager
     */
    protected static $entityManager;

    /** @var Router */
    protected static $router;

    /** @var Application */
    protected static $application;

    /** @var string */
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

        static::initExecuteSetupOnAllTest();
        static::initLoadDataOnSetup();
        self::initializeLoader();
        self::initializeRequester();

        global $argv;
        static::$debug = self::$container->getParameter('easy_api.tests.debug') || in_array('--debug', $argv, true);
    }

    /**
     * sf3 polyfill for sf4
     * @return Container|ContainerInterface|null
     */
    protected static function getContainerInstance(): ?ContainerInterface
    {
        return static::$container ?? self::createClient(['debug' => false])->getContainer();
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
            self::logDebug("\e[42;31m[STEP]\e[0m 👁️ \e[92m{$backTrace['class']}::{$backTrace['function']}()\e[0m", self::DEBUG_LEVEL_ADVANCED, $debugNewLine);
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
            fwrite(
                STDOUT,
                ($debugNewLine ? "\n" : '')
                ."\e[33m🐞"
                .((self::DEBUG_LEVEL_ADVANCED === static::$debugLevel) ? ' ['.str_pad(++self::$debugTop, 3, '0', STR_PAD_LEFT).']' : '')
                ."\e[0m"
                ."{$message}\n"
            );
        }
    }

    /**
     * Show an error line and write it in log file
     *
     * @param string $message
     */
    final protected static function logError(string $message): void
    {
        fwrite(STDOUT, "\e[31m✘\e[91m {$message}\e[0m\n");

        try {
            $logger = self::$container->get('logger');
            $logger->err(str_replace("\t", '', $message));
        } catch (\Exception $exception) {
            fwrite(STDOUT, "\e[31m✘\e[91m {$exception->getMessage()}\e[0m\n");
        }
    }

    /**
     * Count entities.
     *
     * @param string $entityName
     * @param null   $condition
     * @param array $parameters
     *
     * @return int
     *
     * @throws NonUniqueResultException|NoResultException
     */
    final protected static function countEntities(string $entityName, $condition = null, array $parameters = []): int
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
     * @param string|null $className
     *
     * @return int
     *
     */
    protected static function getLastEntityId(string $className = null): int
    {
        $tableName = self::$entityManager->getClassMetadata($className ?? static::entityClass)->getTableName();
        $stmt = self::$entityManager->getConnection()->prepare("SELECT max(id) as id FROM {$tableName}");
        $stmt->execute();

        return (int) $stmt->fetchColumn(0);
    }

    /**
     * @param string|null $className
     *
     * @return int|null
     *
     * @throws DBALException
     */
    protected static function getNextEntityId(string $className = null): ?int
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
     * Initialize $executeSetupOnAllTest, override it to change it
     */
    protected static function initExecuteSetupOnAllTest()
    {
        if (null === static::$executeSetupOnAllTest) {
            static::$executeSetupOnAllTest = true;
        }
    }

    /**
     * Initialize $loadDataOnSetup, override it to change it
     */
    protected static function initLoadDataOnSetup()
    {
        if (null === static::$loadDataOnSetup) {
            static::$loadDataOnSetup = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::logStep();

        if (true === static::$loadDataOnSetup && (true === static::$executeSetupOnAllTest || (false === static::$executeSetupOnAllTest && false === static::$launchFirstSetup))) {
            static::loadData();
        }

        if (false === static::isInitialized() || (true === static::$executeSetupOnAllTest && true === static::$launchFirstSetup)) {
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
     * Define user & password for tests.
     *
     * @param string|null $user
     * @param string|null $password
     */
    protected static function defineUserPassword(string $user = null, string $password = null): void
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
        $fileDir = self::$container->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'artifacts';
        $fileBag = new FileBag();
        foreach ($filenames as $field => $filename) {
            $fileBag->addFile($field, $fileDir.DIRECTORY_SEPARATOR.$filename, true, $filename);
        }

        return $fileBag;
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
    protected static function getArtifactFileContent(string $filename): ?string
    {
        return file_get_contents(static::getArtifactsDir().DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * @return array
     */
    protected static function getRequiredFields(): array
    {
        return static::requiredFields;
    }

    /**
     * @return string
     */
    protected static function getDomainUrl(): string
    {
        $scheme = self::$container->getParameter('router.request_context.scheme');
        $host = self::$container->getParameter('router.request_context.host');

        return "{$scheme}://{$host}";
    }

    /**
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        if (null == static::$kernel) {
            static::$kernel = static::createKernel();
        }

        return static::$kernel;
    }
}