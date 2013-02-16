<?php

class WPPM_Config extends WPPM_Container {
  var $CONTAINED_TYPES = array(
    'hosts' => 'WPPM_Host',
  );
  var $executables = array();
  var $hosts = array();
}
