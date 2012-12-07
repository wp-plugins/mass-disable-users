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
