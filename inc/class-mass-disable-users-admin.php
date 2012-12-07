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
    //if ( ! current_user_can( 'install_plugins' ) ) {
    //  wp_die('You do not have permission to access this feature.');
    //}

    // Create the submenu page
    add_action( 'network_admin_menu', array( &$this, 'add_user_submenu' ) );
    add_action( 'admin_enqueue_scripts', array( &$this, 'admin_load' ) );

  } // end function init()

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

  } // end function add_user_submenu()

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

  } // end function admin_load()


  public function render_page() {
    
    // Check nonces
    if ( ! empty( $_POST ) ) {
      $action = $_POST['mdu-action'];
      check_admin_referer( 'mdu_actions-' . $action );
    }

    // Create page heading
    $html .= '<div class="wrap">';
    $html .= screen_icon();
    $html .= '<h2>';
    $html .= __( 'Mass Disable Users' );
    $html .= '</h2>';

    // If exceptions were submitted, process them
    if ( isset( $_POST['submit_ex'] ) ) {
      $exceptions = $_POST['exceptions'];
      $this->process_exceptions( $exceptions );
    }

    // Disable selected users
    if ( isset( $_POST['disable-submit'] ) ) {
      // Run the loop to disable selected users
      $this->utility->set_to_disable( $_POST['user_select'] );
      $disabled = $this->utility->process_user_blogs();

      // Show message confirming users were disabled
      echo '<div class="updated"><p><strong>' . $disabled . ' users disabled.</strong></p></div>';
    }

    // If a CSV was uploaded, show confirmation page
    if ( isset( $_FILES['csv-file'] ) ) {

      $this->process_users( $_FILES['csv-file'] );
      $html .= '<p>';
      $html .= $this->show_confirmation();
      $html .= '</p>';

    } else {

      // The main form
      $html .= '<p>';
      $html .= $this->exception_form();
      $html .= '</p>';
      $html .= '<p>';
      $html .= $this->csv_upload_form();
      $html .= '</p>';
      $html .= '<p>';
      $html .= $this->process_users_buttons();
      $html .= '</p>';
  
    }

    $html .= '</div><!-- end .wrap -->';

    echo $html;
  } // end function render_pate()

  /**
   * Builds the exceptions form and loads existing exceptions
   */
  private function exception_form() {
  
    $exceptions = $this->utility->get_exceptions();

    $html .= '<h3>Step 1: Exclude email addresses</h3>';
    $html .= '<p>One address per line</p>';
    $html .= '<form id="exceptions" action="" method="post" enctype="multipart/form-data">';
    $html .= wp_nonce_field( 'mdu_actions-submit_ex');
    $html .= '<input type="hidden" name="mdu-action" value="submit_ex" />';
    $html .= '<textarea name="exceptions" wrap="soft" cols="40" rows="10">';
    $html .= $exceptions;
    $html .= '</textarea>';
    $html .= '<br />';
    $html .= '<input type="submit" class="button-primary" name="submit_ex" value="Update Exceptions" />';
    $html .= '</form>';

    return $html;
  
  } // end function exception_form()

  /**
   * Handles the email exceptions for later use
   *
   * @param string $exceptions List of email addresses to exclude
   */
  private function process_exceptions( $exceptions ) {
  
      $this->utility->set_exceptions( $exceptions );

      // Display success message
      echo '<div class="updated"><p><strong>Exceptions updated successfully!</strong></p></div>';
  } // end function process_exceptions()

  /**
   * Builds the upload form
   */
  private function csv_upload_form() {
  
    $html .= '<h3>Step 2: Upload CSV</h3>';
    $html .= '<p>Upload a CSV file containing the existing user email addresses</p>';
    $html .= '<form id="csv-disable" action="" method="post" enctype="multipart/form-data">';
    $html .= wp_nonce_field( 'mdu_actions-upload_form');
    $html .= '<input type="hidden" name="mdu-action" value="upload_form" />';
    $html .= '<input type="file" id="csv-file" name="csv-file" />';

    return $html;
  
  } // end function csv_upload_form()

  /**
   * Finishes the upload form by creating the action button
   */
  private function process_users_buttons() {
  
    $html .= '<h3> Step 3: Process Users </h3>';
    $html .= '<p>You will be asked to confirm the disabled users in the next screen.</p>';
    $html .= '<input name="csv-submit" id="csv-submit" type="submit" class="button-primary" value="Process Users"/>';
    $html .= '</form><!-- .csv-disable -->';

    return $html;
  
  } // end function process_users_buttons()

  /**
   * Processes the CSV file to display users to disable
   *
   * @param array $file The uploaded CSV file
   */
  private function process_users( $file ) {
  
    $this->utility->set_csv( $file['tmp_name'] );
    $this->utility->combine_users();
    $this->utility->set_users();
    $this->utility->set_to_confirm();
  
  } // end function process_users()

  /**
   * Allows user to select accounts to disable
   */
  private function show_confirmation() {
  
    $html .= '<h3>Step 4: Select the users to disable</h3>';
    $html .= '<p>Use the checkboxes to select the accounts to disable</p>';
    $html .= '<form id="confirm-disable" action="" method="post" name="confirm-disable" >';
    $html .= wp_nonce_field( 'mdu_actions-disable_users');
    $html .= '<input type="hidden" name="mdu-action" value="disable_users" />';
    $html .= '<table id="email-list" >';
    $html .= '<p>';
    $html .= $this->utility->count_to_confirm();
    $html .= ' users';
    $html .= '</p>';
    $html .= '<tr>';
    $html .= '<td>';
    $html .= '<input type="checkbox" name="selectAll" id="selectAll" />';
    $html .= '</td>';
    $html .= '<td>';
    $html .= 'Select All';
    $html .= '</td>';
    $html .= '</tr>';

    $to_confirm = $this->utility->get_to_confirm();

    foreach ( $to_confirm as $c ) {

      if ( ! $c == '' ) {
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<input type="checkbox" class="user-select" name="user_select[]" value="' . $c . '"/>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= $c;
        $html .= '</td>';
        $html .= '</tr>';
      } // end if

    } // end foreach

    $html .= '</table><!-- end #email-list -->';
    $html .= '<input type="submit" value="Disable Selected Users" name="disable-submit" class="button-primary"/>';
    $html .= '</form>';

    return $html;
  
  } // end function show_confirmation()

}
