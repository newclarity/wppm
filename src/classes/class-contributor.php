<?php

class WPPM_Contributor extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'links' => 'WPPM_Link',
  );
  var $name;
  var $email;
  var $links = array();
}
