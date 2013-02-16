<?php

class WPPM_Host extends WPPM_Container {
  var $CONTAINED_TYPES = array(
    'accounts' => 'WPPM_Account',
  );
  var $accounts = array();
}
