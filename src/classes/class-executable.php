<?php

class WPPM_Executable extends WPPM_Container {
  var $type;
  var $filepath;
  function __construct( $type, $value, $config ) {
    parent::__construct( $type, $value, $config );
    $this->type = $type;
    if ( is_null( $this->filepath ) )
      $this->filepath = $value;
  }
}
