<?php

class Svn_Agent extends Vcs_Agent {

  /**
   * @param array $args
   */
  function __construct( $args = array() ) {
    parent::__construct( 'svn', $args );
  }

  function ignore( $ignore_files, $local_path ) {
    return $this->_exec( "propset svn:ignore \"{$ignore_files}\" \"{$local_path}/trunk\"" );
  }

  function export( $repository, $local_path ) {
    return $this->_exec( "export {$repository} {$local_path}" );
  }

  protected function _get_auth() {
    return " --username {$this->username} --password {$this->password}";
  }

  protected function _clone( $repository_url, $local_path ) {
    return $this->_exec( "checkout {$repository_url} {$local_path}" . $this->_get_auth() );
  }

  function remove( $repository_dir, $files, $switches = false ) {
    $this->_pushdir( $repository_dir );
    $output = $this->_exec( "remove {$files} {$switches}" );
    $this->_popdir();
    return $output;
  }

  function add( $repository_dir, $files, $switches = false ) {
    $this->_pushdir( $repository_dir );
    $output = $this->_exec( "add {$files} {$switches}" );
    $this->_popdir();
    return $output;
  }

  function tag( $repository_dir, $tag ) {
    $this->_pushdir( $repository_dir );
    $output = $this->commit( $repository_dir, "Adding version {$tag}" );
    $output += $this->_exec( "cp trunk tags/{$tag}" );
    $output += $this->commit( $repository_dir, "Tagging version {$tag}" );
    $this->_popdir();
    return $output;
  }

  function commit( $repository_dir, $message ) {
    $this->_pushdir( $repository_dir );
    $output = $this->_exec( "commit -m \"{$message}\"" . $this->_get_auth() );
    $this->_popdir();
    return $output;
  }

  /**
   * Cleans out all uncommitted changes from the local repository.
   *
   * @return array
   */
  function clean() {
    //return $this->_exec( "update --clean" );
  }

  /**
   * Pulls changes from a specific remote repository and branch to
   * the local repository.
   *
   * @param string $remote
   * @param string $branch
   *
   * @return array
   */
  function pull( $remote, $branch ) {
    //return $this->_exec( "pull {$remote} -b {$branch}" );
  }

  /**
   * Updates local repository to the latest or specified version
   *
   * @param bool $revision
   *
   * @return mixed
   */
  function update( $revision = false ) {
//    if( ! empty( $revision ) ) {
//      $output = $this->_exec( "update -r {$revision}" );
//    } else {
//      $output = $this->_exec( 'update' );
//    }
//    return $output;
  }

  /**
   * Display the log with:
   *
   *   -f for follow changeset history, or file history across copies and renames
   *   -v for changesets with full descriptions and file lists
   *   -l to show the {$limit} number of last commits.
   *
   * @param int $limit
   *
   * @return array
   */
  function log( $limit = 3 ) {
//    return $this->_exec( "log -v -f -l {$limit}" );
  }

  /**
   * Returns what Svn sees needs to be sent out.
   *
   * @return array
   */
  function out() {
//    return $this->_exec( "out" );
  }

  /**
   * @todo See if this is the best name for "Is the the repo up to date?"
   */
  function is_clean() {
//    $is_clean = 0 == count( $this->status() );
//    if ( $is_clean )
//      $is_clean = in_array( 'no changes found', $this->out() );
//    return $is_clean;
  }

  /**
   *
   */
  function tags() {
//    $tags = array();
//    $tags_file = getcwd() . '/.svntags';
//    if ( is_file( $tags_file ) ) {
//      $tags = explode( "\n", file_get_contents( $tags_file ) );
//      foreach( $tags as $line_no => $line ) {
//        if ( empty( $line ) ) {
//          unset( $tags[$line_no] );
//        } else {
//          list( $changeset, $tag ) = explode( ' ', "{$line} " );
//          $tags[$line_no] = preg_replace( '#^v?(.*)$#', '$1', $tag );
//        }
//      }
//    }
//    return $tags;
  }
}
