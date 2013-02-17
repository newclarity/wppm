<?php

abstract class WPPM_Command {
  var $command;
  function __construct() {
  }
  function get_command_name() {
    $commands = array_flip( WP_Packager_Manager::$VALID_COMMANDS );
    return $commands[get_class( $this )];
  }
  function execute() {
    $message = sprintf( 'The %s class has not implemented an execute() method.', get_class( $this ) );
    WP_Packager_Manager::fail( $message );
  }
  function show( $message ) {
    fwrite( STDOUT, "{$message}\n" );
  }
}

