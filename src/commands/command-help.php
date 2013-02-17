<?php
class WPPM_Help_Command extends WPPM_Command {
  function execute() {
    WP_Package_Manager::fail( 'Help comes to those who help themselves.' );
  }
}
