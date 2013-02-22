<?php
class WPPM_Readme_Generator {

  var $project_name;
  var $contributors;
  var $donate_link;
  var $tags;
  var $requires;
  var $tested;
  var $stable_tag;
  var $license;
  var $license_uri;
  var $license_text;
  var $short_description;
  var $description;
  var $installation;
  var $screenshots;
  var $faq;
  var $changelogs = array();
  var $upgrade_notices = array();

  /**
   * @var WPPM_Logger
   */
  var $LOGGER;

  function __construct( $args = false ) {
    $this->LOGGER = isset( $args['logger'] ) &&  is_object( $args['logger'] ) ?  $args['logger'] : new WPPM_Logger();

    if ( is_array( $args ) )
      $this->initialize( $args );
  }

  function initialize( $args ) {
    foreach( $args as $name => $value )
      if ( property_exists( $this, $name ) ) {
        $this->$name = $value;
      } else {
        $this->LOGGER->warning( "Property [{$name}] does not exist in class " . get_class( $this ) );
      }
  }

  function validate() {
    $errors = array();

    /**
     * Check for each of the required properties to ensure they are not empty.
     */
    $required = 'project_name|contributors|tags|requires|tested|stable_tag|license|license_uri|short_description';
    foreach( explode( '|', $required ) as $item ) {
      if ( empty( $this->$item ) ) {
        $xlate = array(
          'project_name' => 'name',
          'requires' => 'requires_wp',
          'tested' => 'tested_with',
          'stable_tag' => 'version',
          'short_description' => 'description',
        );
        if ( isset( $xlate[$item] ) )
          $item = $xlate[$item];
        $errors[] = 'Required value is empty in wp-package.json: [{$$item}]';
      }
    }

    /**
     * Make sure the requred text sections are not empty.
     */
    $required = 'description|installation';
    foreach( explode( '|', $required ) as $section ) {
      if ( empty( $this->$item ) ) {
        $errors[] = 'Missing or empty section file: [readme/{$section}.txt]';
      }
    }

    /**
     * Make sure the required list section is not empty.
     */
    if ( empty( $this->changelogs ) )
      $errors[] = 'Missing or empty changelog file(s): [readme/changelogs/changelog-*.txt]';

    /**
     * Make sure there is a changelog for the stable tag.
     */
    $stable_tag_regex = '#^= ' . preg_quote( $this->stable_tag ) . ' =#';
    if ( ! preg_match( $stable_tag_regex, $this->changelogs ) )
      $errors[] = "Changelog does not begin with the Stable Tag, should begin with:\n\n\t= {$this->stable_tag} =";

    /**
     * Check if there is an upgrade notice for the stable tag, but don't require it.
     */
    if ( ! preg_match( $stable_tag_regex, $this->upgrade_notices ) )
      $this->LOGGER->warning( "Update Notice does not begin with the Stable Tag, should begin with:\n\n\t= {$this->stable_tag} =" );

    /**
     * If any errors, output and die.
     */
    if ( count( $errors ) ) {
      $errors = implode( "\nERROR: ", $errors ) . "\n";
      if ( method_exists( $this->LOGGER, 'error' ) ) {
        $this->LOGGER->error( $errors );
      } else {
        echo "\nERROR: {$errors}";
        die(1);
      }
    }
    return true;
  }

  function generate_readme( $args = array() ) {

    if ( ! isset( $args['validate'] ) || $args['validate'] )
      $this->validate();

    $donate_link = ! empty( $this->donate_link ) ? "\nDonate link: {$this->donate_link}" : false;

    $screenshots = ! empty( $this->screenshots ) ? "\n\n== Screenshots ==\n\n{$this->screenshots}\n" : false;

    $faq = ! empty( $this->faq ) ? "\n\n=== FAQ ===\n\n{$this->faq}\n" : false;

    $upgrade_notice = ! empty( $this->upgrade_notices  ) ? "\n\n== Upgrade Notice ==\n\n{$this->upgrade_notices}" : false;

    $readme =<<<README
=== {$this->project_name} ===

Contributors: {$this->contributors}{$this->donate_link}
Tags: {$this->tags}
Requires at least: {$this->requires}
Tested up to: {$this->tested}
Stable tag: {$this->stable_tag}
License: {$this->license}
License URI: {$this->license_uri}

{$this->short_description}

== Description ==

{$this->description}

== Installation ==

{$this->installation}{$screenshots}{$faq}
== Changelog ==

{$this->changelog}{$upgrade_notice}
README;
    return $readme;
  }
}
