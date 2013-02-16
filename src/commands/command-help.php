<?php
class WPPM_Help_Command extends WPPM_Command_Base {
  function execute() {
    WP_Packager_Manager::fail( 'Help comes to those who help themselves.' );
  }
}
