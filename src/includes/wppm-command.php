<?php
/**
 * SCLITP - Simple Command Line Interface Tool via PHP (pronounced like "Split-Pea')
 */
abstract class WPPM_Command {
  var $command;
  var $errors = array();
  var $messages = array();
  function __construct() {
  }
  function get_command_name() {
    $commands = array_flip( WP_Package_Manager::$VALID_COMMANDS );
    return $commands[get_class( $this )];
  }

  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $message = sprintf( 'The %s class has not implemented an execute() method.', get_class( $this ) );
    $wppm->fail( $message );
  }
  function add_error( $error ) {
    $this->errors[] = "\tERROR: {$error}\n";
  }
  function add_notice( $notice ) {
    $this->messages[] = "\tNOTICE: {$notice}\n";
  }
  function add_message( $message ) {
    $this->messages[] = $message;
  }
  function show( $message = false ) {
    if ( ! $message )
      $message = $this->messages;
    if ( count( $message ) ) {
      $message = implode( "\n", $message );
      fwrite( STDOUT, "{$message}\n" );
    }
  }
}

