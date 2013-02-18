<?php
class File_Ops {
  static $_dir_stack = array();

  static function ensure_dir( $dir ) {
    $output = array();
    if ( ! empty( $dir ) ) {
      if( ! is_dir( $dir ) ) {
        $output = array();
        @mkdir( $dir );
        if ( ! is_dir( $dir ) ) {
          $output += self::ensure_dir( dirname( $dir ) );
          mkdir( $dir );
        }
        $output[] = "Making directory {$dir}...";
      }
    }
  	return $output;
  }

  static function mirror_dir( $from_dir, $to_dir ) {
    $from_dir = rtrim( $from_dir, '/' );
    $to_dir = rtrim( $to_dir, '/' );
    exec( "rsync -vaz {$from_dir}/ {$to_dir}", $output );
    return $output;
  }

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

  static function move_dir( $from_dir, $to_dir ) {
    $from_dir = rtrim( $from_dir, '/' );
    $to_dir = rtrim( $to_dir, '/' );
    $output1 = self::ensure_dir( $to_dir );
    exec( "mv {$from_dir}/*.* {$to_dir}/", $output2 );
    return $output1 + $output2;
  }

  static function move_file( $filepath, $new_dir ) {
    $new_dir = rtrim( $new_dir, '/' );
    $output1 = self::ensure_dir( $new_dir );
    exec( "mv {$filepath} {$new_dir}/", $output2 );
    return $output1 + $output2;
  }

  static function mirror_dir_php( $source, $destination ) {
    $output = array();
  	$dir = opendir( $source );
  	self::ensure_dir( $destination );
  	while( false !== ( $file = readdir( $dir ) ) ) {
  		if ( '.' == $file || '..' == $file )
  			continue;
  		if ( is_dir( "{$source}/{$file}" ) ) {
        $output += self::mirror_dir_php( "{$source}/{$file}" , "{$destination}/{$file}" );
  		} else {
  			copy( $from = "{$source}/{$file}" , $to = "{$destination}/{$file}" );
        $output[] = "Copying {$from} to {$to}...";
  		}
  	}
  	closedir( $dir );
  	return $output;
  }

  static function remake_dir( $dir ) {
    $output = array();
  	if ( is_dir( $dir ) )
      $output += self::kill_dir( $dir );
    $output += self::ensure_dir( $dir );
  	return $output;
  }

  static function kill_dir( $dir ) {
    exec( "rm -r -f {$dir}", $output );
    return $output;
  }

  static function kill_file( $file ) {
    exec( "rm -r -f {$file}", $output );
    return $output;
  }

  /**
   * Recursive rmdir()
   *
   * @see: http://php.net/manual/en/function.rmdir.php
   *
   * @param $directory
   */
  static function remove_directory( $directory ) {
    $output = array();
  	if ( $handle = opendir( $directory ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( is_dir( $entry_dir = "{$directory}/{$entry}" ) === true ) {
          $output += self::remove_directory( $entry_dir );
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
   */
  static function recursive_remove( $directory, $file_or_subdir ) {
    $output = array();
  	if ( $handle = opendir( $directory ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( $file_or_subdir == $entry ) {
          $output += self::kill_dir( "{$directory}/{$file_or_subdir}" );
        } else if ( is_dir( $entry_dir = "{$directory}/{$entry}" ) === true ) {
          $output += self::recursive_remove( $entry_dir, $file_or_subdir );
  			}
  		}
  		closedir( $handle );
  	}
  	return $output;
  }
  private static function _pushdir( $new_dir ) {
    array_push( self::$_dir_stack, getcwd() );
    chdir( $new_dir );
  }

  private static function _popdir() {
    chdir( array_pop( self::$_dir_stack ) );
  }


}







