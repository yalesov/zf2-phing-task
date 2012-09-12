<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineProxyTask extends Task
{
    protected $failonerror;

    /**
     * output directory for entity classes
     *
     * @param string $output
     * @return void
     */
    public function setOutput($output)
    {
        if (!is_dir($output)) {
            throw new BuildException(sprintf(
                'Output directory does not exist: %s',
                realpath($output)
            ));
        }
        if (!is_writable($output)) {
            throw new BuildException(sprintf(
                'Output directory is not writable: %s',
                realpath($output)
            ));
        }
        $this->output = realpath($output);
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

        if (count($metadatas)) {
            foreach ($metadatas as $metadata) {
                $this->log(sprintf('Processing entity %s', $metadata->name));
            }

            $em->getProxyFactory()->generateProxyClasses($metadatas, $this->output);

            // Outputting information message
            $this->log(sprintf('Proxy classes generated to %s', $this->output));
        } else {
            $this->log('No metadata classes to process');
        }
    }
}
