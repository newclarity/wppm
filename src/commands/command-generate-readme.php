<?php
class WPPM_Generate_Readme_Command extends WPPM_Command {
  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $generator = new WPPM_Readme_Generator();
    $generator->initialize( $this->_load_readme_content( $package ) );
    $readme_txt = $generator->generate_readme();
    file_put_contents( getcwd() . '/readme.txt', $readme_txt );
    echo "\nreadme.txt generated.\n\n";

  }
  private function _load_readme_content( $package ) {
    $sections = $this->_load_sections( getcwd() . '/readme' );
    $contributors = implode( ', ', array_keys( $package->contributors ) );
    $contributors = $package->author->name . ( ! empty( $contributors ) ? ", {$contributors}" : false );
    return array(
    	'project_name'	      => $package->name,
    	'contributors'	      => $contributors,
    	'donate_link'		      => $package->donate_link,
    	'tags'			          => implode( ', ', $package->tags ),
    	'requires'			      => $package->requires_wp,
    	'tested'			        => $package->tested_with,
    	'stable_tag'		      => $package->stable_tag,
    	'license'			        => $package->license->type,
      'license_uri'			    => $package->license->url,
      'license_text'			  => $package->license->text,
    	'short_description'   => trim( $package->readme_description ),
    	'description'			    => $sections['description'],
    	'installation'			  => $sections['installation'],
    	'screenshots'			    => $sections['screenshots'],
    	'faq'			            => $sections['faq'],
    	'changelogs'			    => implode( "\n\n", $sections['changelogs'] ),
    	'upgrade_notices'			=> implode( "\n\n", $sections['upgrade_notices'] ),
    );
  }
  private function _load_sections( $readme_dir ) {
    $sections = $arbitrary = array();
    if ( $handle = opendir( $readme_dir ) ) {
      while ( false !== ( $section_file = readdir( $handle ) ) ) {
        if ( '.' == $section_file || '..' == $section_file )
          continue;
        /**
         * Strip the .txt extension,  if there is one (subdirs will not have)
         */
        $section = preg_replace( "#^(.*?)(\.txt)?$#", '$1', $section_file );
        if ( is_dir( "{$readme_dir}/{$section}" ) && preg_match( '#^changelogs|upgrade-notices$#', $section ) ) {
          /**
           * If a subdir for changelog or upgrade-notice
           */
          $sections[$section] = $this->_load_version_log( $readme_dir, $section );
        } else if ( preg_match( '#^description|installation|faq|screenshots$#', $section ) ) {
          /**
           * If one of the known sections
           */
          $sections[$section] = file_get_contents( "{$readme_dir}/{$section_file}" );
        } else {
          /**
           * If one of the arbitrary/unknown sections
           */
          $arbitrary[$section] = file_get_contents( "{$readme_dir}/{$section_file}" );
        }
      }
      closedir( $handle );
    }
    /**
     * Now make sure they are in the correct order
     */
    $output = array();
    foreach( explode( '|', 'description|installation|changelogs|faq|screenshots|upgrade-notices' ) as $section ) {
      if ( ! isset( $sections[$section] ) ) {
        $section_content = false;
      } else {
        $section_content = is_array( $sections[$section] ) ? $sections[$section] : trim( $sections[$section] );
      }
      $output[str_replace( '-', '_', $section)] = $section_content;
    }
    /**
     * Finally add the arbitrary sections at the end
     */
    foreach( $arbitrary as $section => $content ) {
      $output[$section] = $content;
    }
  return $output;
  }

  /**
  * Load the version log content.
  *
  * Version logs will either be Changelog or Upgrade Notice.
  *
  * @param $directory
  * @param $section
  *
  * @return string
  */
  private function _load_version_log( $directory, $section ) {
    $version_log = array();
    if ( $handle = opendir( $section_dir = "{$directory}/{$section}" ) ) {
    while ( false !== ( $file = readdir( $handle ) ) ) {
      $section = rtrim( $section, 's' );
        if ( '.' == $file || '..' == $file ) {
          continue;
        } else if ( preg_match( "#^{$section}-#", $file ) ) {
          $version = preg_replace( "#^{$section}-(.*?).txt$#", '$1', $file );
          $content = trim( file_get_contents( "{$section_dir}/{$file}" ), "\n" );
          if ( ! preg_match( "#^= {$version} =#", $content ) ) {
            list( $first_line ) = explode( "\n", "{$content}\n" );
            $first_line = trim( trim( trim( $first_line ), '=' ) ); // Strip Markdown head markup
            $this->logger->error( "Mismatched or missing version in section file [{$section}/{$file}]: {$first_line}" );
          }
          $version_log[$version] = $content;
        }
    }
    closedir( $handle );
    }
    /**
     * Sort the array where the versions are array element keys.
     * This will sort in numeric order so we'll need to reverse to get descending order.
     */
    uksort( $version_log, 'version_compare' );
    /**
     * By default version_compare() sort in ascending order so we'll need to reverse to get descending order.
     */
    return array_reverse( $version_log );
  }
}
