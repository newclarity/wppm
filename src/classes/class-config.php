<?php

class WPPM_Config extends WPPM_Container {
  var $CONTAINED_TYPES = array(
    'executables' => 'WPPM_Executable',
    'hosts'       => 'WPPM_Host',
  );
  var $hosts = array();
  var $executables = array();
  function __construct( $config_filepath, $config ) {
    $this->FILEPATH = $config_filepath;
    parent::__construct( 'config', $config, $this );
  }
}
