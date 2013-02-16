<?php

class WPPM_Link extends WPPM_Container {

  var $type;
  var $url;

  function __construct( $type, $value, $package ) {
    if ( is_object( $value ) ) {
      parent::__construct( $value, $type, $package );
    } else {
      $this->id   = $value;
      $this->ROOT = $package;
      $this->type = $type;
      if ( preg_match( '#^https?://#', $value ) ) {
        $this->url = $value;
      } else if ( is_string( $value ) ) {
        switch ( $type ) {
          case 'github':
          case 'twitter':
            $this->url = "http://{$type}.com/{$this->id}";
            break;

          case 'bitbucket':
            $this->url = "http://{$type}.org/{$this->id}";
            break;

          case 'aboutme':
            $this->url = "http://about.me/{$this->id}";
            break;

          case 'linkedin':
            $this->url = "http://www.linkedin.com/in/{$this->id}";
            break;

          case 'website':
            $this->url = "http://{$this->id}";
            break;

        }
      }
    }
  }
}

