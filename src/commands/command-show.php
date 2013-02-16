<?php
class WPPM_Show_Command extends WPPM_Command {
  function execute() {
    $package = WP_Packager_Manager::parse_package();
    $package->show();
  }
}
