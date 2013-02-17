<?php

class WPPM_Link extends WPPM_Container {

  var $type;
  var $url;

  function __construct( $type, $value, $package ) {
    if ( is_object( $value ) ) {
      parent::__construct( $value, $type, $package );
    } else {
      $this->ID   = $value;
      $this->ROOT = $package;
      $this->type = $type;
      if ( preg_match( '#^https?://#', $value ) ) {
        $this->url = $value;
      } else if ( is_string( $value ) ) {
        switch ( $type ) {
          case 'github':
          case 'twitter':
            $this->url = "http://{$type}.com/{$this->ID}";
            break;

          case 'bitbucket':
            $this->url = "http://{$type}.org/{$this->ID}";
            break;

          case 'aboutme':
            $this->url = "http://about.me/{$this->ID}";
            break;

          case 'linkedin':
            $this->url = "http://www.linkedin.com/in/{$this->ID}";
            break;

          case 'website':
            $this->url = "http://{$this->ID}";
            break;

        }
      }
    }
  }
}

