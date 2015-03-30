<?php

/**
 * @file
 * A Phing task to run Behat commands.
 */
require_once "phing/Task.php";

class BehatTask extends Task {

  private $path = NULL;
  private $executable = NULL;
  private $config = NULL;
  private $stoponfailure = FALSE;
  private $strict = FALSE;
  private $verbose = FALSE;
  private $colors = TRUE;
  private $dryrun = FALSE;
  private $return_property = 0;
  private $options = array();

  /**
   * Path the Behat executable.
   */
  public function setexecutable($str) {
    $this->executable = $str;
  }

  /**
   * Set the path to features to test.
   */
  public function setPath($path) {
    $this->path = $path;
  }

  public function setReturnProperty($str) {
    $this->return_property = $str;
  }

  /**
   * Display extra information about the command.
   */
  public function setVerbose($var) {
    if (is_string($var)) {
      $this->verbose = ($var === 'yes');
    } else {
      $this->verbose = !!$var;
    }
  }

  public function setStoponfailure($var) {
    if (is_string($var)) {
      $this->stoponfailure = ($var === 'yes');
    } else {
      $this->stoponfailure = !!$var;
    }
  }

  public function setStrict($var) {
    if (is_string($var)) {
      $this->strict = ($var === 'yes');
    } else {
      $this->strict = !!$var;
    }
  }


  protected function createOption($name, $value) {
    if (is_numeric($name)) {
      return '--' . $value;
    }

    return '--' . $name . '=' . $value;
  }

  /**
   * The init method: Do init steps.
   */
  public function init() {
  }

  /**
   * The main entry point method.
   */
  public function main() {
    $command = array();

    $command[] = !empty($this->executable) ? $this->executable : 'behat';
    $command[] = !empty($this->path) ? $this->path : '';

    if ($this->dryrun) {
      $this->options[] = 'dry-run';
    }

    if ($this->verbose) {
      $this->options[] = 'verbose';
    }

    if (!$this->colors) {
      $this->options[] = 'no-colors';
    }

    if ($this->strict) {
      $this->options[] = 'strict';
    }

    if ($this->stoponfailure) {
      $this->options[] = 'stop-on-failure';
    }

    if ($this->config) {
      $this->options['config'] = $this->config;
    }

    foreach ($this->options as $name => $value) {
      $command[] = $this->createOption($name, $value);
    }
    $command = implode(' ', $command);

    $this->log("Executing $command");
    $output = array();
    exec($command, $output, $return);
    foreach ($output as $line) {
      $this->log($line);
    }
    if (!empty($this->return_property)) {
      $this->getProject()->setProperty($this->return_property, implode("\n", $output));
    }
    // Build fail.
    if ($this->stoponfailure && $return != 0) {
      throw new BuildException("Behat exited with code $return");
    }
    return $return != 0;

  }

}
