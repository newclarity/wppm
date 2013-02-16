<?php

class WPPM_Host extends WPPM_Container {
  var $domain;
  var $accounts = array();
  function __construct( $domain, $value, $config ) {
    parent::__construct( $domain, null, $config );
    $this->domain = $domain;
    $account = new WPPM_Account( $domain, $value, $this->ROOT );
    $this->accounts["{$domain}/{$account->username}"] = $account;
  }
}
