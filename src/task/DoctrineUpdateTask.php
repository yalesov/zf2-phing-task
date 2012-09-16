<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineUpdateTask extends Task
{
    protected $em;
    protected $failonerror;

    /**
     * the ServiceLocator identifier of the EntityManager
     *
     * can be either a FQCN, or an alias;
     * must be registered with ZF2's ServiceManager
     *
     * @param string $em
     * @return void
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * if error occured, whether build should fail
     *
     * @param bool $value
     * @return void
     */
    public function setFailonerror($value)
    {
        $this->failonerror = $value;
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * main method
     *
     * @return void
     */
    public function main()
    {
        static $em;
        if ($em === null) {
            $em = $this->project->getProperty('zf')
                ->getServiceManager()->get($this->em);
        }

        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        if (!empty($metadatas)) {
            $tool = new SchemaTool($em);
            $sqls = $tool->getUpdateSchemaSql($metadatas, false);

            if (0 === count($sqls)) {
                $this->log('Nothing to update - your database is already in sync with the current entity metadata.');
            } else {
                $this->log('Updating database schema...');
                $tool->updateSchema($metadatas, false);
                $this->log(sprintf('Database schema updated successfully! %s queries were executed', count($sqls)));
            }
        } else {
            $this->log('No metadata classes to process');
        }
    }
}
