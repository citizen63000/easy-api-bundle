<?php


namespace EasyApiBundle\Util\Maker;


use Doctrine\ORM\EntityManager;

class DatabaseConfigurationLoader
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * DatabaseConfigurationLoader constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $tableName
     * @param string $schema
     * @return array
     * @throws \Doctrine\DBAL\DBALException
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
     * @param string $tableName
     * @param string $schema
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadColumns(string $tableName, string $schema = null): array
    {
        $tableName = $schema ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
        $stmt = $this->em->getConnection()->executeQuery(" DESCRIBE {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tableName
     * @param string $schema
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadIndexes(string $tableName, string $schema = null): array
    {
        $tableName = $schema ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
        $stmt = $this->em->getConnection()->executeQuery("SHOW INDEX FROM {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tableName
     * @param string|null $schema
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadRelations(string $tableName, string $schema = null): array
    {
        $sql = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_SCHEMA = '{$schema}' AND REFERENCED_TABLE_NAME = '{$tableName}'";

        $stmt = $this->em->getConnection()->executeQuery($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}