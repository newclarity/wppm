<?php
/**
 * @property WPPM_Package $ROOT
 */
class WPPM_Repository extends WPPM_Container {

  var $vcs;
  var $url;
  var $domain;
  var $version = '*';
  var $type;
  var $account;
  var $repository;

  var $singular_type_name;
  var $plural_type_name;

  function __construct( $id, $value, $package = false ) {
    parent::__construct( $id, $value, $package );
    $this->ID = preg_replace( '#^https?://(.*)#', '$1', $this->url );
    $parts = explode( '/', $this->ID );

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
        $this->url = preg_replace( '#^http://(.*)$#', 'https://$1', $this->url );
        break;
      case 'wordpress.org':
        $this->vcs = 'svn';
        break;
    }

    switch ( $this->type ) {
      case 'plugin':
        $this->singular_type_name = 'Plugin';
        $this->plural_type_name =   'Plugins';
        break;

      case 'theme':
        $this->singular_type_name = 'Theme';
        $this->plural_type_name =   'Themes';
        break;

      case 'library':
        $this->singular_type_name = 'Library';
        $this->plural_type_name =   'Libraries';
        break;
    }

    $this->ID = "{$this->domain}/{$this->account}/{$this->repository}";

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


