<?php
class WPPM_Publish_Command extends WPPM_Command {


  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
  }
}
