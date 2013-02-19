<?php
/**
 * Based class for loading JSON.
 *
 * All base class properties UPPERCASE to avoid conflict as JSON conventions never(?) use ALL UPPERCASE.
 *
 * @todo Refactor out of WPPM and into "TCLP - Typed Config Loader for PHP"
 *
 */
abstract class WPPM_Container {

  /**
   * @var string
   */
  var $ID;

  /**
   * @var string
   */
  var $FILEPATH;

  /**
   * @var bool|WPPM_Container
   */
  var $ROOT;

  /**
   * @var array
   */
  var $CONTAINED_TYPES;

  /**
   * @var array
   */
  var $UNUSED;

  /**
   * @param string $id
   * @param bool|object|mixed $vars
   * @param bool|WPPM_Container $root
   * @param WPPM_Package
   */
  function __construct( $id, $vars, $root = false ) {
    if ( ! $root )
      $root = $this;
    $this->ID = $id;
    $this->ROOT = $root;
    if ( ! is_null( $vars ) ) {
      if ( is_object( $vars ) )
        $vars = (array) $vars;
      if ( is_array( $vars ) ) {
        foreach ( $vars as $name => $value ) {
          if ( property_exists( $this, $name ) ) {
            if ( $this->_is_convertable( $name ) ) {
              if ( is_array( $this->$name ) ) {
                $this->$name = $this->_convert_to_array( $name, $value );
              } else {
                $this->$name = $this->_convert( $name, $value, $this->CONTAINED_TYPES[$name] );
              }
            } else {
              $this->$name = $value;
            }
            unset( $vars[$name] );
          }
        }
        if ( count( $vars ) )
          $this->UNUSED = $vars;
      }
    }
    if ( $this === $this->ROOT ) {
      $this->_fixup();
      $this->_strip_CONTAINED_TYPES( $this );
    }
  }

  function show() {
    print_r( $this );
  }

  private function _is_convertable( $property ) {
    return isset($this->CONTAINED_TYPES[$property]);
  }

  private function _convert($property, $value, $class_name ) {
    return new $class_name( $property, $value, $this->ROOT );
  }

  private function _convert_to_array( $property, $json_object ) {
    $list = array();
    $class_name = $this->CONTAINED_TYPES[$property];
    if ( class_exists( $class_name ) ) {
      foreach ( (array) $json_object as $element_id => $element_value ) {
        $list[$element_id] = $this->_convert( $element_id, $element_value, $class_name );
      }
    } else {
      foreach ( (array) $json_object as $element_id => $element_value ) {
        $list[$element_id] = $element_value;
      }
    }

    return $list;
  }
  protected function _fixup() {
    foreach ( get_object_vars( $this ) as $name => $value ) {
      if ( preg_match( '#^(ROOT|CONTAINED_TYPES)$#', $name ) ) {
        continue;
      } else if ( is_object( $value ) ) {
        if ( method_exists( $value, '_fixup' ) ) {
          $value->_fixup();
        }
      } else if ( is_array( $value ) ) {
        foreach ( $value as $sub_name => $sub_value ) {
          if ( is_object( $sub_value ) && method_exists( $sub_value, '_fixup' ) ) {
            $sub_value->_fixup();
          }
        }
      }
    }
  }
  /**
   * Strip the CONTAINED_TYPES property because it's a lot of unnecessary visual noise.
   * @param $object
   */
  protected function _strip_CONTAINED_TYPES( $object ) {
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

