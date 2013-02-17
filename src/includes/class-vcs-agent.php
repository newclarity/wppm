<?php

abstract class Vcs_Agent {
  protected $agent_type;
  protected $executable;
  /**
   * Instantiate the agent
   *
   * $args['executable'] to contain the executable command ( e.g. 'git' or '/usr/local/bin/git' )
   *
   * @param string $agent_type
   * @param bool|array $args
   */
  function __construct( $agent_type, $args = array() ) {
    $this->agent_type = $agent_type;

    if ( empty( $args['executable'] ) )
      $args['executable'] = $agent_type;

    $this->executable = $args['executable'];

  }

  protected function _exec( $command ) {
    exec( "{$this->executable} {$command}", $output );
    return $output;
  }

  /**
   * Returns the status of a local repository
   *
   * @return array
   */
  function status() {
    return $this->_exec( 'status' );
  }

  function out() { return $this->_not_implemented( 'out' ); }
  function clean() { return $this->_not_implemented( 'clean' ); }
  function pull( $remote, $branch ) { return $this->_not_implemented( 'pull' ); }
  function update( $revision = false ) { return $this->_not_implemented( 'update' ); }
  function log( $limit = 3 ) { return $this->_not_implemented( 'log' ); }
  function tags() { return $this->_not_implemented( 'tags' ); }
  function is_clean() { return false; }
  private function _not_implemented( $method ) {
    return array( get_class( $this ) . "->{$method}() not implemented." );
  }
}

