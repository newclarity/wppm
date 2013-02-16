<?php

abstract class WPPM_Container {

  var $id;
  var $ROOT;
  var $CONTAINED_TYPES;

  /**
   * @param string $id
   * @param bool|object|mixed $vars
   * @param WPPM_Package
   */
  function __construct( $id, $vars, $root ) {
    $this->id = $id;
    $this->ROOT = $root;
    if ( is_object( $vars ) )
      $vars = (array) $vars;
    if ( is_array( $vars ) )
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
    if ( count( $vars ) ) {
      echo "WARNING: These property/values were not recognized inside JSON container [{$id}]:\n";
      foreach ( $vars as $name => $value ) {
        if ( is_object( $value ) )
          $value = "**(object)**";
        else if ( is_array( $value ) )
          $value = "**(array)**";
        echo "\n\t{$name}: {$value}\n";
      }
    }
  }

  static function get_pretty_name() {
    return preg_replace( '#^WPPM_(.*)$#', '$1', str_replace( '_', ' ', __CLASS__ ) );
  }

  private function _is_convertable( $property ) {
    return isset($this->CONTAINED_TYPES[$property]);
  }

  private function _convert($property, $value, $class_name ) {
    return new $class_name( $property, $value, $this->ROOT );
  }

  private function _convert_to_array( $property, $json_object ) {
    $list       = array();
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
}

