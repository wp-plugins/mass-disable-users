<?php

class Mass_Disable_Users_Utilities {

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
  public function update_exceptions( $exceptions ) {
  
    // Convert exceptions to array
    $exceptions = $this->string_to_array( $exceptions );

    update_option( 'mdu_email_exceptions', $exceptions );
  
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

  /**
   * Parses the CSV into array
   *
   * @param string $filename Path to CSV file
   *
   * @returns array $users Users in the CSV
   */
  public function parse_csv( $filename ) {
  
    $file = fopen( $filename, 'r' );
    $file = fread( $file, filesize($filename) );
    $users = str_getcsv( $file );

    return $users;
  
  }

  /**
   * Compares exceptions with provided CSV of users to disable
   *
   * @param string $filename
   * @param array $exceptions
   * @returns array $users Users to disable
   */
  public function compare_users( $filename, $exceptions ) {
  
    $csv = $this->parse_csv( $filename );

    $users = array_diff( $csv, $exceptions);

    return $users;
  
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
