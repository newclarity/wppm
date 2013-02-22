<?php
class WPPM_Generate_Header_Command extends WPPM_Command {

  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->get_package();
    $config = $wppm->get_config();
    $generator = new WPPM_Header_Generator();
    $generator->initialize( $this->_load_header_content( $package ) );
    $header_content = $generator->generate_header();
    $this->_replace_header( $package, getcwd() . "/{$package->main_file}", $header_content );
    $this->logger->message( "\n{$package->main_file} generated." );
  }
  private function _load_header_content( $package ) {
    $header_content = array(
      'project_type_name'	=> $package->singular_type_name,
      'project_type'	=> $package->type,
    	'project_name'	=> $package->name,
    	'version'		    => $package->version,
      'copyright'			=> $package->copyright,
      'license'			  => $package->license->type,
      'license_uri'	  => $package->license->url,
      'license_text'	=> implode( "\n * ", explode( "\n", trim( $package->license->text ) ) ),
    	'description'	  => trim( $package->plugin_description ),
      'project_uri'		=> $package->url,
      'author'			  => $package->author->name,
      'author_uri'		=> $package->author->url,
      'text_domain'		=> $package->text_domain,
      'domain_path'		=> $package->domain_path,
    );
    return $header_content;
  }
  private function _replace_header( $package, $header_file, $new_header_content ) {
    $old_content = trim( is_file( $header_file ) ? file_get_contents( $header_file ) : false );
    if ( empty( $old_content ) ) {
      $new_content = $new_header_content;
    } else {
      $header_end_pos = strpos( $old_content, '*/' ) + 2;
      $old_header_content = substr( $old_content, 0, $header_end_pos );
      $regex = "#{$package->singular_type_name} Name:(.*)#";
      preg_match( $regex, $old_header_content, $match );
      if ( 2 == count( $match ) && false === strpos( $match[1], $package->name ) ) {
        $match[1] = trim( $match[1] );
        $this->logger->error( "Cannot overwrite header; {$package->type} name mismatch: [{$match[1]}] != [{$package->name}]" );
      }
      $new_content = $new_header_content . substr( $old_content, $header_end_pos );
    }
    return file_put_contents( $header_file, $new_content );
  }
}
