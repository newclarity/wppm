<?php
class WPPM_Prep_Command extends WPPM_Command {
  function execute() {
    $package = WP_Packager_Manager::parse_package();

  }
}
