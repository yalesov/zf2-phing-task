<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineDropTask extends Task
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

        $tool = new SchemaTool($em);
        $this->log('Dropping database schema...');
        $tool->dropDatabase();
        $this->log('Database schema dropped successfully!');
    }
}
