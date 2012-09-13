<?php
require_once __DIR__ . '/../../../../autoload.php';

use Symfony\Component\Yaml\Yaml;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class TwigTask extends Task
{
    protected $file;
    protected $failonerror;

    /**
     * path to template file, relative to template base directory
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
        static $assetic;
        static $environment;

        if ($assetic === null || $environment === null) {
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
            $assetic     = $sm->get('assetwig-assetic');
            $environment = $sm->get('assetwig-environment');

            chdir($wd);

            /* finish bootstrapping zf2 */
        }

        $path = pathinfo($this->file);
        $template = "{$path['dirname']}/{$path['filename']}";

        $this->log(sprintf('Loading %s', $template));
        /*
        $assetic->setup($template);
        $environment->loadTemplate($template);
         */
    }
}
