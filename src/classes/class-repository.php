<?php

class WPPM_Repository extends WPPM_Container {

  var $vcs;
  var $source;
  var $domain;
  var $version = '*';
  var $type;
  var $account;
  var $repository;
  function __construct( $id, $value, $package = false ) {
    parent::__construct( $id, $value, $package );
    if ( ! preg_match( '#^https?://#', $this->source ) )
      $this->source = "http://{$this->source}";
    $source = preg_replace( '#^https?://(.*)#', '$1', $this->source );
    $parts = explode( '/', $source );

    $this->domain = $parts[0];

    switch ( $this->domain ) {
      case 'bitbucket.org':
      case 'github.com':
        $this->account = isset( $parts[1] ) ? $parts[1] : null;
        $this->repository = isset( $parts[2] ) ? $parts[2] : null;
        break;
      case 'wordpress.org':
        $this->repository = array_pop( $parts );
        break;
    }

    switch ( $this->domain ) {
      case 'github.com':
        $this->vcs = 'git';
        break;
      case 'bitbucket.org':
        $this->vcs = is_null( $this->vcs ) ? 'hg' : $this->vcs;
        break;
      case 'wordpress.org':
        $this->vcs = 'svn';
        break;
    }


  }
  protected function _fixup() {
    parent::_fixup();
    if ( is_null( $this->type ) )
      switch ( $this->ROOT->type ) {
        case 'plugin':
          $this->type = 'library';
          break;

        case 'theme':
          $this->type = 'plugin';
          break;

      }
  }
}


