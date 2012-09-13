<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineUpdateTask extends Task
{
    protected $failonerror;

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
            $wd = getcwd();

            $previousDir = '.';
            while (!file_exists('config/application.config.yml')) {
                $dir = dirname(getcwd());

                if ($previousDir === $dir) {
                    throw new BuildException('Unable to locate "config/application.config.yml"');
                }

                $previousDir = $dir;
                chdir($dir);
            }

            $application = Application::init(Yaml::parse('config/application.config.yml'));
            $em = $application->getServiceManager()->get('doctrine.entitymanager.orm_default');

            chdir($wd);

            /* finish bootstrapping zf2 */
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
