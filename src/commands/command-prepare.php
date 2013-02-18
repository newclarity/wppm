<?php
class WPPM_Prepare_Command extends WPPM_Command {
  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $work_dir = $wppm->get_wppm_filepath( "work/{$package->slug}" );
    $source_dir = "{$work_dir}/source";

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
    echo "\nCloning {$svn_id}\n";
    $local_path = "{$work_dir}/existing";
    File_Ops::remake_dir( $local_path );
    $this->show( $svn_agent->clone( $package->wordpress_svn_url, $local_path ) );
    echo "Repository clone complete.\n\n";

    /**
     * Merging functionality
     */
    $local_path = $wppm->get_wppm_filepath( "{$work_dir}/updated" );

  }
}
