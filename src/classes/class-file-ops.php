<?php
class File_Ops {
  static $_dir_stack = array();

  /**
   * @param string $directory
   *
   * @return array
   */
  static function ensure_dir( $directory ) {
    $output = array();
    if ( ! empty( $directory ) ) {
      if( ! is_dir( $directory ) ) {
        $output = array();
        @mkdir( $directory );
        if ( ! is_dir( $directory ) ) {
          $output = array_merge( $output, self::ensure_dir( dirname( $directory ) ) );
          mkdir( $directory );
        }
        $output[] = "Making directory {$directory}...";
      }
    }
  	return $output;
  }

  /**
   * @param string $source_dir
   * @param string $destination_dir
   *
   * @return array
   */
  static function mirror_dir( $source_dir, $destination_dir ) {
    $source_dir = rtrim( $source_dir, '/' );
    $destination_dir = rtrim( $destination_dir, '/' );
    exec( "rsync -vaz {$source_dir}/ {$destination_dir}", $output );
    return $output;
  }

  /**
   * @param string $source_dir
   * @param string $destination_dir
   *
   * @return array
   */
  static function mirror_dir_php( $source_dir, $destination_dir ) {
    $output = array();
  	$dir = opendir( $source_dir );
  	self::ensure_dir( $destination_dir );
  	while( false !== ( $file = readdir( $dir ) ) ) {
  		if ( '.' == $file || '..' == $file )
  			continue;
  		if ( is_dir( "{$source_dir}/{$file}" ) ) {
        $output = array_merge( $output,  self::mirror_dir_php( "{$source_dir}/{$file}" , "{$destination_dir}/{$file}" ) );
  		} else {
  			copy( $from = "{$source_dir}/{$file}" , $to = "{$destination_dir}/{$file}" );
        $output[] = "Copying {$from} to {$to}...";
  		}
  	}
  	closedir( $dir );
  	return $output;
  }

  /**
   * @param string $dir_to_zip
   * @param string $zip_file
   *
   * @return array
   */
  static function zip_dir( $dir_to_zip, $zip_file ) {
    $dir_to_zip = rtrim( $dir_to_zip, '/' );
    $parent_dir = dirname( $dir_to_zip );
    $dir_to_zip = basename( $dir_to_zip );
    self::_pushdir( $parent_dir );
    $command = "zip -vr {$zip_file} {$dir_to_zip}/ -x \"*.DS_Store\"";
    exec( $command, $output );
    self::_popdir();
    return $output;
  }

  /**
   * @param string $from_dir
   * @param string $to_dir
   *
   * @return array
   */
  static function move_dir( $from_dir, $to_dir ) {
    $from_dir = rtrim( $from_dir, '/' );
    $to_dir = rtrim( $to_dir, '/' );
    $output1 = self::ensure_dir( $to_dir );
    exec( "mv {$from_dir}/*.* {$to_dir}/", $output2 );
    return array_merge( $output1, $output2 );
  }

  /**
   * @param string $from_dir
   * @param string $to_dir
   *
   * @return array
   */
  static function copy_dir( $from_dir, $to_dir ) {
    $from_dir = rtrim( $from_dir, '/' );
    $to_dir = rtrim( $to_dir, '/' );
    exec( "cp -rf {$from_dir}/ {$to_dir}", $output );
    return $output;
  }

  /**
   * @param string $filepath
   * @param string $new_dir
   *
   * @return array
   */
  static function move_file( $filepath, $new_dir ) {
    $new_dir = rtrim( $new_dir, '/' );
    $output1 = self::ensure_dir( $new_dir );
    exec( "mv {$filepath} {$new_dir}/", $output2 );
    return array_merge( $output1, $output2 );
  }

  /**
   * @param string $dir
   *
   * @return array
   */
  static function remake_dir( $dir ) {
    $output = is_dir( $dir ) ? self::kill_dir( $dir ) : array();
    return array_merge( $output,  self::ensure_dir( $dir ) );
  }

  /**
   * @param string $dir
   *
   * @return array
   */
  static function kill_dir( $dir ) {
    exec( "rm -r -f {$dir}", $output );
    return $output;
  }

  /**
   * @param string $file
   *
   * @return array
   */
  static function kill_file( $file ) {
    exec( "rm -r -f {$file}", $output );
    return $output;
  }

  /**
   * Recursive rmdir()
   *
   * @see: http://php.net/manual/en/function.rmdir.php
   *
   * @param string $directory
   * @return array
   */
  static function remove_directory( $directory ) {
    $output = array();
  	if ( $handle = opendir( $directory ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( is_dir( $entry_dir = "{$directory}/{$entry}" ) === true ) {
          $output = array_merge( $output,  self::remove_directory( $entry_dir ) );
        } else {
          $output[] = "Removing {$entry_dir}...";
          unlink( $entry_dir );
        }
  		}
  		closedir( $handle );
  		rmdir( $directory );
  	}
  	return $output;
  }

  /**
   * Recursive removes a file or a subdirectory in a directory and it's subdirectories
   *
   * Useful for deleting .DS_Store files and .svn subdirectories anywhere within a directory.
   *
   * @see: http://php.net/manual/en/function.rmdir.php
   *
   * @param string $directory Directory in which files or subdirs to delete may be found.
   * @param string $file_or_subdir Delete this file or directory recursively (turtles all the way down)
   * @return array
   *
   */
  static function recursive_remove( $directory, $file_or_subdir ) {
    $output = array();
  	if ( $handle = opendir( $directory ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( $file_or_subdir == $entry ) {
          $output = array_merge( $output,  self::kill_dir( "{$directory}/{$file_or_subdir}" ) );
        } else if ( is_dir( $entry_dir = "{$directory}/{$entry}" ) === true ) {
          $output = array_merge( $output,  self::recursive_remove( $entry_dir, $file_or_subdir ) );
  			}
  		}
  		closedir( $handle );
  	}
  	return $output;
  }

  /**
   * @param string $new_dir
   */
  private static function _pushdir( $new_dir ) {
    array_push( self::$_dir_stack, getcwd() );
    chdir( $new_dir );
  }

  /**
   *
   */
  private static function _popdir() {
    chdir( array_pop( self::$_dir_stack ) );
  }


}







