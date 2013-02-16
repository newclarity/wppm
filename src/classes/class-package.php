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

  function show() {
    print_r( $this );
  }

  function __construct( $id, $vars ) {
    parent::__construct( $id, $vars, $this );

    if ( is_null( $this->slug ) )
      $this->slug = strtolower( str_replace( ' ', '-', $this->name ) );

    $this->_fixup();
    $this->_strip_CONTAINED_TYPES( $this );
  }

  protected function _fixup() {
    parent::_fixup();
    $bundledDependencies = array();
    foreach ( $this->bundledDependencies as $dependency_id ) {
      if ( isset($this->dependencies[$dependency_id]) ) {
        $bundledDependencies[$dependency_id] = $this->dependencies[$dependency_id];
      }
    }
    $this->bundledDependencies = $bundledDependencies;
  }

  /**
   * Strip the CONTAINED_TYPES property because it's a lot of unnecessary visual noise.
   * @param $object
   */
  private function _strip_CONTAINED_TYPES( $object ) {
    foreach ( get_object_vars( $object ) as $name => $value ) {
      if ( 'ROOT' == $name ) {
        continue;
      } else if ( 'CONTAINED_TYPES' == $name ) {
        unset( $object->CONTAINED_TYPES );
      } else if ( is_object( $value ) ) {
        $this->_strip_CONTAINED_TYPES( $value );
      } else if ( is_array( $value ) ) {
        foreach ( $value as $sub_name => $sub_value ) {
          if ( is_object( $sub_value ) )
            $this->_strip_CONTAINED_TYPES( $sub_value );
        }
      }
    }
  }
}


