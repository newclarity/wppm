<?php
class WPPM_Show_Command extends WPPM_Command {
  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $config = $wppm->parse_config();
    $config->show();
    $package = $wppm->parse_package();
    $package->show();
  }
}
