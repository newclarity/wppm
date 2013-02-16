<?php

class WP_Packager_Manager {
  static $VALID_COMMANDS = array(
    'help'    => 'WPPM_Help_Command',
    'prep'    => 'WPPM_Prep_Command',
    'status'  => 'WPPM_Status_Command',
  );
  static $suppress_errors = false;
  static $command = 'help';
  static $args = array();
  static $switches = array();
  /**
   *
   */
  static function execute() {
    self::parse_command_line();
    self::get_command( self::$command )->execute();
  }

  /**
   * @param string $command
   * @return WPPM_Command_Base
   */
  static function get_command( $command ) {
    if ( ! self::has_command( $command ) )
      self::fail( "Command [{$command}] not valid." );

    $command_class = self::$VALID_COMMANDS[$command];
    require( dirname( __DIR__ ) . "/commands/command-{$command}.php" );
    $command = new $command_class;
    return $command;
  }
  /**
   * @param bool|string $package_file
   *
   * @return WPPM_Package
   */
  static function parse_package( $package_file = false ) {
    if ( ! $package_file )
      $package_file = getcwd() . "/wp-package.json";

    if ( ! is_file( $package_file ) ) {
      self::fail( "The package file does not exist: {$package_file}" );
      exit;
    }

    $json_package = file_get_contents( $package_file );

    $json_package = json_decode( $json_package );

    $package = new WPPM_Package( $package_file, $json_package );

    return $package;
  }

  /**
   * @param $message
   */
  static function fail( $message ) {
    $usage =<<<USAGE
wppm - WordPress Package Manager

  {$message}

Usage:
   wppm <command> <switches>
\n
USAGE;
    if ( ! self::$suppress_errors )
      fwrite( STDERR, $usage );
    exit;
  }

  /**
   * @param $command
   *
   * @return bool
   */
  static function has_command( $command ) {
    return isset( self::$VALID_COMMANDS[$command] );
  }

  /**
   *
   */
  static function parse_command_line() {
 		global $argv;
 		if ( isset( $argv[1] ) )
      self::$command = $argv[1];

 		if ( ! self::has_command( self::$command ) ) {
 			self::fail( 'Command [' . self::$command .'] not valid.' );
 		}

 		for( $i = 2; $i < count( $argv ); $i++ ) {
 			if ( '--' == substr( $argv[$i], 0, 2 ) ) {
 				$switch = $argv[$i++];
 				break;
 			} else {
 				self::$args[] = $argv[$i];
 			}
 		}

 		$switches = array();
 		for( null; $i < count( $argv ); $i++ ) {
 			if ( '--' == substr( $argv[$i], 0, 2 ) ) {
 				self::$switches[] = trim( $switch );
 				$switch = '';
 			}
 			$switch .= " {$argv[$i]}";
 		}
 		if ( ! empty( $switch ) ) {
 			self::$switches[] = trim( $switch );
 		}
 	}
}


