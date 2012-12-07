<?php

/**
 * Plugin Name: Mass Disable Users
 * Plugin URI: https://github.com/channeleaton/WP-Mass-Disable-Users
 * Description: Allows network administrators to disable users via CSV while retaining all author information
 * Version: 0.1
 * Author: J. Aaron Eaton
 * Author URI: http://channeleaton.com
 * License: GPL2
 */

require_once( 'inc/class-mass-disable-users-utilities.php' );
require_once( 'inc/class-mass-disable-users-admin.php' );

if ( ! array_key_exists( 'mass-disable-users', $GLOBALS ) ) {

  class Mass_Disable_Users {
    
    function __construct() {

      $this->utility  = new Mass_Disable_Users_Utilities;
      $this->admin    = new Mass_Disable_Users_Admin;

      // Setup network admin area
      add_action( 'init', array( $this->admin, 'init' ) );

      // Setup options
      add_action( 'init', array( $this->utility, 'add_options' ) );

    } // end function __construct()

  } // end class Mass_Disable_Users

  $GLOBALS['mass-disable-users'] = new Mass_Disable_Users;

} // endif
