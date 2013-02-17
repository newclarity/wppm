<?php

class Git_Agent extends Vcs_Agent {

  /**
   * @param array $args
   */
  function __construct( $args = array() ) {
    parent::__construct( 'git', $args );
  }

  /**
   * Cleans out all uncommitted changes from the local repository.
   *
   * @return array
   */
  function clean() {
    return $this->_exec( "reset --hard HEAD" );
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
    return $this->_exec( "pull {$remote} {$branch}" );
  }

  /**
   * Updates local repository to the latest version
   *
   * @param bool $revision
   *
   * @return array
   */
  function update( $revision = false ) {
    if( ! empty( $revision ) ) {
      $output = $this->_exec( "checkout {$revision}" );
    } else {
      $output = $this->_exec( 'checkout' );
    }
    return $output;
  }

  /**
   * Display the log with:
   *
   *   -<n> Limits the number of commits to show to {$limit}.
   *
   * @param int $limit
   *
   * @return array
   */
  function log( $limit = 3 ) {
    return $this->_exec( "log -{$limit}" );
  }

  /**
   *
   */
  function tags() {
    return $this->_exec( 'tag -l' );

    return $output;
  }

}



