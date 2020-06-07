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
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(string $tableName): array
    {
        $informations = [];
        $informations['columns'] = $this->loadColumns($tableName);
        $informations['indexes'] = $this->loadIndexes($tableName);

        return $informations;
    }

    /**
     * @param string $tableName
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadColumns(string $tableName): array
    {
        $stmt = $this->em->getConnection()->executeQuery("describe {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tableName
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadIndexes(string $tableName): array
    {
        $stmt = $this->em->getConnection()->executeQuery("SHOW INDEX FROM {$tableName}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}