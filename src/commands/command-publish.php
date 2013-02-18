<?php
class WPPM_Publish_Command extends WPPM_Command {


  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $work_dir = $wppm->get_wppm_filepath( "work/{$package->slug}/{$package->version}" );
    $prepared_dir = "{$work_dir}/prepared";

    $svn_agent = $wppm->get_vcs_agent( 'svn', 'wordpress.org' );

    echo "\nPublishing {$package->type} {$package->name} {$package->version} to WordPress.org...\n";
    $this->show( $svn_agent->push_version( $prepared_dir, $package->version ) );


  }
}
