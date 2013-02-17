<?php

class Hg_Agent extends Vcs_Agent {

  /**
   * @param array $args
   */
  function __construct( $args = array() ) {
    parent::__construct( 'hg', $args );
  }

  /**
   * Cleans out all uncommitted changes from the local repository.
   *
   * @return array
   */
  function clean() {
    return $this->_exec( "update --clean" );
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
    return $this->_exec( "pull {$remote} -b {$branch}" );
  }

  /**
   * Updates local repository to the latest or specified version
   *
   * @param bool $revision
   *
   * @return mixed
   */
  function update( $revision = false ) {
    if( ! empty( $revision ) ) {
      $output = $this->_exec( "update -r {$revision}" );
    } else {
      $output = $this->_exec( 'update' );
    }
    return $output;
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
    return $this->_exec( "log -v -f -l {$limit}" );
  }

  /**
   * Returns what Hg sees needs to be sent out.
   *
   * @return array
   */
  function out() {
    return $this->_exec( "out" );
  }

  /**
   * @todo See if this is the best name for "Is the the repo up to date?"
   */
  function is_clean() {
    $is_clean = 0 == count( $this->status() );
    if ( $is_clean )
      $is_clean = in_array( 'no changes found', $this->out() );
    return $is_clean;
  }

  /**
   *
   */
  function tags() {
    $tags = array();
    $tags_file = getcwd() . '/.hgtags';
    if ( is_file( $tags_file ) ) {
      $tags = explode( "\n", file_get_contents( $tags_file ) );
      foreach( $tags as $line_no => $line ) {
        if ( empty( $line ) ) {
          unset( $tags[$line_no] );
        } else {
          list( $changeset, $tag ) = explode( ' ', "{$line} " );
          $tags[$line_no] = preg_replace( '#^v?(.*)$#', '$1', $tag );
        }
      }
    }
    return $tags;
  }
}
