<?php

class WPPM_Package extends WPPM_Container {

  var $CONTAINED_TYPES = array(
    'author'       => 'WPPM_Contributor',
    'source'       => 'WPPM_Repository',
    'license'      => 'WPPM_License',
    'contributors' => 'WPPM_Contributor',
    'dependencies' => 'WPPM_Repository',
  );
  var $name;
  var $slug;
  var $main_file;
  var $description;
  var $url;
  var $version;
  var $donate_link;
  var $type;
  var $license;
  var $license_uri;
  var $copyright;
  var $requires_wp;
  var $tested_with;
  var $requires_php;
  var $author;
  var $contributors = array();
  var $tags;
  var $text_domain;
  var $domain_path;

  /**
   * @var WPPM_Repository
   */
  var $source;
  var $dependencies = array();
  var $bundled_dependencies = array();
  var $delete_files = array();

  var $singular_type_name;
  var $plural_type_name;
  var $plural_type;
  var $wordpress_svn_url;
  var $plugin_description;
  var $readme_description;

  function __construct( $package_filepath, $package ) {
    $this->FILEPATH = $package_filepath;

    if ( isset( $package->requires ) && ! isset( $package->requires_wp ) ) {
      $package->requires_wp = $package->requires;
      unset( $package->requires );
    }

    parent::__construct( 'package', (array)$package );

    if ( is_null( $this->type ) )
      $this->type = 'plugin';

    if ( is_null( $this->license ) )
      $package->license = 'GPLv2 or later';

    if ( is_null( $this->license_uri ) )
      $package->license_uri = 'http://www.gnu.org/licenses/gpl-2.0.html';

    if ( is_null( $this->plugin_description ) )
      $this->plugin_description = $this->description;

    if ( is_null( $this->readme_description ) )
      $this->readme_description = $this->description;

    if ( is_null( $this->description ) )
      $this->description = is_null( $this->readme_description ) ? $this->plugin_description : $this->readme_description;

    switch ( $this->type ) {
      case 'plugin':
        $this->singular_type_name = 'Plugin';
        $this->plural_type_name =   'Plugins';
        $this->plural_type =        'plugins';
        $this->wordpress_svn_url =  "http://plugins.svn.wordpress.org/{$this->slug}/";
        //$this->wordpress_svn_url =  "http://newclarity.unfuddle.com/svn/newclarity_lexity-live-for-wp-e-commerce";
        break;

      case 'theme':
        $this->singular_type_name = 'Theme';
        $this->plural_type_name =   'Themes';
        $this->plural_type =        'themes';
        $this->wordpress_svn_url =  "http://themes.svn.wordpress.org/{$this->slug}/";
        break;

      case 'library':
        $this->singular_type_name = 'Library';
        $this->plural_type_name =   'Libraries';
        $this->plural_type =        'libraries';
        break;

    }
  }
  protected function _fixup() {
    if ( is_null( $this->slug ) )
      $this->slug = strtolower( str_replace( ' ', '-', $this->name ) );

    if ( is_null( $this->text_domain ) )
      $this->text_domain = $this->slug;

    if ( is_null( $this->domain_path ) )
      $this->domain_path = '/languages';

    if ( is_null( $this->main_file ) )
      $this->main_file = "{$this->slug}.php";

    parent::_fixup();

    if ( empty( $this->license ) ) {
      $this->license = new WPPM_License( 'license', null, $this->ROOT );
    }

    if ( ! empty( $this->license_uri ) ) {
      $this->license->url = $this->license_uri;
      unset( $this->license_uri );
    }

    if ( 0 == count( $this->bundled_dependencies ) ) {
      if ( count( $this->dependencies ) ) {
        $this->bundled_dependencies = $this->dependencies;
      }
    } else {
      $bundled_dependencies = array();
      foreach ( $this->bundled_dependencies as $dependency_id ) {
        if ( is_string( $dependency_id ) && isset( $this->dependencies[$dependency_id] ) ) {
          $bundled_dependencies[$dependency_id] = $this->dependencies[$dependency_id];
        }
      }
      $this->bundled_dependencies = $bundled_dependencies;
    }
  }

}


