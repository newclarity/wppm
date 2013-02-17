<?php
class WPPM_Show_Command extends WPPM_Command {
  function execute() {
    $config = WP_Package_Manager::parse_config();
    $config->show();
    $package = WP_Package_Manager::parse_package();
    $package->show();
  }
}
