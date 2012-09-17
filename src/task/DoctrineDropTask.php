<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\SchemaTool;
use Heartsentwined\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineDropTask extends Task
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
            $wd = getcwd();
            $zf = $this->project->getProperty('zf');
            $application = require $zf;
            if (!$application instanceof Application) {
                throw new BuildException(sprintf(
                    'zf bootstrap file "%s" should return an instance of Zend\Mvc\Application',
                    $zf
                ));
            }
            chdir($wd);

            $em = $application->getServiceManager()->get($this->em);
        }

        $tool = new SchemaTool($em);
        $this->log('Dropping database schema...');
        $tool->dropDatabase();
        $this->log('Database schema dropped successfully!');
    }
}
