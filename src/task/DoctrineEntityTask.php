<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineEntityTask extends Task
{
    protected $em;
    protected $failonerror;

    /**
     * EntityManager
     *
     * @param EntityManager $em
     * @return void
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

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
        $this->setEm(
            $application->getServiceManager()->get('doctrine.entitymanager.orm_default'));

        chdir($wd);

        /* finish bootstrapping zf2 */

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($this->em);
        $metadatas = $cmf->getAllMetadata();

        if (count($metadatas)) {
            // Create EntityGenerator
            $entityGenerator = new EntityGenerator();

            $entityGenerator->setGenerateAnnotations(false);
            $entityGenerator->setGenerateStubMethods(true);
            $entityGenerator->setRegenerateEntityIfExists(true);
            $entityGenerator->setUpdateEntityIfExists(true);
            $entityGenerator->setNumSpaces(4);

            foreach ($metadatas as $metadata) {
                $this->log(sprintf('Processing entity %s', $metadata->name));
            }

            // Generating Entities
            $entityGenerator->generate($metadatas, $this->output);

            // Outputting information message
            $this->log(sprintf('Entity classes generated to %s', $this->output));
        } else {
            $this->log('No metadata classes to process');
        }
    }
}
