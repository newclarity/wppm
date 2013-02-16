<?php

class WPPM_Package extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'author'       => 'WPPM_Contributor',
    'wordpress'    => 'WPPM_WordPress',
    'repository'   => 'WPPM_Repository',
    'contributors' => 'WPPM_Contributor',
    'dependencies' => 'WPPM_Repository',
  );
  var $name;
  var $slug;
  var $description;
  var $version;
  var $type;
  var $license;
  var $wordpress;
  var $author;
  var $contributors = array();
  var $tags;
  var $repository;
  var $dependencies = array();
  var $bundledDependencies = array();

  protected function _fixup() {
    if ( is_null( $this->slug ) )
      $this->slug = strtolower( str_replace( ' ', '-', $this->name ) );

    parent::_fixup();

    $bundledDependencies = array();
    foreach ( $this->bundledDependencies as $dependency_id ) {
      if ( isset($this->dependencies[$dependency_id]) ) {
        $bundledDependencies[$dependency_id] = $this->dependencies[$dependency_id];
      }
    }
    $this->bundledDependencies = $bundledDependencies;

  }

}


