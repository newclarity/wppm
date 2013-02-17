<?php
class WPPM_Status_Command extends WPPM_Command {

  function execute() {
    $package = WP_Packager_Manager::parse_package();
    $status = <<<MSG
\n\tSTATUS
\t------
\t{$package->singular_type_name} Name: {$package->name}
\tPackage Version: {$package->version}\n
MSG;
    $this->show( $status );
  }
}
