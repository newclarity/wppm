<?php

class WPPM_License extends WPPM_Container {
  var $type;
  var $url;
  var $text;
  function __construct( $domain, $value, $config ) {
    parent::__construct( $domain, null, $config );
    $gpl2_url = "http://www.gnu.org/licenses/gpl-2.0.html";
    $gpl2_text = <<<TEXT
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
TEXT;
    if ( is_null( $this->type ) ) {
  	  $this->type = "GPL v2 or later";
      if ( is_null( $this->url ) )
  	    $this->url = "http://www.gnu.org/licenses/gpl-2.0.html";
      if ( is_null( $this->text ) )
      	$this->text = $gpl2_text;
    }
    if ( preg_match( '#GPL.*2#', $this->type ) ) {
      if ( is_null( $this->url ) )
        $this->url = $gpl2_url;
      if ( is_null( $this->text ) )
  	    $this->text = $gpl2_text;
    }
  }
}
