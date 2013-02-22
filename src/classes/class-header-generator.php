<?php
class WPPM_Header_Generator {

  var $project_type_name;
  var $project_type;
  var $project_name;
  var $description;
  var $project_uri;
  var $version;
  var $copyright;
  var $author;
  var $author_uri;
  var $license;
  var $license_uri;
  var $license_text;
  var $text_domain;
  var $domain_path;

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
    $required = 'project_type|project_name|description|project_uri|version|author|author_uri|license|license_uri|text_domain|domain_path';
    foreach( explode( '|', $required ) as $item ) {
      if ( empty( $this->$item ) ) {
        $xlate = array(
          'project_type' => 'type',
          'project_name' => 'name',
          'project_uri' => 'url',
        );
        if ( isset( $xlate[$item] ) )
          $item = $xlate[$item];
        $errors[] = 'Required value is empty in wp-package.json: [{$$item}]';
      }
    }

    /**
     * Make sure the license text is not empty.
     */
    if ( empty( $this->license_text ) )
      $errors[] = 'Missing or empty license file: [license.txt]';

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

  function generate_header( $args = array() ) {

    if ( ! isset( $args['validate'] ) || $args['validate'] )
      $this->validate();

    $header =<<<README
<?php
/*
 * Plugin Name: {$this->project_name}
 * Plugin URI: {$this->project_uri}
 * Description: {$this->description}
 * Version: {$this->version}
 * Author: {$this->author}
 * Author URI: {$this->author_uri}
 * Text Domain: {$this->text_domain}
 * License: {$this->license}
 * License: {$this->license_uri}
 *
 * Copyright {$this->copyright}
 *
 * {$this->license_text}
 */
README;
    return $header;
  }
}
