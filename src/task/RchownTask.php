<?php
require_once __DIR__ . '/../../../../autoload.php';

use Heartsentwined\FileSystemManager\FileSystemManager;

class RchownTask extends Task
{
    protected $file;
    protected $user;
    protected $failonerror = false;

    /**
     * file / dir to rchown to
     *
     * @param string $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = realpath($file);
    }

    /**
     * user to chown to
     *
     * @param string $user
     * @return void
     */
    public function setUser($user)
    {
        $this->user = $user;
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
        if (!$this->file) throw new BuildException('file must be set');
        if (!$this->user) throw new BuildException('user must be set');

        if (strpos($this->user, '.')) {
            list($owner, $group) = explode('.', $this->user);
        } else {
            $owner = $this->user;
        }

        FileSystemManager::rchown($this->file, $owner);
        if (isset($group)) FileSystemManager::rchgrp($this->file, $group);

        if (isset($group)) {
            $this->log(sprintf(
                'Recursively changed file owner on \'%s\' to %s.%s',
                $this->file,
                $owner,
                $group
            ));
        } else {
            $this->log(sprintf(
                'Recursively changed file owner on \'%s\' to %s',
                $this->file,
                $owner
            ));
        }
    }
}
