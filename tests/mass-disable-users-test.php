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
    $this->utility->update_exceptions( 'exception1@example.com' );
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

  // Test finding blogs of which the user is a member
  public function testFindUserBlogs () {
  
    $user_id = $this->factory->user->create( array( 'role' => 'editor' ) );

    $blog_ids = $this->factory->blog->create_many( 4 );
    $blog_ids[0] = 1;

    foreach ( $blog_ids as $blog ) {
      add_user_to_blog( $blog, $user, 'editor' );
    }
    
    $this->utility->process_user_blogs( $user_id );

    foreach ( $blog_ids as $blog ) {
      $user = new WP_User( $user_id );

      if ( ! $user->has_cap( 'read' ) ) {
        $actual[$blog] = 'disabled';
      }

      $expected[$blog] = 'disabled';
    }


    $this->assertSame( $expected, $actual );

  }

  // Test finding a user 
  public function testUserDisable() {

    $users = array();
    foreach ( array( 'administrator', 'editor', 'author', 'contributor' ) as $role ) {
      $id = $this->factory->user->create( array( 'role' => $role ) );
      $users[$id] = $id;
    }

    foreach ( $users as $user ) {
      $this->utility->disable_user( $user );
    }

    foreach ( $users as $id ) {
      $user = new WP_User( $id );

      if ( ! $user->has_cap('read') ) {
        $users[$id] = 'disabled';
      }
      // Create the expected array based on the ID's of the created users
      $expected[$id] = 'disabled';
    }

    $actual = $users;

    $this->assertSame( $expected, $actual );


  }

  // Test getting data from CSV
  public function testCSVData () {
  
    $path = dirname(__FILE__);
    $file = $path . '/test.csv';
  
    $actual = $this->utility->parse_csv( $file );

    $expected = array(
      'test1@example.com',
      'test2@example.com',
      'test3@example.com',
      'exception1@example.com',
      'exception2@example.com'
    );

    $this->assertSame( $expected, $actual );
  }

  // Test comparing exceptions and CSV contents
  public function testExceptionCompare () {
  
    $this->utility->add_options();
    $exceptions = array(
      'exception1@example.com',
      'exception2@example.com'
    );
    update_option( 'mdu_email_exceptions', $exceptions );

    $path = dirname(__FILE__);
    $filename = $path . '/test.csv';
    $exceptions = get_option('mdu_email_exceptions');
    $actual = $this->utility->compare_users( $filename, $exceptions );

    $expected = array(
      'test1@example.com',
      'test2@example.com',
      'test3@example.com'
    );

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
