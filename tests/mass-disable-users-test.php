<?php

/**
 * Tests all functions of the Mass Disable Users plugin
 */

require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/mass-disable-users.php' );
require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/inc/class-mass-disable-users-admin.php' );
require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/inc/class-mass-disable-users-utilities.php' );

class Tests_Mass_Disable_Users extends WP_UnitTestCase {

  private $plugin;
  private $utility;

  public function setUp() {

    parent::setUp();
    $this->plugin = $GLOBALS['mass-disable-users'];
    $this->admin = $GLOBALS['mdu-admin'];
    $this->utility = $GLOBALS['mdu-utilities'];

  }

  // Sanity check
  public function testTrueStillEqualsTrue() {

    $this->assertTrue( true );

  }

  // Test plugin initialization
  public function testPluginInitialization() {
    
    $this->assertFalse( null == $this->plugin );

  }

  // Ensure that the utilties class is loading
  public function testUtilityClassExists() {

    $this->assertTrue( class_exists( 'Mass_Disable_Users_Utilities' ) );

  }
  
  // Ensure that the admin class is loading
  public function testAdminClassExists() {

    $this->assertTrue( class_exists( 'Mass_Disable_Users_Admin' ) );

  }

  // Check if the mdu_email_exceptions option exists
  public function testEmailExceptionsOption() {
    
    $this->utility->add_options();
    $return = get_option( 'mdu_email_exceptions' );
    $this->assertTrue( false != $return );

  }

  // Test updating mdu_email_exceptions
  public function testEmailExceptionsUpdate() {
    
    $this->utility->add_options();
    $old = get_option( 'mdu_email_exceptions' );
    $this->utility->update_exceptions( 'test@test.com' );
    $new = get_option( 'mdu_email_exceptions' );
    $this->assertFalse( $old == $new );

  }

  // Test retrieving mdu_email_exceptions
  public function testEmailExceptionsRetrieve() {
    
    $exceptions = $this->teststring();
    $this->utility->update_exceptions( $exceptions );
    $return = $this->utility->get_exceptions();

    $this->assertSame( $exceptions, $return );

  }

  /**
   * Test if strings are converted to arrays
   *
   * @dataProvider teststring
   */
  public function testStringToArray( $string ) {

    $actual = $this->utility->string_to_array( $string );
    $expected = $this->testarray();

    $this->assertSame( $expected, $actual );
  
  }

  public function teststring() {
  
    return 'test@test.com\naaron@test.com\ntravis@test.com';
  
  }

  public function testarray() {
  
    return array(
      'test@test.com',
      'aaron@test.com',
      'travis@test.com'
    );
  }
} 
