<?php

class WPPM_Package extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'author'       => 'WPPM_Contributor',
    'source'       => 'WPPM_Repository',
    'license'      => 'WPPM_License',
    'contributors' => 'WPPM_Contributor',
    'dependencies' => 'WPPM_Repository',
  );
  var $name;
  var $slug;
  var $description;
  var $version;
  var $stable_tag;
  var $type;
  var $license;
  var $copyright;
  var $requires_wp;
  var $tested_with;
  var $requires_php;
  var $author;
  var $contributors = array();
  var $tags;
  /**
   * @var WPPM_Repository
   */
  var $source;
  var $dependencies = array();
  var $bundled_dependencies = array();
  var $delete_files = array();
  var $url;

  var $singular_type_name;
  var $plural_type_name;
  var $wordpress_svn_url;

  function __construct( $package_filepath, $package ) {
    $this->FILEPATH = $package_filepath;

    if ( isset( $package->requires ) && ! isset( $package->requires_wp ) ) {
      $package->requires_wp = $package->requires;
      unset( $package->requires );
    }

    if ( isset( $package->tested ) && ! isset( $package->tested_with ) ) {
      $package->tested_with = $package->tested;
      unset( $package->tested );
    }

    parent::__construct( 'package', (array)$package );

    if ( is_null( $this->type ) )
      $this->type = 'plugin';

    if ( is_null( $this->stable_tag ) )
      $this->stable_tag = $this->version;

    switch ( $this->type ) {
      case 'plugin':
        $this->singular_type_name = 'Plugin';
        $this->plural_type_name =   'Plugins';
        $this->wordpress_svn_url = "http://plugins.svn.wordpress.org/{$this->slug}/";
        break;

      case 'theme':
        $this->singular_type_name = 'Theme';
        $this->plural_type_name =   'Themes';
        $this->wordpress_svn_url = "http://themes.svn.wordpress.org/{$this->slug}/";
        break;

      case 'library':
        $this->singular_type_name = 'Library';
        $this->plural_type_name =   'Libraries';
        break;

    }
  }
  protected function _fixup() {
    if ( is_null( $this->slug ) )
      $this->slug = strtolower( str_replace( ' ', '-', $this->name ) );

    parent::_fixup();

    if ( 0 == count( $this->bundled_dependencies ) ) {
      if ( count( $this->dependencies ) ) {
        $this->bundled_dependencies = $this->dependencies;
      }
    } else {
      $bundled_dependencies = array();
      foreach ( $this->bundled_dependencies as $dependency_id ) {
        if ( isset($this->dependencies[$dependency_id]) ) {
          $bundled_dependencies[$dependency_id] = $this->dependencies[$dependency_id];
        }
      }
      $this->bundled_dependencies = $bundled_dependencies;
    }
  }

}


