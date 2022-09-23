<?php

namespace EasyApiBundle\Util\Tests;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Yaml\Parser as YmlParser;

/**
 * Trait ApiTestDataLoaderTrait.
 *
 * @see https://symfony.com/blog/new-in-symfony-3-1-cache-component
 */
trait ApiTestDataLoaderTrait
{
    use APITestCacheManagementTrait;

    /**
     * @var string
     */
    protected static $csvDataFilesPath;

    /**
     * @var array
     */
    protected static $schemas = [];

    /** @var array */
    protected static $referentialsToClean = [];

    protected static function initializeLoader(): void
    {
        self::initializeCache();
        self::$csvDataFilesPath = self::$projectDir.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR;
    }

    /**
     * Load data fixtures.
     *
     * @throws OptimisticLockException|\Exception
     */
    protected static function loadData(): void
    {
        $start = microtime(true);

        self::cleanDb();

        $files = array_merge(static::initFiles, static::$additionalInitFiles);

        foreach ($files as $filename) {
            preg_match('/\.(yml|sql)$/i', $filename, $matches); // can use pathinfo but twice slower
            $extension = $matches[1];

            if ('yml' === $extension) {
                self::loadYaml($filename);
            } elseif ('sql' === $extension) {
                if (true === static::$debug) {
                    self::logDebug("\e[32m[SQL]\e[0m ▶ \e[32m{$filename}\e[0m", self::DEBUG_LEVEL_SIMPLE);
                }

                self::executeSQLQuery(self::getSqlFileContent($filename));
            }
        }

        if (true === static::$debug) {
            $time = microtime(true) - $start;
            self::logDebug("\e[32m[SQL]\e[0m ▶ \e[32mTotal loading time: {$time} seconds\e[0m", self::DEBUG_LEVEL_SIMPLE);
        }
    }

    /**
     * @return int|void
     */
    private static function cleanDb()
    {
        /**
         * @var $stmt Statement
         */
        $stmt = self::$entityManager->getConnection()->executeQuery(self::retrieveNotEmptyTablesQuery());
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if ($stmt->rowCount() > 0) {
            $resetQuery = "SET FOREIGN_KEY_CHECKS = 0;\n";
            foreach ($tables as $table) {
                $resetQuery .= "DELETE FROM {$table};ALTER TABLE {$table} AUTO_INCREMENT=1;";
            }
            $resetQuery .= "\nSET FOREIGN_KEY_CHECKS = 1;";

            if (true === static::$debug) {
                $strTables = implode(', ', $tables);
                self::logDebug("\e[32m[SQL]\e[0m ▶ \e[32mReset {$stmt->rowCount()} tables: {$strTables}\e[0m", self::DEBUG_LEVEL_SIMPLE);
            }

            return self::executeSQLQuery($resetQuery);
        }

        return 0;
    }

    /**
     * @return string
     */
    private static function retrieveNotEmptyTablesQuery(): ?string
    {
        $cachedQuery = self::getCachedData('test.data.reset.not_empty_tables_query');

        if (!$cachedQuery->isHit() || !static::$useCache) {
            if (count(static::$schemas) > 0) {
                $arraySchemas = [];
                foreach (static::$schemas as $schema) {
                    $arraySchemas[] = "'{$schema}'";
                }
                $schemas = implode(',', $arraySchemas);
            }

            $sql = "SELECT CONCAT('`', TABLE_SCHEMA, '`', '.', '`', TABLE_NAME, '`')
                    FROM information_schema.tables
                    WHERE TABLE_SCHEMA IN ({$schemas})
                    AND TABLE_TYPE = 'BASE TABLE'
                    AND TABLE_NAME NOT LIKE 'ref\_%'
                    ";

            if (count(static::$referentialsToClean)) {
                $arrayRefs = [];
                foreach (static::$referentialsToClean as $table) {
                    $arrayRefs[] = "'{$table}'";
                }
                $sqlRefsToClean = implode(',', $arrayRefs);
                $sql .= " OR CONCAT(TABLE_SCHEMA, '.', TABLE_NAME) IN ({$sqlRefsToClean})";
            }

            $stmt = self::$entityManager->getConnection()->executeQuery($sql);
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $listingQueries = [];

            foreach ($tables as $table) {
                $listingQueries[] = "SELECT '{$table}' as `table`, count(1) as nbRows FROM {$table}\n";
            }

            $listingQuery = implode(' UNION ', $listingQueries);
            $cachedQuery->set("SELECT `table` FROM ({$listingQuery}) AS sub WHERE sub.nbRows > 0;");
            self::$cache->save($cachedQuery);
        }

        return $cachedQuery->get();
    }

    /**
     * @param string $filename
     * @throws \Exception
     */
    protected static function loadYaml(string $filename)
    {
        $content = self::parseDataYmlFile($filename);

        foreach ($content as $managerName => $files) {
            foreach ($files as $file => $table) {
                if (true === static::$debug && self::DEBUG_LEVEL_ADVANCED <= static::$debugLevel) {
                    self::logDebug("\e[32m[CSV]\e[0m ▶ \e[32m{$filename} ▶ {$file}\e[0m", self::DEBUG_LEVEL_ADVANCED);
                }

                preg_match('/\.(csv|sql)$/i', $file, $matches); // can use pathinfo but twice slower
                $extDataFile = $matches[1];

                if ('csv' === $extDataFile) {
                    self::executeSQLQuery(self::generateLoadDataQuery($table, $file), false, static::$showQuery, $managerName);
                } elseif ('sql' === $extDataFile) {
                    self::executeSQLQuery(self::getSqlFileContent($file), false, static::$showQuery, $managerName);
                } else {
                    throw new \Exception("Unknow format for file '{$file}'");
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $filename
     *
     * @return mixed
     */
    protected static function generateLoadDataQuery(string $table, string $filename)
    {
        $filePath = self::getDataCsvFilePath($filename);
        $columns = self::getDataCsvFileColumns($filename);

        return "\n LOAD DATA LOCAL INFILE '{$filePath}' INTO TABLE {$table} FIELDS TERMINATED BY ',' ENCLOSED BY '\"' IGNORE 1 LINES ({$columns});";
    }

    /**
     * Execute some SQL statements (Tests purposes ONLY), giving SQL test filename.
     *
     * @param string $filename     SQL script filename
     * @param bool   $debugNewLine Adds a new line before debug log
     */
    final protected static function executeSQLScript(string $filename, bool $debugNewLine = false)
    {
        $sql = file_get_contents(self::$projectDir.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.$filename);

        if (true === static::$debug) {
            self::logDebug("\e[32m[SQL]\e[0m ▶ \e[32m{$filename}\e[0m", self::DEBUG_LEVEL_SIMPLE, $debugNewLine);
        }

        self::executeSQLQuery($sql, $debugNewLine);
    }

    /**
     * Execute SQL query (Tests purposes ONLY), giving SQL query.
     *
     * @param string      $query        SQL query
     * @param bool        $debugNewLine Adds a new line before debug log
     * @param bool        $showQuery    Show query (debug mode)
     * @param string|null $managerName  the manager to use (default manager used if null)
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    final protected static function executeSQLQuery(string $query, bool $debugNewLine = false, bool $showQuery = false, string $managerName = null): void
    {
        $em = null === $managerName ? static::$entityManager : static::getContainerInstance()->get('doctrine')->getManager($managerName);

        if (static::$debug && $showQuery) {
            self::logDebug("\e[32m[SQL]\e[0m ▶ \e[32m{$query}\e[0m");
        }

        try {
            $start = microtime(true);
            $result = $em->getConnection()->exec($query);
            $seconds = microtime(true) - $start;

            // errors
            if (static::$debug) {
                self::logDebug("\t\t🎌 \e[32m{$result}\e[0m affected rows in {$seconds} seconds", self::DEBUG_LEVEL_ADVANCED, $debugNewLine);
                $warnings = $em->getConnection()->executeQuery('SHOW WARNINGS')->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($warnings as $warning) {
                    self::logError("\t\t🎌\t SQl Error: level: {$warning['Level']}, code: {$warning['Code']}, message: {$warning['Message']}, Query: {$query}");
                }
            }
        } catch (\Exception $e) {
            self::logError($e->getMessage());
            // STOP
            exit(E_CORE_ERROR);
        }

        self::$entityManager->flush();
    }

    /**
     * @param string $filename
     *
     * @return bool|string
     */
    protected static function getSqlFileContent(string $filename)
    {
        return file_get_contents(
            self::$projectDir
            .DIRECTORY_SEPARATOR
            .'tests'
            .DIRECTORY_SEPARATOR
            .'data'
            .DIRECTORY_SEPARATOR
            .'csv'
            .DIRECTORY_SEPARATOR
            .$filename
        );
    }

    /**
     * @return bool|string
     */
    protected static function getDataCsvFileColumns(string $filename)
    {
        $path = self::$projectDir.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.$filename;
        $f = fopen($path, 'r');
        $line = str_replace(['"', "\n", ' ', '`'], '', fgets($f));
        fclose($f);

        return implode(',', array_map(function ($column) { return "`$column`"; }, explode(',', $line)));
    }

    /**
     * @param string $filename
     *
     * @return bool|string
     */
    protected static function getDataYmlFileContent(string $filename)
    {
        return file_get_contents(self::$projectDir.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * Parse yml date file.
     *
     * @param string $filename
     *
     * @return array
     */
    protected static function parseDataYmlFile(string $filename): array
    {
        $cachedContent = self::getCachedData("test.data.{$filename}");

        if (!$cachedContent->isHit() || !static::$useCache) {
            $content = (new YmlParser())->parseFile(
                self::$projectDir.
                DIRECTORY_SEPARATOR.'tests'.
                DIRECTORY_SEPARATOR.'data'.
                DIRECTORY_SEPARATOR.'csv'.
                DIRECTORY_SEPARATOR.$filename
            );

            $cachedContent->set($content);
            self::$cache->save($cachedContent);
        }

        return $cachedContent->get();
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    protected static function getDataCsvFilePath(string $fileName)
    {
        return self::$csvDataFilesPath.$fileName;
    }
}
