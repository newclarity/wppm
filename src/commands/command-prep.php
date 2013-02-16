<?php
class WPPM_Prep_Command extends WPPM_Command_Base {
  function execute() {
    $package = WP_Packager_Manager::parse_package();

  }
}
