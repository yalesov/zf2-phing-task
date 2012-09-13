<?php
require_once __DIR__ . '/../../../../autoload.php';

use Heartsentwined\FileSystemManager\FileSystemManager;

class RrmdirTask extends Task
{
    protected $dir;
    protected $failonerror = false;

    /**
     * directory to delete
     *
     * @param string $dir
     * @return void
     */
    public function setDir($dir)
    {
        if (!is_dir($dir)) {
            throw new BuildException(sprintf(
                'Directory does not exist: %s',
                realpath($dir)
            ));
        }
        if (!is_writable($dir)) {
            throw new BuildException(sprintf(
                'Directory is not writable: %s',
                realpath($dir)
            ));
        }
        $this->dir = realpath($dir);
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
        if (!$this->dir) throw new BuildException('dir must be set');

        FileSystemManager::rrmdir($this->dir);

        $this->log(sprintf(
            'Recursively deleted the directory \'%s\'',
            $this->dir
        ));
    }
}
