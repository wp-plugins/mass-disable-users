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

    // Create the submenu page
    add_action( 'network_admin_menu', array( &$this, 'add_user_submenu' ) );

    // Load the data handlers
  
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


  public function render_page() {
    
    if( isset( $_POST['submit_ex'] ) ) {
      $exceptions = $_POST['exceptions'];
      $this->process_exceptions( $exceptions );
    }

    $html = '<div class="wrap">';
    $html .= screen_icon();
    $html .= '<h2>';
    $html .= __( 'Mass Disable Users' );
    $html .= '</h2>';
    $html .= '<p>';
    $html .= $this->exception_form();
    $html .= '</p>';
    $html .= '</div><!-- end .wrap -->';

    echo $html;
  
  }

  private function exception_form() {
  
    $exceptions = $this->utility->get_exceptions();

    $html = '<h3>Exclude email addresses</h3>';
    $html .= '<p>One address per line</p>';
    $html .= '<form id="exceptions" action="" method="post">';
    $html .= '<textarea name="exceptions" cols="40" rows="10">';
    $html .= $exceptions;
    $html .= '</textarea>';
    $html .= '<br />';
    $html .= '<input type="submit" class="button-primary" name="submit_ex" value="Update Exceptions" />';
    $html .= '</form>';

    return $html;
  
  }

  /**
   * Handles the email exceptions for later use
   */
  private function process_exceptions( $exceptions ) {
  
      $this->utility->update_exceptions( $exceptions );
?>
  <div class="updated"><p><strong><?php _e('Exceptions updated successfully!'); ?></strong></p></div>
<?php
    
  }
}

$GLOBALS['mdu-admin'] = new Mass_Disable_Users_Admin;
