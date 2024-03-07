<?php

namespace EasyApiBundle\Util\Entity;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;

class DatabaseConfigurationLoader
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @throws Exception
     */
    public function load(string $tableName, string $schema = null): array
    {
        $informations = [];
        $informations['columns'] = $this->loadColumns($tableName, $schema);
        $informations['indexes'] = $this->loadIndexes($tableName, $schema);
        $informations['relations'] = $this->loadRelations($tableName, $schema);

        return $informations;
    }

    /**
     * @throws Exception
     */
    protected function loadColumns(string $tableName, string $schema = null): array
    {
        $tableName = $schema ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
        $stmt = $this->em->getConnection()->executeQuery(" DESCRIBE {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    protected function loadIndexes(string $tableName, string $schema = null): array
    {
        $tableName = $schema ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
        $stmt = $this->em->getConnection()->executeQuery("SHOW INDEX FROM {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Foreign Keys having table in target
     */
    protected function loadRelations(string $tableName, string $schema = null): array
    {
        $sqlSchemaConstraint = $schema ? "REFERENCED_TABLE_SCHEMA = '{$schema}' AND " : '';
        $sql = "SELECT TABLE_SCHEMA, TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE {$sqlSchemaConstraint}REFERENCED_TABLE_NAME = '{$tableName}'";

        $stmt = $this->em->getConnection()->executeQuery($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $key => $result) {
            
            if (preg_match("/{$tableName}/", $result['TABLE_NAME'])) {
                $sql = "SELECT TABLE_SCHEMA, TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                WHERE TABLE_SCHEMA = '{$result['TABLE_SCHEMA']}' AND TABLE_NAME = '{$result['TABLE_NAME']}'";
                $foreignKeys = $this->em->getConnection()->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($foreignKeys as $foreignKey) {
                    if ("{$foreignKey['REFERENCED_TABLE_NAME']}_{$tableName}" === $result['TABLE_NAME'] || "{$tableName}_{$foreignKey['REFERENCED_TABLE_NAME']}" === $result['TABLE_NAME']) {
                        $results[$key]['target']['REFERENCED_TABLE_NAME'] = $foreignKey['REFERENCED_TABLE_NAME'];
                        $results[$key]['target']['COLUMN_NAME'] = $foreignKey['COLUMN_NAME'];
                        $results[$key]['target']['REFERENCED_COLUMN_NAME'] = $foreignKey['REFERENCED_COLUMN_NAME'];
                    }
                }
            }
        }

        return $results;
    }
}
