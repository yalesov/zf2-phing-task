<?php
require_once __DIR__ . '/../../../../autoload.php';

use Zend\Mvc\Application;

class TwigTask extends Task
{
    protected $file;
    protected $failonerror;

    /**
     * path to template file, relative to template base directory
     *
     * @param  string $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
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
        static $assetic;
        static $environment;

        if ($assetic === null || $environment === null) {
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

            $sm = $application->getServiceManager();
            $assetic     = $sm->get('assetwig-assetic');
            $environment = $sm->get('assetwig-environment');
        }

        $path = pathinfo($this->file);
        $template = "{$path['dirname']}/{$path['filename']}";

        $this->log(sprintf('Loading %s', $template));
        $assetic->setup($template);
        $environment
            ->prepareRender()
            ->loadTemplate($template);
    }
}
