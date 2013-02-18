<?php

class Vcs_Interface {
  private static $_agent_types = array(
    'git' => 'Git_Agent',
    'hg'  => 'Hg_Agent',
    'svn' => 'Svn_Agent'
  );
  /**
   * Factory to instantiate an VCS agent given an agent type.
   *
   * @param string $agent_type
   * @param array $args
   *
   * @return Vcs_Agent
   */
  static function get_agent( $agent_type, $args = array() ) {
    $agent = false;
    if ( self::has_agent_type( $agent_type ) ) {
      $agent_class = self::$_agent_types[$agent_type];
      if ( ! class_exists( $agent_class, false ) )
        require( dirname( __DIR__ ) . "/vcs-agents/class-{$agent_type}-agent.php" );

      if ( empty( $args['executable'] ) )
        $args['executable'] = "/usr/bin/{$agent_type}";

      if ( is_file( $args['executable'] ) )
        $agent = new $agent_class( $args );
    }
    return $agent;
  }

  /**
   * @param string $agent_type
   *
   * @return bool
   */
  static function has_agent_type( $agent_type ) {
    return isset( self::$_agent_types[$agent_type] );
  }

}


