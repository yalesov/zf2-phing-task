<?php
require_once __DIR__ . '/../../../../autoload.php';

use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineEntityTask extends Task
{
    protected $baseDir;
    protected $file;
    protected $failonerror;

    /**
     * base directory for file
     *
     * @param string $baseDir
     * @return void
     */
    public function setBaseDir($baseDir)
    {
        if (!is_dir($baseDir)) {
            throw new BuildException(sprintf(
                'baseDir directory does not exist: %s',
                realpath($baseDir)
            ));
        }
        if (!is_writable($baseDir)) {
            throw new BuildException(sprintf(
                'baseDir directory is not writable: %s',
                realpath($baseDir)
            ));
        }
        $this->baseDir = realpath($baseDir);
    }

    /**
     * path to template file
     *
     * @param string $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
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
        static $sm;

        if ($sm === null) {
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
            $sm = $application->getServiceManager();

            chdir($wd);

            /* finish bootstrapping zf2 */
        }

        $assetic = $sm->get('assetwig-assetic');
        $environment = $sm->get('assetwig-environment');

        $this->log('DIR IS ' . $this->baseDir);
        $this->log('FILE IS ' . $this->file);
        return ;

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
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
