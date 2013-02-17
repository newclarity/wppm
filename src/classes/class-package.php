<?php

class WPPM_Package extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'author'       => 'WPPM_Contributor',
    'source'       => 'WPPM_Repository',
    'contributors' => 'WPPM_Contributor',
    'dependencies' => 'WPPM_Repository',
  );
  var $name;
  var $slug;
  var $description;
  var $version;
  var $type;
  var $license;
  var $requires;
  var $tested;
  var $author;
  var $contributors = array();
  var $tags;
  var $source;
  var $dependencies = array();
  var $bundled_dependencies = array();

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


