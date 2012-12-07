<?php

class Mass_Disable_Users_Admin {
  
  /**
   * Fires required hooks
   */
  public function init() {
  
    add_action( 'network_admin_menu', array( &$this, 'add_user_submenu' ) );
  
  }

  /**
   * Add a submenu page to the Users menu in the network admin area
   */
  public function add_user_submenu() {
    
    add_submenu_page(
      'users.php',                    // Parent page slug
      'Mass Disable Users',           // Page title
      'Mass Disable Users',           // Sub-menu title
      'manage_network',               // Required capability
      'mass-disable-users',           // Menu slug
      array( &$this, 'render_page' )  // Page display callback
    );

  }

  public function render_page() {
  
    $html = '<div class="wrap">';
    $html .= '<h2>';
    $html .= __( 'Mass Disable Users' );
    $html .= '</h2>';
    $html .= '</div><!-- end .wrap -->';

    echo $html;
  
  }

}
