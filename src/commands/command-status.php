<?php
class WPPM_Status_Command extends WPPM_Command {

  /**
   * @param WP_Package_Manager $wppm
   */
  function execute( $wppm ) {
    $package = $wppm->parse_package();
    $status = "\n\tSTATUS\n\t------\n";

    $package = $wppm->parse_package();

    $status .= "\tPackage Name: {$package->name}\n";
    $status .= "\tPackage Version: {$package->version}\n";

    if ( ! isset( $package->source->vcs ) )
      $wppm->fail( "ERROR: No 'vcs' defined in the 'source' property within wp-package.json." );

    $config = $wppm->parse_config();

    $vcs = $package->source->vcs;
    if ( ! isset( $config->executables[$vcs] ) )
      $wppm->fail( "ERROR: No executable defined for version control system '{$vcs}' within /.wppm/config.php." );

    if ( ! is_file( $vcs_filepath = $config->executables[$vcs]->filepath ) )
      $wppm->fail( "ERROR: Version Control System file {$vcs_filepath} does not exist." );

    $agent = Vcs_Interface::get_agent( $vcs, array( 'executable' => $vcs_filepath ) );

    if ( ! $agent->is_clean() ) {
      $status = $agent->status();
      if ( 0 == count( $status ) )
        $status = $agent->out();
      $this->add_error( "Repository not clean:\n\t\t" . implode( "\n\t\t", $status ) );
    }
    $tags = $agent->tags();
    $repository_version = array_pop( $tags );

    if ( $repository_version != $package->version ) {
      $this->add_error( "Version mismatch: Package version not equal to latest Repository Tag: {$repository_version}." );
    }

    $readme_files = array(
      'description' => true,
      'installation' => true,
      "changelog-{$package->version}" => true,
      'screenshots' => false,
      'license' => false,
      'faq' => false,
     );
    foreach( $readme_files as $readme_file => $required ) {
      $filepath = "/readme/{$readme_file}.txt";
      $full_filepath = getcwd() . $filepath;
      if ( ! is_file( $full_filepath ) ) {
        if ( $required ) {
          $this->add_error( "File {$filepath} not found." );
        } else {
          $this->add_notice( "File {$filepath} not found." );
        }
      } else {
        $contents = file_get_contents( $full_filepath );
        if ( empty( $contents ) ) {
          if ( $required ) {
            $this->add_error( "Required file {$filepath} is empty." );
          } else {
            $this->add_notice( "File {$filepath} is empty." );
          }
        }
      }
    }

    $messages = count( $this->messages ) ? "\n\t" . implode( "\n\t", $this->messages ) : false;
    $errors = count( $this->errors ) ? "\n\t" . implode( "\n\t", $this->errors ) : false;

    $status .= "{$messages}{$errors}";
    if ( 0 == count( $this->errors ) ) {
      $status .= "\n\tNO CRITICAL PROBLEMS FOUND.\n";
    }
    $this->show( $status );
  }
}
