<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineRepoTask extends Task
{
    protected $output;
    protected $filter;
    protected $em;
    protected $failonerror;

    /**
     * output directory for entity classes
     *
     * @param  string $output
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
     * metadata filter
     *
     * @param  string $filter
     * @return void
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * the ServiceLocator identifier of the EntityManager
     *
     * can be either a FQCN, or an alias;
     * must be registered with ZF2's ServiceManager
     *
     * @param  string $em
     * @return void
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * if error occured, whether build should fail
     *
     * @param  bool $value
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

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($this->filter)) {
            $metadatas = MetadataFilter::filter($metadatas, $this->filter);
        }

        if (count($metadatas)) {
            $numRepositories = 0;
            $generator = new EntityRepositoryGenerator();

            foreach ($metadatas as $metadata) {
                if ($metadata->customRepositoryClassName) {
                    $this->log(sprintf('Processing entity %s', $metadata->name));
                    $generator->writeEntityRepositoryClass($metadata->customRepositoryClassName, $this->output);
                    $numRepositories++;
                }
            }

            if ($numRepositories) {
                // Outputting information message
                $this->log(sprintf('Repository classes generated to %s', $this->output));
            } else {
                $this->log('No repository classes were found to be processed');
            }
        } else {
            $this->log('No metadata classes to process');
        }
    }
}
