<?php

require_once( 'class-mass-disable-users-utilities.php' );

class Mass_Disable_Users_Admin {
  
  /**
   * Class setup
   */
  function __construct() {
  
    $this->utility = new Mass_Disable_Users_Utilities;
  
  }
  
  /**
   * Fires required hooks
   */
  public function init() {
  
    // Check to see if user is a Super Admin
    if( ! current_user_can( 'manage_network' ) )
      die( 'Nice try!' );

    // Create the submenu page
    add_action( 'network_admin_menu', array( &$this, 'add_user_submenu' ) );

    // Load the data handlers
    add_action( 'load-$page', array( &$this, 'admin_load' ) );
    add_action( 'load-$page', array( &$this, 'compare_tables' ) );
    add_action( 'load-$page', array( &$this, 'take_action' ) );
  
  }

  /**
   * Add a submenu page to the Users menu in the network admin area
   */
  public function add_user_submenu() {
    
    $menu = add_submenu_page(
      'users.php',                    // Parent page slug
      'Mass Disable Users',           // Page title
      'Mass Disable Users',           // Sub-menu title
      'manage_network',               // Required capability
      'mass-disable-users',           // Menu slug
      array( &$this, 'render_page' )  // Page display callback
    );

    return $menu;

  }

  /**
   * Register & enqueue the CSS & Javascript files.
   *
   * @TODO - Create help tab
   */
  public function admin_load() {
  
  }

  /**
   * Handles the email exceptions for later use
   */
  public function take_action() {
  
    if( empty( $_POST ) )
      return;

    if( isset( $_POST['exceptions'] ) ) {
      check_admin_referrer( 'email-exceptions', '_wpnonce-email-exceptions' );
      $this->utility->update_exceptions( $_POST['exceptions'] );
    
    }
  
  }

  public function render_page() {
    
    $html = '<div class="wrap">';
    $html .= screen_icon();
    $html .= '<h2>';
    $html .= __( 'Mass Disable Users' );
    $html .= '</h2>';
    $html .= '</div><!-- end .wrap -->';

    echo $html;
  
  }

}

$GLOBALS['mdu-admin'] = new Mass_Disable_Users_Admin;
