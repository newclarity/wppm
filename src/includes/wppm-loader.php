<?php

/**
 * Load Root and Base Classes. We'll always need them.
 */
require ( __DIR__ . "/wp-package-manager.php" );
require ( __DIR__ . "/class-container.php" );
require ( __DIR__ . "/class-command.php" );
require ( __DIR__ . "/vcs-interface.php" );
require ( __DIR__ . "/class-vcs-agent.php" );

spl_autoload_register( 'wppm_class_autoloader' );

/**
 * Autoloads classes from the /classes subdirectory.
 *
 * Class WPPM_Foo_Bar_Baz should be found in class-foo-bar-baz.php
 *
 * @param string $class_name
 *
 * @throws Exception
 */
function wppm_class_autoloader( $class_name ) {
  $class_file = strtolower( str_replace( '_', '-', preg_replace( '#^WPPM_(.*)$#', '$1', $class_name ) ) );
  $class_file = dirname( __DIR__ ) . "/classes/class-{$class_file}.php";
  if ( file_exists( $class_file ) ) {
    require ( $class_file );
  } else {
    $error_msg = "Class [{$class_name}] could not be autoloaded from: {$class_file}.";
    error_log( $error_msg );
    throw new Exception( $error_msg );
  }
}
