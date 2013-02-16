<?php

class WPPM_Account extends WPPM_Container {
  var $domain;
  var $username;
  var $password;
  function __construct( $domain, $value, $config ) {
    parent::__construct( $domain, $value, $config );
    $this->domain = $domain;
  }
}
