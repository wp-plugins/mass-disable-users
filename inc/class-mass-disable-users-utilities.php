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
  
  }

  /**
   * Updates the email exceptions list
   *
   * @param string $exceptions Delimited list of email addresses to exclude from disable list.
   */
  public function set_exceptions( $string ) {
  
    // Convert exceptions to array
    $exceptions = $this->string_to_array( $string );

    update_option( 'mdu_email_exceptions', $exceptions );

    $this->exceptions = $exceptions;
  
  }

  /**
   * Retrieves the array of exceptions
   *
   * @returns string $exceptions
   */
  public function get_exceptions() {
  
    $exceptions = get_option( 'mdu_email_exceptions' );
    $exceptions = $this->array_to_string( $exceptions );

    return $exceptions;
    
  }

  public function set_csv( $filename ) {
  
    $csv = $this->parse_csv( $filename );
    $this->csv = $csv;
  
  }

  public function get_csv() {
    
    return $this->csv;

  }

  /**
   * Parses the CSV into array
   *
   * @param string $filename Path to CSV file
   *
   * @returns array $users Users in the CSV
   */
  private function parse_csv( $filename ) {
  
    $file = fopen( $filename, 'r' );
    while( ! feof( $file ) ) {
      $user_array[] = fgetcsv( $file );
    }

    // Since fgetcsv returns a multi-dimensional array, we need to pull the email address out, making it an array value.
    foreach ( $user_array as $u ) {
      $users[] = strtolower($u[0]);
    }

    // fgetcsv also returns false when it hits the end of a file, messing up our array. Let's pop that sucker off.
    array_pop( $users );

    return $users;
  
  }

  /**
   * Combines users from the provided CSV and the exceptions
   *
   * @param array $csv Array of user email addresses in the CSV
   *
   * @returns array $combined Combined array of users who should exist
   */
  public function combine_users() {
  
    $exceptions = get_option( 'mdu_email_exceptions' );

    $csv = $this->get_csv();
    
    $combined = array_merge( $csv, $exceptions );

    return $combined;

  }

  /**
   * Pulls the email addresses of all existing users from the database
   *
   * @returns array $emails Array of all email addresses
   */
  public function set_users() {
  
    global $wpdb;

    $sort = "ID";

    $all_users = $wpdb->get_col( $wpdb->prepare(
      "SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC",
      $sort
    ));

    foreach( $all_users as $id ) {
      $user = get_userdata( $id );
      $emails[] = strtolower($user->user_email);
    }

    $this->users = $emails;
  
  }

  public function get_users() {
    
    return $this->users;

  }

  public function set_to_confirm() {
  
    $csv = $this->get_csv();
    $compared = $this->compare_users( $csv );
    $to_confirm = $this->remove_subscribers( $compared );

    $this->to_confirm = $to_confirm;
  
  }

  public function get_to_confirm() {
  
    return $this->to_confirm;
  
  }

  public function count_to_confirm() {
  
    $d = $this->get_to_confirm();

    $count = count( $d );

    return $count;
  
  }

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
  
  }

  public function set_to_disable( $emails ) {
  
    foreach( $emails as $e ) {
      if( email_exists( $e ) ) {
        $to_disable[] = email_exists( $e );
      }
    }

    $this->to_disable = $to_disable;
  
  }

  public function get_to_disable() {
  
    return $this->to_disable;
  
  }


  /**
   * Find and loop through a user's blogs
   * 
   * @param int $user The user's ID
   */
  public function process_user_blogs() {
  
    foreach( $this->get_to_disable() as $user ) {

      $user_blogs = get_blogs_of_user( $user );
      
      foreach ( $user_blogs as $blog ) {
        switch_to_blog( $blog->userblog_id );
        $this->disable_user( $user );
      }

    }
  
  }

  /**
   * Changes the given user to subscriber
   *
   * @param int $user
   */
  public function disable_user( $user ) {
  
      $user = new WP_User( $user );
      $user->set_role( 'subscriber' );
  
  }

  private function remove_subscribers( $users ) {
  
    foreach( $users as $u ) {
      if( email_exists( $u ) ) {
        $user['id'] = email_exists( $u );
        $user['email'] = $u;

        $all_users[] = $user;
      }
    }

    foreach( $all_users as $k => $u ) {
      $blogs = get_blogs_of_user( $u['id'] );


      // Search each user's blog. If they are only a subscriber, drop them.
      foreach( $blogs as $b ) {

        $blogid = $b->userblog_id;
        switch_to_blog( $blogid );
        $roles[] = $this->get_blog_role( $u['id'], $blogid );

      }

      // The $roles array is pretty messy. Clean it up!
      if( ! empty( $roles) ) {
        foreach( $roles as $role ) {
          $clean_roles[] = $role[0];
        }
      } else {
        unset( $all_users[$k] );
      }

      // Create testing array
      if( ! empty( $clean_roles ) ) {
        foreach( $clean_roles as $role ) {
          if( $role == 'subscriber' ){
            $test[] = TRUE;
          } else {
            $test[] = FALSE;
          }
        }
      }

      // Get rid of users with zero permissions
      if( $test === null ) {
        unset( $all_users[$k] );
      }

      // Unset users who are ONLY subscribers
      if( ! $test == null ){
        if( ! in_array( FALSE, $test ) ) {
          unset( $all_users[$k] );
        }
      }

      // Clean-up
      unset($test);
      unset( $clean_roles );
      unset( $roles );
    }
  
    // Construct the array for returning
    foreach( $all_users as $u ) {
      $emails[] = $u['email'];
    }
    return $emails;

  }

  private function get_blog_role( $user, $blogid ) {
    $theuser = new WP_User( $user, $blogid );

    if( ! empty( $theuser->roles) && is_array( $theuser->roles ) ) {
      foreach( $theuser->roles as $role ) {
        $userroles[] = $role;
      }
    }
    return $userroles;

  }

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
  
  }

  /**
   * Wrapper for implode()
   *
   * This makes it easier to change the delimiter in the future
   *
   * @param array $array Array to convert to a string
   * @return string The concatenated string
   */
  public function array_to_string( $array ) {
  
    $string = implode( "\r\n", $array );

    return $string;
  
  }

  
}

$GLOBALS['mdu-utilities'] = new Mass_Disable_Users_Utilities;
