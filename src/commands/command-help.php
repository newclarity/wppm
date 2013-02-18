<?php
class WPPM_Help_Command extends WPPM_Command {

  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $wppm->fail( 'Help comes to those who help themselves.' );
  }
}
