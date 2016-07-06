<?php
require_once __DIR__ . '/../../../../autoload.php';

use Zend\Mvc\Application;

class ZfTask extends Task
{
  protected $boostrap;
  protected $failonerror;

  /**
   * bootstrap file
   *
   * the bootstrap file must return an instance of Zend\Mvc\Application
   *
   * @param  string $bootstrap
   * @return void
   */
  public function setBootstrap($bootstrap)
  {
    if (!is_file($bootstrap)) {
      throw new BuildException(sprintf(
        'Bootstrap file does not exist: %s',
        realpath($bootstrap)
      ));
    }
    if (!is_readable($bootstrap)) {
      throw new BuildException(sprintf(
        'bootstrap file is not readable: %s',
        realpath($bootstrap)
      ));
    }
    $this->bootstrap = realpath($bootstrap);
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
   * @throws BuildException
   *    if bootstrap file does not return Zend\Mvc\Application
   * @return void
   */
  public function main()
  {
    $this->project->setProperty('zf', $this->bootstrap);
  }
}
