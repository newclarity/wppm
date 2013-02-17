<?php

class WPPM_Contributor extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'links' => 'WPPM_Link',
  );
  var $type;
  var $name;
  var $email;
  var $homepage;
  var $links = array();
  function __construct( $type, $value, $config ) {
    parent::__construct( $type, $value, $config );
    if ( is_null( $this->name ) && is_string( $value ) ) {
      /**
       * The file contains a string name for author, not multiple subproperties
       * This means 'author_homepage' is probably also included, ala this:
       *   http://1.shadowcdn.com/files/external-update-example/info.json
       * From this:
       *   http://w-shadow.com/blog/2010/09/02/automatic-updates-for-any-plugin/
       */
      $this->name = $value;
    }
    $this->type = $type;
    if ( is_null( $this->homepage ) ) {
      if ( isset( $this->links['homepage'] ) )
        /**
         * Homepage not set, but is set in links.
         */
        $this->homepage = $this->links['homepage']->url;
      else if ( count( $this->links ) ) {
        /**
         * Homepage not set, get the first link in the links array.
         */
        $this->homepage = reset( $this->links )->url;
      }
    } else if ( ! preg_match( '#^https?://#', $this->homepage ) ) {
      /**
       * Not a URL but a key into the links array.
       */
      $this->homepage = $this->links[$this->homepage]->url;
    }
  }
  function _fixup() {
    if ( 'author' == $this->ID ) {

      if ( is_null( $this->homepage ) && isset( $this->ROOT->UNUSED['author_homepage'] ) ) {
        $this->homepage = $this->ROOT->UNUSED['author_homepage'];
        unset( $this->ROOT->UNUSED['author_homepage'] );
      }

      $found = false;
      foreach( $this->links as $link_id => $link ) {
        if ( $link->url == $this->homepage ) {
          $found = true;
          break;
        }
      }
      if ( ! $found )
        $this->links['homepage'] = $this->homepage;

      return;
    }
  }
}
