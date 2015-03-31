<?php
/**
 * @file
 * A Phing task to run Behat commands.
 */

require_once "phing/Task.php";

/**
 * A Behat task. Runs behavior-driven development tests against a codebase.
 *
 * @author  Adam Malone <adam@adammalone.net>
 */
class BehatTask extends Task {

  protected $file;    // the source file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task

  // parameters for behat task
  protected $executable = NULL;
  protected $path = NULL;
  protected $config = NULL;
  protected $name = NULL;
  protected $tags = NULL;
  protected $role = NULL;
  protected $profile = NULL;
  protected $suite = NULL;
  protected $strict = FALSE;
  protected $verbose = FALSE;
  protected $colors = TRUE;
  protected $dryRun = FALSE;
  protected $haltonerror = FALSE;
  protected $output_property = NULL;
  protected $return_property = 0;
  protected $options = array();

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

  /**
   * Sets the Behat config file to use
   *
   * @param string $config The config file
   */
  public function setConfig($config)
  {
    $this->config = $config;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Sets the test tags to use
   *
   * @param string $tags The tag(s) to use
   */
  public function setTags($tags)
  {
    $this->tags = $tags;
  }

  public function setRole($role)
  {
    $this->role = $role;
  }

  public function setProfile($profile)
  {
    $this->profile = $profile;
  }

  public function setSuite($suite)
  {
    $this->suite = $suite;
  }

  /**
   * Sets the flag if strict testing should be enabled.
   * @param boolean $strict
   */
  public function setStrict($strict)
  {
    $this->strict = StringHelper::booleanValue($strict);
  }

  /**
   * Sets the flag if a verbose output should be used.
   * @param boolean $verbose
   */
  public function setVerbose($verbose)
  {
    $this->verbose = StringHelper::booleanValue($verbose);
  }

  /**
   * Either force ANSI colors on or off.
   * @param boolean $colors
   */
  public function setColors($colors)
  {
    $this->colors = StringHelper::booleanValue($colors);
  }

  /**
   * Invokes test formatters without running tests against a site.
   * @param boolean $dryrun
   */
  public function setDryRun($dryrun)
  {
    $this->dryRun = StringHelper::booleanValue($dryrun);
  }

  /**
   * Sets the flag if test execution should stop in the event of a failure.
   * @param boolean $stop
   */
  public function setHaltonerror($stop)
  {
    $this->haltonerror = StringHelper::booleanValue($stop);
  }

  public function setOutputProperty($str) {
    $this->output_property = $str;
  }

  public function setReturnProperty($str) {
    $this->return_property = $str;
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

    // @TODO check behat path exists
    $command[] = !empty($this->executable) ? $this->executable : 'behat';

    // @TODO check each path exists
    $command[] = !empty($this->path) ? $this->path : '';

    if ($this->config) {
      if (!file_exists($this->config)) {
        throw new BuildException(
          'ERROR: the "' . $this->config . '" config file does not exist.',
          $this->getLocation()
        );
      }

      $this->options['config'] = $this->config;
    }

    if ($this->name) {
      $this->options['name'] = $this->name;
    }

    if ($this->tags) {
      $this->options['tags'] = $this->tags;
    }

    if ($this->role) {
      $this->options['role'] = $this->role;
    }

    if ($this->profile) {
      $this->options['profile'] = $this->profile;
    }

    if ($this->suite) {
      $this->options['suite'] = $this->suite;
    }

    if ($this->strict) {
      $this->options[] = 'strict';
    }

    if ($this->verbose) {
      $this->options[] = 'verbose';
    }

    if (!$this->colors) {
      $this->options[] = 'no-colors';
    }

    if ($this->dryRun) {
      $this->options[] = 'dry-run';
    }

    if ($this->haltonerror) {
      $this->options[] = 'stop-on-failure';
    }

    foreach ($this->options as $name => $value) {
      $command[] = $this->createOption($name, $value);
    }
    $command = implode(' ', $command);
    $this->log("Running '$command'");

    // Run Behat.
    $output = array();
    exec($command, $output, $return);

    // Collect Behat output for display through the Phing log.
    foreach ($output as $line) {
      $this->log($line);
    }

    if (!empty($this->output_property)) {
      $this->getProject()
        ->setProperty($this->output_property, implode("\n", $output));
    }

    if (!empty($this->return_property)) {
      $this->getProject()
        ->setProperty($this->return_property, $return);
    }

    // Throw an exception if behat fails.
    if ($this->haltonerror && $return != 0) {
      throw new BuildException("Behat exited with code $return");
    }

    return $return != 0;

  }

}