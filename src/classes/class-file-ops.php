<?php
class File_Ops {
  static function ensure_dir( $dir ) {
  	@mkdir( $dir );
  	if ( ! is_dir( $dir ) ) {
  		if ( ! empty( $dir ) ) {
  			self::ensure_dir( dirname( $dir ) );
  			mkdir( $dir );
  		}
  	}
  }

  static function remake_dir( $dir ) {
    $output = array();
  	if ( is_dir( $dir ) )
      self::kill_dir( $dir );
  	self::ensure_dir( $dir );
  }

  static function kill_dir( $dir ) {
    exec( "rm -r -f {$dir}" );
  }

  static function kill_file( $file ) {
    exec( "rm -r -f {$file}" );
  }

  /**
   * Recursive kill_dir()
   *
   * @see: http://php.net/manual/en/function.rmdir.php
   * @param $dir
   */
  static function recursive_kill_dir( $dir ) {
  	if ( $handle = opendir( $dir ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( is_dir( $entry_dir = "{$dir}/{$entry}" ) === true ) {
          self::recursive_kill_dir( $entry_dir );
        } else {
          unlink( $entry_dir );
        }
  		}
  		closedir( $handle );
  		rmdir( $dir );
  	}
  }

  /**
   * Recursive strip_dir()
   *
   * @see: http://php.net/manual/en/function.rmdir.php
   *
   * @param string $dir
   * @param string $subdir
   */
  static function recursive_kill_subdir( $dir, $subdir ) {
  	if ( $handle = opendir( $dir ) ) {
  		while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( '.' == $entry || '..' == $entry ) {
      		continue;
        } else if ( $entry == $subdir ) {
  			  self::kill_dir( "{$dir}/{$subdir}" );
        } else if ( is_dir( $entry_dir = "{$dir}/{$entry}" ) === true){
          self::recursive_kill_subdir( $entry_dir, $subdir );
  			}
  		}
  		closedir( $handle );
  	}
  }

  static function get_dir_files( $dir ) {
  	$files = array();
  	$di = new DirectoryIterator( rtrim( $dir, '/' ) );
  	/**
  	 * @var SplFileInfo $file_info
  	 */
  	foreach( $di as $file_name => $file_info ) {
  		$basename = $file_info->getBasename();
  		if ( '.' == $basename || '..' == $basename ) {
  			continue;
  		} else if ( preg_match( '#^\.(hg|git)#', $basename ) ) {
  			continue;
  		} else if ( '.DS_Store' == $basename  ) {
  			continue;
  		} else if ( '.svn' == $basename  ) {
  			$files[$basename] = true;
  		} else if ( $file_info->isDir() ) {
  			$files[$basename] = self::get_dir_files( "{$dir}/{$basename}" );
  		} else {
  			$files[$basename] = true;
  		}
  	}
  	return $files;
  }
  function download_repos( $plugin_slug ) {
  	global $BITBUCKET_USER, $BITBUCKET_ACCOUNT, $argv;
  	extract( get_prep_vars( $plugin_slug ) );

  	$svn_url = "http://plugins.svn.wordpress.org/{$plugin_slug}";

  	if ( ! in_array( '--no-test-svn', $argv ) )
  		if ( ! @file_get_contents( $svn_url ) )
  			throw new Exception( "Connection error, or no plugin [{$plugin_slug}] on wordpress.org" );

  	// Can't easily test this one...
  	if ( ! ( $hg_repo = get_switch_value( '--hg-repo' ) ) )
  		$hg_repo = $plugin_slug;

  	$hg_url = "https://{$BITBUCKET_USER}@bitbucket.org/{$BITBUCKET_ACCOUNT}/{$hg_repo}";

  	remake_dir( $svn_dir );
  	clone_repo( 'SVN', "{$svn_url} {$svn_dir}" );

  	remake_dir( $hg_dir );
  	clone_repo( 'HG', "{$hg_url} {$hg_dir}" );

  }
  function get_switch_value( $switch ) {
  	global $argv;
  	$value = false;
  	foreach( $argv as $arg ) {
  		if ( preg_match( "#^{$switch}=(.*)$#", $arg, $match ) ) {
  			$value = $match[1];
  		}
  	}
  	return $value;
  }
  function process_plugin( $plugin_slug ) {
  	extract( get_prep_vars( $plugin_slug ) );


  	$svn_files = get_dir_files( $svn_dir );
  	$hg_files = get_dir_files( $hg_dir );

  	clone_svn( $svn_dir, $svn_work_dir );

  	fixup_files( $plugin_slug, $hg_files, $hg_dir, $svn_files['trunk'], "{$svn_work_dir}/trunk" );

  	/**
  	 * Get svn to recognize all the new files.
  	 */
  	chdir( $svn_work_dir );
    shell_exec( 'svn remove trunk/assets --force' );
    shell_exec( 'svn add * --force' );


  }
  function fixup_files( $plugin_slug, $hg_files, $hg_dir, $svn_files, $svn_work_dir ) {
  	$cwd = getcwd();

  	foreach( $hg_files as $hg_file_name => $hg_file ) {
  		/**
  		 * Check to see if this is a sudir
  		 */
  		if ( is_array( $hg_file ) ) {
  			$svn_sub_files = isset( $svn_files[$hg_file_name] ) ? $svn_files[$hg_file_name] : array();
  			fixup_files( $plugin_slug,
  				$hg_file, "{$hg_dir}/{$hg_file_name}",
  				$svn_sub_files, "{$svn_work_dir}/{$hg_file_name}"
  			);
  		} else { // is file
  			$svn_work_file_name = "{$svn_work_dir}/{$hg_file_name}";
  			$hg_file_name = "{$hg_dir}/{$hg_file_name}";
  			/**
  			 * Check to see if the files are different
  			 */
  			$copy_file = true;
  			if ( file_exists( $svn_work_file_name ) && @md5_file( $svn_work_file_name ) == md5_file( $hg_file_name ) ) {
  				/**
  				 * It's files are same, don't copy.
  				 */
  				$copy_file = false;
  			}
  			/**
  			 * If svn and hg files were not the same or hg had a file that svn did not have
  			 */
  			if ( $copy_file ) {
  				/**
  				 * If svn file exists and was different than hg then delete the svn file
  				 */
  				if ( file_exists( $svn_work_file_name ) ) {
  					unlink( $svn_work_file_name );
  				}
  				/**
  				 * Copy the hg version to svn-work
  				 */
  				echo "Copying " . basename( $hg_file_name ) . ' to ' . dirname( $svn_work_file_name ) . "...";
  				ensure_dir( dirname( $svn_work_file_name ) );
  				copy( $hg_file_name, $svn_work_file_name );
  				echo "\n";
  			}
  		}
  		/**
  		 * Remove from the svn files array because we've processed it.
  		 */
  		unset( $svn_files[basename($hg_file_name)] );
  	}
  	/**
  	 * Remove any files from svn that are not in $hg_files
  	 */
  	unset( $svn_files['.svn'] );
  	if ( count( $svn_files ) ) {
  		chdir( $svn_work_dir );
  		$directories = array();
  		foreach( $svn_files as $svn_file_name => $svn_file ) {
  			$filename_to_remove = "{$svn_work_dir}/{$svn_file_name}";
  			shell_exec( "svn rm --force {$filename_to_remove}" );
  			if ( is_dir( $filename_to_remove ) ) {
  				$directories[] = $filename_to_remove;
  			} else if ( file_exists( $filename_to_remove ) ) {
  				unlink( $filename_to_remove );
  			}
  		}
  		foreach ( $directories as $directory ) {
  			shell_exec( "svn rm --force {$filename_to_remove}" );
  			rrmdir( $directory );
  		}

  	}
  }

  function clone_svn( $source, $destination ) {
  	$dir = opendir( $source );
  	@mkdir($destination);
  	while( false !== ( $file = readdir( $dir ) ) ) {
  		if ( '.' == $file || '..' == $file )
  			continue;
  		if ( is_dir( "{$source}/{$file}" ) ) {
  			clone_svn( "{$source}/{$file}" , "{$destination}/{$file}" );
  		} else {
  			copy( "{$source}/{$file}" , "{$destination}/{$file}" );
  		}
  	}
  	closedir( $dir );
  }
  //function clone_svn( $svn_files, $svn_dir, $svn_work_dir ) {
  //	if ( ! is_array( $svn_files ) )
  //		return;
  //	mkdir( $svn_work_dir );
  //	echo shell_exec( "cp {$svn_dir}/* {$svn_work_dir}" );
  //	echo shell_exec( "cp -r {$svn_dir}/.svn {$svn_work_dir}/.svn" );
  //	foreach( $svn_files as $name => $svn_file ) {
  //		clone_svn( $svn_file, "{$svn_dir}/{$name}", "{$svn_work_dir}/{$name}" );
  //	}
  //}
//  function get_prep_vars( $plugin_slug ) {
//  	$plugin_dir = __DIR__ .  "/{$plugin_slug}";
//  	$vars = array(
//  		'plugin_dir' 		=> $plugin_dir,
//  		'svn_dir' 			=> "{$plugin_dir}/svn",
//  		'hg_dir' 				=> "{$plugin_dir}/hg",
//  		'svn_work_dir' 	=> "{$plugin_dir}/svn-work",
//  	);
//  	$vars['svn_trunk_dir'] = "{$vars['svn_dir']}/trunk";
//  	return $vars;
//  }




}







