<?php

class Mass_Disable_Users_Utilities {

  protected $exceptions;
  protected $csv;
  protected $users;
  protected $to_confirm;
  protected $to_disable;


  /**
   * Creates the following options:
   * - Exceptions
   */
  public function add_options() {
  
    add_option( 'mdu_email_exceptions', array( 'agrilifeweb@tamu.edu', 'test@test.com' ) );
  
  } // end function add_options()

  /**
   * Updates the email exceptions list
   *
   * @param string $exceptions Delimited list of email addresses to exclude from disable list.
   */
  public function set_exceptions( $string ) {
  
    // Convert exceptions to array
    $exceptions = $this->string_to_array( $string );

    // Sanitize!
    $exceptions = $this->sanitize_emails( $exceptions );

    update_option( 'mdu_email_exceptions', $exceptions );
    $this->exceptions = $exceptions;

  } // end function set_exceptions()

  /** Sanitizes email addresses for exceptions
   *
   * @param array $emails Array of dirty, dirty email addresses
   * @returns array $clean Array of squeaky clean email addresses
   */
  private function sanitize_emails( $emails ) {
  
    foreach ( $emails as $e ) {

      $e = trim( $e );
      $e = wp_strip_all_tags( $e );
      
      if ( is_email( $e ) === $e ) {
        $clean[] = $e;
      } else {
        echo '<div id="message" class="error"><p><strong>' . $e . ' is not a valid email address</strong></p></div>';
        break;
      }
    }

    return $clean;
  
  } // end function sanitize_emails()

  /**
   * Retrieves the array of exceptions
   *
   * @returns string $exceptions List of email addresses to exclude
   */
  public function get_exceptions() {
  
    $exceptions = get_option( 'mdu_email_exceptions' );
    $exceptions = $this->array_to_string( $exceptions );

    return $exceptions;
    
  } // end function get_exceptions()

  /**
   * Sets the property $this->csv
   *
   * @param string $filename The temp filename/location of the uploaded CSV
   */
  public function set_csv( $filename ) {
  
    $csv = $this->parse_csv( $filename );
    $this->csv = $csv;
  
  } // end function set_csv()

  /**
   * Retrieves $this->csv
   *
   * @returns array $this->csv Array of email addresses from the CSV
   */
  public function get_csv() {
    
    return $this->csv;

  } // end function get_csv()

  /**
   * Parses the CSV into array
   *
   * @param string $filename Path to CSV file
   * @returns array $users Users in the CSV
   */
  private function parse_csv( $filename ) {
  
    $file = fopen( $filename, 'r' );
    while ( ! feof( $file ) ) {
      $user_array[] = fgetcsv( $file );
    }

    // Since fgetcsv returns a multi-dimensional array, we need to pull the email address out, making it an array value.
    foreach ( $user_array as $u ) {
      $users[] = strtolower($u[0]);
    }

    // fgetcsv also returns false when it hits the end of a file, messing up our array. Let's pop that sucker off.
    array_pop( $users );

    return $users;
  
  } // end function parse_csv()

  /**
   * Combines users from the provided CSV and the exceptions
   *
   * @returns array $combined Combined array of users who should exist
   */
  public function combine_users() {
  
    $exceptions = get_option( 'mdu_email_exceptions' );

    $csv = $this->get_csv();
    
    $combined = array_merge( $csv, $exceptions );

    return $combined;

  } // end function combine_users()

  /**
   * Pulls the email addresses of all existing users from the database
   * and sets $this->users
   */
  public function set_users() {
  
    global $wpdb;

    $sort = "ID";

    $all_users = $wpdb->get_col( $wpdb->prepare(
      "SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC",
      $sort
    ));

    foreach ( $all_users as $id ) {
      $user = get_userdata( $id );
      $emails[] = strtolower($user->user_email);
    }

    $this->users = $emails;
  
  } // end function set_users()

  /**
   * Retrieves $this->users
   *
   * @returns array $this->users Array of existing users
   */
  public function get_users() {
    
    return $this->users;

  } // end function get_users()

  /**
   * Sets the users that need to be confirmed $this->to_confirm
   */
  public function set_to_confirm() {
  
    $csv        = $this->get_csv();
    $compared   = $this->compare_users( $csv );
    $to_confirm = $this->remove_subscribers( $compared );

    $this->to_confirm = $to_confirm;
  
  } // end function set_to_confirm()

  /**
   * Retrieves $this->to_confirm
   *
   * @returns array $this->to_confirm Array of users to confirm
   */
  public function get_to_confirm() {
  
    return $this->to_confirm;
  
  } // end function get_to_confirm()

  /**
   * Counts the number of addresses in $this->to_confirm
   *
   * @returns int $count
   */
  public function count_to_confirm() {
  
    $d = $this->get_to_confirm();

    $count = count( $d );

    return $count;
  
  } // end function count_to_confirm()

  /**
   * Compares existing users with those provided in the CSV and exceptions
   *
   * @returns array $to_disable Array of users to disable
   */
  private function compare_users( $csv ) {
  
    $combined = $this->combine_users( $csv );

    $existing = $this->get_users();

    $to_disable = array_diff( $existing, $combined );

    return $to_disable;
  
  } // end function compare_users()

  /**
   * Sets the users selected to disable as user IDs $this->to_disable
   *
   * @param array $emails Array of email addresses to disable
   */
  public function set_to_disable( $emails ) {
  
    foreach ( $emails as $e ) {
      if ( email_exists( $e ) ) {
        $to_disable[] = email_exists( $e );
      }
    }

    $this->to_disable = $to_disable;
  
  } // end function set_to_disable()

  /**
   * Retrieves $this->to_disable()
   *
   * @returns array $this->to_disable Array of user IDs to disable
   */
  public function get_to_disable() {
  
    return $this->to_disable;
  
  } // end function get_to_disable()

  /**
   * Find and loop through a user's blogs, making user a subscriber in each
   *
   * @returns int $count Number of users to disable
   */
  public function process_user_blogs() {
  
    $count = count( $this->get_to_disable() );

    foreach ( $this->get_to_disable() as $user ) {

      $user_blogs = get_blogs_of_user( $user );
      
      foreach ( $user_blogs as $blog ) {
        switch_to_blog( $blog->userblog_id );
        $this->disable_user( $user );
      }

    }

    return $count;
  
  } // end function process_user_blogs()

  /**
   * Changes the given user to subscriber
   *
   * @param int $user
   */
  public function disable_user( $user ) {
  
      $user = new WP_User( $user );
      $user->set_role( 'subscriber' );
  
  } // end function disable_user()

  /**
   * Removes users who are either already subscribers on all blogs
   * or don't have permissions on any blogs
   *
   * @param array $users Array of email addresses that don't exist in CSV
   * @returns array $emails Array of email addresses of only contributors+
   */
  private function remove_subscribers( $users ) {
  
    // Construct array of users ID and email address
    foreach ( $users as $u ) {

      if ( email_exists( $u ) ) {
        $user['id']     = email_exists( $u );
        $user['email']  = $u;

        $all_users[] = $user;
      }

    }

    foreach ( $all_users as $k => $u ) {

      $blogs = get_blogs_of_user( $u['id'] );

      // Get the users role in each blog
      foreach ( $blogs as $b ) {
        $blogid = $b->userblog_id;
        switch_to_blog( $blogid );
        $roles[] = $this->get_blog_role( $u['id'], $blogid );
      } // end foreach

      // The $roles array is pretty messy. Clean it up!
      if ( ! empty( $roles) ) {

        foreach ( $roles as $role ) {
          $clean_roles[] = $role[0];
        } // end foreach

      } else {
        unset( $all_users[$k] );
      } // end if

      // Create testing array
      if ( ! empty( $clean_roles ) ) {

        foreach ( $clean_roles as $role ) {

          if ( $role == 'subscriber' ){
            $test[] = TRUE;
          } else {
            $test[] = FALSE;
          } // end if

        } // end foreach

      } // end if

      // Get rid of users with zero permissions
      if ( $test === null ) {
        unset( $all_users[$k] );
      }

      // Unset users who are ONLY subscribers
      if ( ! $test == null ){

        if ( ! in_array( FALSE, $test ) ) {
          unset( $all_users[$k] );
        } // end if

      } // end if

      // Clean-up
      unset($test);
      unset( $clean_roles );
      unset( $roles );
    } // end foreach
  
    // Construct the array for returning
    foreach ( $all_users as $u ) {
      $emails[] = $u['email'];
    }

    return $emails;

  } // end function remove_subscribers()

  /**
   * Gets the users role in the selected blog
   *
   * @param int $user User's ID
   * @param int $blogid The selected blog ID
   * @returns array $userroles Array of user's role in the selected blog
   */
  private function get_blog_role( $user, $blogid ) {

    $theuser = new WP_User( $user, $blogid );

    if ( ! empty( $theuser->roles) && is_array( $theuser->roles ) ) {

      foreach ( $theuser->roles as $role ) {
        $userroles[] = $role;
      } // end foreach

    } // end if
    return $userroles;

  } // end function get_blog_role()

  /**
   * Wrapper for explode()
   *
   * This makes it easier to change the delimiter in the future
   *
   * @param string $string String to convert to array.
   * @return array Array of strings
   */
  public function string_to_array( $string ) {
  
    $array = explode( "\r\n", $string );

    return $array;
  
  } // end function string_to_array()

  /**
   * Wrapper for implode()
   *
   * This makes it easier to change the delimiter in the future
   *
   * @param array $array Array to convert to a string
   * @return string The concatenated string
   */
  public function array_to_string( $array ) {
    $string = implode( "\r\n", (array)$array );

    return $string;
  
  } // end function array_to_string()

}

$GLOBALS['mdu-utilities'] = new Mass_Disable_Users_Utilities;
