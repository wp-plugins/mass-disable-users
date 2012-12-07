<?php

class Mass_Disable_Users_Utilities {

  protected $exceptions;
  protected $csv;
  protected $users;
  protected $to_disable;


  /**
   * Creates the following options:
   * - Exceptions
   */
  public function add_options() {
  
    add_option( 'mdu_email_exceptions', array( 'agrilifeweb@tamu.edu' ) );
  
  }

  /**
   * Updates the email exceptions list
   *
   * @param string $exceptions Delimited list of email addresses to exclude from disable list.
   */
  public function set_exceptions( $exceptions ) {
  
    // Convert exceptions to array
    $exceptions = $this->string_to_array( $exceptions );

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
    $file = fread( $file, filesize($filename) );
    $users = str_getcsv( $file );

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
   * Gets the email addresses of all existing users
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
      $emails[] = $user->user_email;
    }

    $this->users = $emails;
  
  }

  public function get_users() {
    
    return $this->users;

  }

  public function set_to_disable() {
  
    $csv = $this->get_csv();
    $to_disable = $this->compare_users( $csv );

    $this->to_disable = $to_disable;
  
  }

  public function get_to_disable() {
  
    return $this->to_disable;
  
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

  /**
   * Find and loop through a user's blogs
   * 
   * @param int $user The user's ID
   */
  public function process_user_blogs( $user ) {
  
    $user_blogs = get_blogs_of_user( $user );
    foreach ( $user_blogs as $blog ) {
      switch_to_blog( $blog->userblog_id );
      $this->disable_user( $user );
    }
  
  }

  /**
   * Changes the given user to subscriber
   *
   * @param int $user
   */
  public function disable_user( $user ) {
  
      $user = new WP_User( $user );
      $user->remove_all_caps();
  
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
  
    $array = explode( '\n', $string );

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
  
    $string = implode( '\n', $array );

    return $string;
  
  }

  
}

$GLOBALS['mdu-utilities'] = new Mass_Disable_Users_Utilities;
