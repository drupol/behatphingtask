<?php

namespace Phing\Behat;

/**
 * Class Option. Represents a Behat CLI option.
 */
class Option {

  /**
   * The option's name.
   *
   * @var string
   *   The option's name.
   */
  protected $name;

  /**
   * The option's value.
   *
   * @var string
   *   The option's value.
   */
  protected $value;

  /**
   * Set the option's name.
   *
   * @param string $str
   *   The option's name.
   */
  public function setName($str) {
    $this->name = (string) $str;
  }

  /**
   * Get the option's name.
   *
   * @return string
   *   The option's name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set the option's value.
   *
   * @param string $str
   *   The option's value.
   */
  public function setValue($str) {
    $this->value = $str;
  }

  /**
   * Set the option's value from a text element.
   *
   * @param string $str
   *   The value of the text element.
   */
  public function addText($str) {
    $this->value = (string) $str;
  }

  /**
   * Get the option's value.
   *
   * @return string
   *   The option's value.
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Get the a representation of the option.
   *
   * @return string
   *   The option as a string.
   */
  public function toString() {
    $name = $this->getName();
    $value = $this->getValue();
    $str = sprintf('--%s', $name);

    if (isset($value) && $value != '') {
      $str = sprintf('%s="%s"', $str, escapeshellcmd($value));
    }

    return $str;
  }

}
