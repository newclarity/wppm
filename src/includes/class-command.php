<?php

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
  function execute() {
    $message = sprintf( 'The %s class has not implemented an execute() method.', get_class( $this ) );
    WP_Package_Manager::fail( $message );
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
      $message = implode( "\n\t", $this->messages );
    fwrite( STDOUT, "{$message}\n" );
  }
}

