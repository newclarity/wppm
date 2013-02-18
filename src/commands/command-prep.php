<?php
class WPPM_Prep_Command extends WPPM_Command {
  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $work_dir = $wppm->get_wppm_filepath( "work/{$package->slug}" );
    $source_dir = "{$work_dir}/source";

    File_Ops::kill_file( $work_dir );
    /**
     * Exporting Source Repository
     */
    $src_agent = $wppm->get_source_agent();
    echo "\nCloning {$package->name} repository from {$package->source->domain}...\n";
    File_Ops::remake_dir( $source_dir );
    $this->show( $src_agent->export( $package->source->url, $source_dir ) );
    echo "Repository clone complete.\n\n";

    /**
     * Exporting Source Repository
     * @var WPPM_Repository $dependency
     */
    foreach( $package->bundled_dependencies as $slug => $dependency ) {
      $dependency_agent = $wppm->get_vcs_agent( $dependency->vcs, $dependency->domain );
      echo "Cloning {$dependency->repository} repository from {$dependency->domain}/{$dependency->account}...\n";
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
      echo "Deleting {$filepath} from {$source_dir}...\n";
      File_Ops::kill_file( $local_filepath );
    }

    /**
     * Cloning WordPress Host Repository
     */
    $svn_agent = $wppm->get_vcs_agent( 'svn', 'wordpress.org' );
    echo "\nCloning {$package->name} repository from WordPress.org...\n";
    $local_path = "{$work_dir}/existing";
    File_Ops::remake_dir( $local_path );
    $this->show( $svn_agent->clone( $package->wordpress_svn_url, $local_path ) );
    echo "Repository clone complete.\n\n";

    File_Ops::recursive_kill_subdir( $local_path, '.svn' );

    /**
     * Merging functionality
     */
    $local_path = $wppm->get_wppm_filepath( "{$work_dir}/updated" );

  }
}
