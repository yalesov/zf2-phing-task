<?php
require_once __DIR__ . '/../../../../autoload.php';

use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

class DoctrineEntityTask extends Task
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

    $cmf = new DisconnectedClassMetadataFactory();
    $cmf->setEntityManager($em);
    $metadatas = $cmf->getAllMetadata();
    if (!empty($this->filter)) {
      $metadatas = MetadataFilter::filter($metadatas, $this->filter);
    }

    if (count($metadatas)) {
      // Create EntityGenerator
      $entityGenerator = new EntityGenerator();

      $entityGenerator->setGenerateAnnotations(true);
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
