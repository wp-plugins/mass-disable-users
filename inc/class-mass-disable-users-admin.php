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
    add_action( 'admin_enqueue_scripts', array( &$this, 'admin_load' ) );

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
  
    $path = plugins_url( 'mass-disable-users', 'mass-disable-users');
    wp_register_script( 'mdu-upload', $path . '/js/mdu-upload.js' );
    if ( 'users_page_mass-disable-users-network' == get_current_screen()->id ) {
      wp_enqueue_script( 'jquery' );
      wp_enqueue_script( 'mdu-upload' );
    }

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
    $html .= '<p>';
    $html .= $this->csv_upload_form();
    $html .= '</p>';
    $html .= '<p>';
    $html .= $this->process_users_buttons();
    $html .= '</p>';
    $html .= '</div><!-- end .wrap -->';

    echo $html;
  
  }

  private function exception_form() {
  
    $exceptions = $this->utility->get_exceptions();

    $html = '<h3>Step 1: Exclude email addresses</h3>';
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

  private function csv_upload_form() {
  
    $html = '<h3>Step 2: Upload CSV</h3>';
    $html .= '<p>Upload a CSV file containing the existing user email addresses</p>';
    $html .= '<form id="csv-disable" action="" method="post">';
    $html .= '<input type="file" id="csv-file" name="csv-file" />';

    return $html;
  
  }

  private function process_users_buttons() {
  
    $html = '<h3> Step 3: Process Users </h3>';
    $html .= '<p>You will be asked to confirm the disabled users in the next screen.</p>';
    $html .= '<input name="csv-submit" id="csv-submit" type="submit" class="button-primary" value="Process Users"/>';
    $html .= '</form><!-- .csv-disable -->';

    return $html;
  
  }
}

$GLOBALS['mdu-admin'] = new Mass_Disable_Users_Admin;
