<?php

final class WP_Package_Manager {
  static $command = 'help';
  static $args = array();
  static $switches = array();

  private $VALID_COMMANDS = array(
    'help'    => 'WPPM_Help_Command',
    'show'    => 'WPPM_Show_Command',
    'prep'    => 'WPPM_Prep_Command',
    'publish' => 'WPPM_Publish_Command',
    'status'  => 'WPPM_Status_Command',
  );
  private $_package;
  private $_config;
  var $suppress_errors = false;

  /**
   *
   */
  function execute() {
    self::parse_command_line();
    $this->get_command( self::$command )->execute( $this );
  }

  /**
   * @param string $command
   * @return WPPM_Command
   */
  function get_command( $command ) {
    if ( ! $this->has_command( $command ) )
      $this->fail( "Command [{$command}] not valid." );

    $command_class = $this->VALID_COMMANDS[$command];
    require( dirname( __DIR__ ) . "/commands/command-{$command}.php" );
    $command = new $command_class;
    return $command;
  }

  /**
   * @param bool|string $package_file
   *
   * @return WPPM_Package
   */
  function get_package( $package_file = false ) {
    if ( ! isset( $this->_package ) ) {
      $this->_package = $this->parse_package( $package_file );
    }
    return $this->_package;
  }

  /**
   * @return Vcs_Agent
   */
  function get_source_agent() {
    $source = $this->get_package()->source;
    $agent = $this->get_vcs_agent( $source->vcs, $source->domain );
    return $agent;
  }

  /**
   * Return the name of the subdirectory with a plugin directory based on type or repository.
   *
   * @param string $repository_type - 'library', 'theme' or 'plugin'
   *
   * @return bool|string
   */
  function get_type_subdir( $repository_type ) {
    $type_subdir = false;
    switch (  $repository_type ) {
      case 'theme':
        $type_subdir = 'themes';
        break;

      case 'library':
        $type_subdir = 'libraries';
        break;

      case 'plugin':
        $type_subdir = 'plugins';
        break;

    }
    return $type_subdir;
  }

  /**
   * @param string $vcs_type Type of version control system, i.e. 'git', 'hg', 'svn'
   * @param string $domain
   *
   * @return Vcs_Agent
   */
  function get_vcs_agent( $vcs_type, $domain ) {
    $config = $this->get_config();
    $args = array( 'executable' => $config->executables[$vcs_type]->filepath );
    $agent = Vcs_Interface::get_agent( $vcs_type, $args );
    $account = reset( $config->hosts[$domain]->accounts );
    $agent->username = $account->username;
    $agent->password = $account->password;
    return $agent;
  }

  /**
   * @param bool|string $package_file
   *
   * @return WPPM_Package
   */
  function parse_package( $package_file = false ) {
    if ( ! $package_file )
      $package_file = getcwd() . "/wp-package.json";

    return $this->_parse_json( $package_file, 'package', 'WPPM_Package' );
  }

  /**
   * @param bool|string $config_file
   *
   * @return WPPM_config
   */
  function get_config( $config_file = false ) {
    if ( ! isset( $this->_config ) ) {
      $this->_config = $this->parse_config( $config_file );
    }
    return $this->_config;
  }

  /**
   * @param bool|string $config_file
   *
   * @return WPPM_config
   */
  function parse_config( $config_file = false ) {
    if ( ! $config_file )
      $config_file = $this->get_wppm_filepath( 'config.json' );

    return $this->_parse_json( $config_file, 'config', 'WPPM_Config' );
  }

  /**
   *
   */
  function get_wppm_filepath( $path ) {
    $filepath = "{$_SERVER['HOME']}/.wppm/{$path}";
    File_Ops::ensure_dir( dirname( $filepath ) );
    return $filepath;
  }

  /**
   * @param string $json_file
   * @param string $file_type
   * @param string $class_name
   *
   * @return WPPM_Container
   */
  private function _parse_json( $json_file, $file_type, $class_name ) {

    if ( ! is_file( $json_file ) )
      $this->fail( "The {$file_type} file does not exist: {$json_file}" );

    $json_object = file_get_contents( $json_file );
    if ( empty( $json_object ) )
      $this->fail( "The {$file_type} file {$json_file} is empty." );

    $json_object = json_decode( $json_object );
    if ( empty( $json_object ) )
      $this->fail( "The {$file_type} file {$json_file} has invalid syntax." );

    $object = new $class_name( $json_file, $json_object );

    return $object;

  }

  /**
   * @param $message
   */
  function fail( $message ) {
    $usage =<<<USAGE
wppm - WordPress Package Manager

  {$message}

Usage:
   wppm <command> <switches>
\n
USAGE;
    if ( ! $this->suppress_errors )
      fwrite( STDERR, $usage );
    exit;
  }

  /**
   * @param $command
   *
   * @return bool
   */
  function has_command( $command ) {
    return isset( $this->VALID_COMMANDS[$command] );
  }

  /**
   * @todo Move this to a global scope, CLI
   */
  function parse_command_line() {
 		global $argv;
 		if ( isset( $argv[1] ) )
      self::$command = $argv[1];

 		if ( ! $this->has_command( self::$command ) ) {
 			$this->fail( 'Command [' . self::$command .'] not valid.' );
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


