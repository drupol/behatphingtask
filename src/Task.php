<?php

namespace Phing\Behat;

/**
 * A Behat task for Phing.
 * Runs behavior-driven development tests against a codebase.
 */
class Task extends \ExecTask {

  /**
   * The source file from XML attribute.
   *
   * @var \PhingFile
   */
  protected $file;

  /**
   * All fileset objects assigned to this task.
   *
   * @var array
   */
  protected $filesets = array(); //

  /**
   * Path the the Behat executable.
   *
   * @var \PhingFile
   */
  protected $executable = 'behat';

  /**
   * Optional path(s) to execute.
   *
   * @var null
   */
  protected $path = null;

  /**
   * Specify config file to use.
   *
   * @var null
   */
  protected $config = null;

  /**
   * Only executeCall the feature elements which match part
   * of the given name or regex.
   *
   * @var null
   */
  protected $name = null;

  /**
   * Only executeCall the features or scenarios with tags
   * matching tag filter expression.
   *
   * @var null
   */
  protected $tags = null;

  /**
   * Only executeCall the features with actor role matching
   * a wildcard.
   *
   * @var null
   */
  protected $role = null;

  /**
   * Specify config profile to use.
   *
   * @var null
   */
  protected $profile = null;

  /**
   * Only execute a specific suite.
   *
   * @var null
   */
  protected $suite = null;

  /**
   * Passes only if all tests are explicitly passing.
   *
   * @var bool
   */
  protected $strict = false;

  /**
   * Increase verbosity of exceptions.
   *
   * @var bool
   */
  protected $verbose = false;

  /**
   * Force ANSI color in the output.
   *
   * @var bool
   */
  protected $colors = true;

  /**
   * Invokes formatters without executing the tests and hooks.
   *
   * @var bool
   */
  protected $dryRun = false;

  /**
   * Stop processing on first failed scenario.
   *
   * @var bool
   */
  protected $haltonerror = false;

  /**
   * All Behat options to be used to create the command.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Set the path to the Behat executable.
   *
   * @param \PhingFile $str
   *   The executable
   */
  public function setExecutable($str) {
    $this->executable = $str;
  }

  /**
   * Set the path to features to test.
   *
   * @param string $path The path to features.
   *
   * @return void
   */
  public function setPath($path) {
    $this->path = $path;
  }

  /**
   * Sets the Behat config file to use.
   *
   * @param string $config The config file
   *
   * @return void
   */
  public function setConfig($config) {
    $this->config = $config;
  }

  /**
   * Sets the name of tests to run.
   *
   * @param string $name The feature name to match
   *
   * @return void
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Sets the test tags to use.
   *
   * @param string $tags The tag(s) to use
   *
   * @return void
   */
  public function setTags($tags) {
    $this->tags = $tags;
  }

  /**
   * Sets the role able to run tests.
   *
   * @param string $role The actor role to match.
   *
   * @return void
   */
  public function setRole($role) {
    $this->role = $role;
  }

  /**
   * Set the profile to use for tests.
   *
   * @param string $profile The profile to use.
   *
   * @return void
   */
  public function setProfile($profile) {
    $this->profile = $profile;
  }

  /**
   * Set the test suite to use.
   *
   * @param string $suite The suite to use.
   *
   * @return void
   */
  public function setSuite($suite) {
    $this->suite = $suite;
  }

  /**
   * Sets the flag if strict testing should be enabled.
   *
   * @param bool $strict Behat strict mode.
   *
   * @return void
   */
  public function setStrict($strict) {
    $this->strict = StringHelper::booleanValue($strict);
  }

  /**
   * Sets the flag if a verbose output should be used.
   *
   * @param bool $verbose Use verbose output.
   *
   * @return void
   */
  public function setVerbose($verbose) {
    $this->verbose = $verbose;
  }

  /**
   * Either force ANSI colors on or off.
   *
   * @param bool $colors Use ANSI colors.
   *
   * @return void
   */
  public function setColors($colors) {
    $this->colors = StringHelper::booleanValue($colors);
  }

  /**
   * Invokes test formatters without running tests against a site.
   *
   * @param bool $dryrun Run without testing.
   *
   * @return void
   */
  public function setDryRun($dryrun) {
    $this->dryRun = StringHelper::booleanValue($dryrun);
  }

  /**
   * Sets the flag if test execution should stop in the event of a failure.
   *
   * @param bool $stop If all tests should stop on failure.
   *
   * @return void
   */
  public function setHaltonerror($stop) {
    $this->haltonerror = StringHelper::booleanValue($stop);
  }

  /**
   * Options of the Drush command.
   *
   * @return Option
   *   The created option.
   */
  public function createOption() {
    $num = array_push($this->options, new Option());
    return $this->options[$num - 1];
  }

  /**
   * Checks if the Behat executable exists.
   *
   * @param string $executable The path to Behat
   *
   * @return bool
   */
  protected function behatExists($executable) {
    // First check if the executable path is a file.
    if (is_file($executable)) {
      return true;
    }
    // Now check to see if the executable has a path.
    $return = shell_exec('type '.escapeshellarg($executable));

    return (empty($return) ? false : true);
  }

  /**
   * The main entry point method.
   *
   * @throws BuildException
   * @return bool $return
   */
  public function main() {
    $command = array();

    if (!$this->behatExists($this->executable)) {
      throw new BuildException(
        'ERROR: the Behat executable "'.$this->executable.'" does not exist.',
        $this->getLocation()
      );
    }

    /**
     * The Behat binary command.
     */
    $command[] = $this->executable->getAbsolutePath();

    if ($this->path) {
      if (!file_exists($this->path)) {
        throw new BuildException(
          'ERROR: the "'.$this->path.'" path does not exist.',
          $this->getLocation()
        );
      }
    }

    $command[] = !empty($this->path) ? $this->path : '';

    if ($this->config) {
      if (!file_exists($this->config)) {
        throw new BuildException(
          'ERROR: the "'.$this->config.'" config file does not exist.',
          $this->getLocation()
        );
      }

      $option = new Option();
      $option->setName('config');
      $option->addText($this->config);
      $this->options[] = $option;
    }

    if ($this->name) {
      $option = new Option();
      $option->setName('nocolor');
      $option->addText($this->name);
      $this->options[] = $option;
    }

    if ($this->tags) {
      $option = new Option();
      $option->setName('tags');
      $option->addText($this->tags);
      $this->options[] = $option;
    }

    if ($this->role) {
      $option = new Option();
      $option->setName('role');
      $option->addText($this->role);
      $this->options[] = $option;
    }

    if ($this->profile) {
      $option = new Option();
      $option->setName('profile');
      $option->addText($this->profile);
      $this->options[] = $option;
    }

    if ($this->suite) {
      $option = new Option();
      $option->setName('suite');
      $option->addText($this->suite);
      $this->options[] = $option;
    }

    if ($this->strict) {
      $option = new Option();
      $option->setName('strict');
      $this->options[] = $option;
    }

    if ($this->verbose !== false) {
      $option = new Option();
      $option->setName('verbose');
      $option->addText($this->verbose);
      $this->options[] = $option;
    }

    if (!$this->colors) {
      $option = new Option();
      $option->setName('no-colors');
      $this->options[] = $option;
    }

    if ($this->dryRun) {
      $option = new Option();
      $option->setName('dry-run');
      $this->options[] = $option;
    }

    if ($this->haltonerror) {
      $option = new Option();
      $option->setName('stop-on-failure');
      $this->options[] = $option;
    }

    // Contract all options into the form Behat expects.
    foreach ($this->options as $option) {
      $command[] = $option->toString();
    }

    $this->realCommand = implode(' ', $command);

    list($return, $output) = $this->executeCommand();
    $this->cleanup($return, $output);

    if ($this->haltonerror && $return != 0) {
      $outloglevel = $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE;
      foreach ($output as $line) {
        $this->log($line, $outloglevel);
      }

      // Throw an exception if Behat fails.
      throw new BuildException("Behat exited with code $return");
    }

    return $return != 0;
  }

}
