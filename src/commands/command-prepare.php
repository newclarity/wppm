<?php
class WPPM_Prepare_Command extends WPPM_Command {
  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $work_dir = $wppm->get_wppm_filepath( "work/{$package->slug}/{$package->version}" );
    $existing_dir = "{$work_dir}/existing";
    $source_dir = "{$work_dir}/source";
    $prepared_dir = "{$work_dir}/prepared";
    $trunk_dir = "{$prepared_dir}/trunk";
    $export_dir = "{$work_dir}/export/{$package->slug}";
    $zip_file = "{$package->slug}-{$package->version}.zip";
    $archive_dir = $wppm->get_wppm_filepath( "archive/{$package->plural_type}/{$package->slug}" );

    echo "\nPreparing {$package->singular_type_name} {$package->name}...\n";

    /**
     * Clearing out work directory
     */
    echo "\nInitializing directory {$work_dir}/...\n";
    File_Ops::kill_file( $work_dir );

    /**
     * Exporting Source Repository
     */
    $src_agent = $wppm->get_source_agent();
    echo "\nCloning {$package->source->ID}...\n";
    File_Ops::remake_dir( $source_dir );
    $this->show( $src_agent->export( $package->source->url, $source_dir ) );
    echo "Repository clone complete.\n\n";

    /**
     * Exporting Source Repository
     * @var WPPM_Repository $dependency
     */
    foreach( $package->bundled_dependencies as $slug => $dependency ) {
      $dependency_agent = $wppm->get_vcs_agent( $dependency->vcs, $dependency->domain );
      echo "Cloning {$dependency->ID}...\n";
      $type_subdir = $wppm->get_type_subdir( $dependency->type );
      $local_path = "{$source_dir}/{$type_subdir}/{$slug}";
      File_Ops::remake_dir( $local_path );
      $this->show( $dependency_agent->export( $dependency->url, $local_path ) );
      echo "Repository clone complete.\n\n";
    }

    /**
     * Exporting Source Repository
     * @var WPPM_Repository $dependency
     */
    foreach( $package->delete_files as $filepath ) {
      $local_filepath = "{$source_dir}/{$filepath}";
      echo "Deleting {$local_filepath}...\n";
      File_Ops::kill_file( $local_filepath );
    }

    /**
     * Cloning WordPress Host Repository
     */
    $svn_agent = $wppm->get_vcs_agent( 'svn', 'wordpress.org' );
    $svn_id = str_replace( 'http://', '', ltrim( $package->wordpress_svn_url, '/' ) );
    echo "\nChecking out {$svn_id}...\n";
    File_Ops::remake_dir( $existing_dir );
    $this->show( $svn_agent->clone( $package->wordpress_svn_url, $existing_dir ) );
    echo "Repository checkout complete.\n\n";

    /**
     * Merging functionality
     */
    //File_Ops::remake_dir( $prepared_dir );
    echo "\nMirroring {$svn_id}...\n";
    $this->show( File_Ops::mirror_dir( $existing_dir, $prepared_dir ) );
    echo "Repository mirror complete.\n\n";

    echo "\nMerging Source Files with Existing...\n";
    $this->show( $this->_merge_to_svn( $source_dir, $trunk_dir ) );

    /**
     * Get svn to recognize all the new files.
     */
    echo "\nMoving /trunk/assets/*.* up to /assets/*.*...\n";
    $this->show( File_Ops::move_dir( "{$trunk_dir}/assets", "{$prepared_dir}/assets" ) );
    $this->show( $svn_agent->remove( $prepared_dir, "trunk/assets", "--force" ) );

    /**
     * Get rid of annoying Apple .DS_Store files
     */
    echo "\nRemoving annoying Apple Mac OS X .DS_Store files...\n";
    $this->show( File_Ops::recursive_remove( $prepared_dir, '.DS_Store' ) );

    /**
     * Get svn to recognize all the new files.
     */
    echo "\nAdding new/updated files to Subversion repository...\n";
    $this->show( $svn_agent->add( $prepared_dir, "*", "--force" ) );

    /**
     * Export to create a .ZIP file
     * Make sure the parent directory is there first.
     */
    echo "\nExporting to {$export_dir}...\n";
    File_Ops::ensure_dir( dirname( $export_dir ) );
    $this->show( $svn_agent->export( $trunk_dir, $export_dir ) );

    /**
     * Export to create a .ZIP file
     * Make sure the parent directory is there first.
     */
    $zip_filepath = dirname( $export_dir ) . "/{$zip_file}";
    echo "\nZipping to {$zip_filepath}...\n";
    File_Ops::zip_dir( $export_dir, $zip_file );

    /**
     * Move the .ZIP file to the archive directory
     * Make sure the parent directory is there first.
     */
    $archive_filepath = $archive_dir . "/{$zip_file}";
    echo "\nArchiving to {$archive_filepath}...\n";
    File_Ops::move_file( $zip_filepath, $archive_dir );

    echo "\nDONE!!!\n\n";
  }
  private function _merge_to_svn( $source_dir, $prepared_dir, $source_files = false, $prepared_files = false ) {
    $messages = array();

    $source_files = $this->_get_merge_files( $source_dir );
    $prepared_files = $this->_get_merge_files( $prepared_dir );

  	$cwd = getcwd();

  	foreach( $source_files as $source_filename => $source_file ) {
  		/**
  		 * Check to see if this is a sudir
  		 */
  		if ( is_array( $source_file ) ) {
        $messages += $this->_merge_to_svn( "{$source_dir}/{$source_filename}",	"{$prepared_dir}/{$source_filename}" );
  		} else { // is file
  			$prepared_filename = "{$prepared_dir}/{$source_filename}";
  			$source_filename = "{$source_dir}/{$source_filename}";
  			/**
  			 * Check to see if the files are different
  			 */
  			$copy_file = true;
  			if ( file_exists( $prepared_filename ) && @md5_file( $prepared_filename ) == md5_file( $source_filename ) ) {
  				/**
  				 * It's files are same, don't copy.
  				 */
  				$copy_file = false;
  			}
  			/**
  			 * If existing and source files were not the same or source had a file that existing did not have
  			 */
  			if ( $copy_file ) {
  				/**
  				 * If svn file exists and was different than hg then delete the svn file
  				 */
  				if ( file_exists( $prepared_filename ) ) {
  					unlink( $prepared_filename );
  				}
  				/**
  				 * Copy the hg version to svn-work
  				 */
  				$messages[] =  'Merging ' . dirname( $prepared_filename ) . '/' . basename( $source_filename ) . '...';
  				File_Ops::ensure_dir( dirname( $prepared_filename ) );
  				copy( $source_filename, $prepared_filename );
  			}
  		}
  		/**
  		 * Remove from the svn files array because we've processed it.
  		 */
  		unset( $prepared_files[basename($source_filename)] );
  	}
  	/**
  	 * Remove any files from svn that are not in $source_files
  	 */
  	unset( $prepared_files['.svn'] );
  	if ( count( $prepared_files ) ) {
  		chdir( $prepared_dir );
  		$directories = array();
  		foreach( array_keys( $prepared_files ) as $prepared_filename ) {
  			$filename_to_remove = "{$prepared_dir}/{$prepared_filename}";
        $messages[] =  shell_exec( "svn rm --force {$filename_to_remove}" );
  			if ( is_dir( $filename_to_remove ) ) {
  				$directories[] = $filename_to_remove;
  			} else if ( file_exists( $filename_to_remove ) ) {
  				unlink( $filename_to_remove );
  			}
  		}
  		foreach ( $directories as $directory ) {
        $messages[] =  shell_exec( "svn rm --force {$filename_to_remove}" );
        File_Ops::remove_directory( $directory );
  		}

  	}
  	return $messages;
  }

  /**
   * @param string $directory
   *
   * @return array
   */
  private function _get_merge_files( $directory ) {
  	$files = array();
    $directory = rtrim( $directory, '/' );
    File_Ops::ensure_dir( $directory );
  	$di = new DirectoryIterator( rtrim( $directory, '/' ) );
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
        $files[$basename] = array();  // This is the marker for a subdirectory.
  		} else {
  			$files[$basename] = true;
  		}
  	}
  	return $files;
  }

}
