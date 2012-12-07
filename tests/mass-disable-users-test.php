<?php

/**
 * Tests all functions of the Mass Disable Users plugin
 */

require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/mass-disable-users.php' );
require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/inc/class-mass-disable-users-admin.php' );
require_once( ABSPATH . '/wp-content/plugins/mass-disable-users/inc/class-mass-disable-users-utilities.php' );

class Tests_Mass_Disable_Users extends WP_UnitTestCase {

  private $plugin;
  private $admin;
  private $utility;

  public function setUp() {

    parent::setUp();
    $this->plugin = new Mass_Disable_Users;
    $this->admin = new Mass_Disable_Users_Admin;
    $this->utility = new Mass_Disable_Users_Utilities;

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
    $this->utility->set_exceptions( 'exception1@example.com\ntest@test.com' );
    $new = get_option( 'mdu_email_exceptions' );
    $this->assertFalse( $old == $new );

  }

  // Test retrieving mdu_email_exceptions
  public function testEmailExceptionsRetrieve() {
    
    $exceptions = $this->teststring();
    $this->utility->add_options();
    $this->utility->set_exceptions( $exceptions );
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
    
    $this->utility->set_users();
    $to_disable = $this->utility->get_users();
    $this->utility->set_to_disable( $to_disable );
    $this->utility->process_user_blogs();

    foreach ( $blog_ids as $blog ) {
      $user = new WP_User( $user_id );

      if ( ! $user->get( 'role' ) == 'subscriber' ) {
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

      if ( ! $user->has_cap('edit_posts') ) {
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
  
    $this->utility->set_csv( $file );

    $actual = $this->utility->get_csv();

    $expected = array(
      'user_1@example.org',
      'user_2@example.org',
      'user_3@example.org',
      'exception1@example.org',
      'exception2@example.org'
    );

    $this->assertSame( $expected, $actual );
  }

  // Test combining CSV & exceptions into one array
  public function testUserCombine() {
  
    $this->utility->add_options();

    $path = dirname(__FILE__);
    $file = $path . '/test.csv';

    $this->utility->set_csv($file);

    $actual = $this->utility->combine_users();

    $expected = array(
      'user_1@example.org',
      'user_2@example.org',
      'user_3@example.org',
      'exception1@example.org',
      'exception2@example.org',
      'agrilifeweb@tamu.edu',
      'test@test.com'
    );


    $this->assertSame( $actual, $expected );
  
  }

  // Test retrieving list of existing users by email
  public function testSetUsers() {
  
    $user_ids = $this->factory->user->create_many( 9 );
    
    foreach( $user_ids as $id ) {
      $user = get_userdata( $id );
      $expected[] = $user->user_email;
    }

    $admin = 'admin@example.org';
    array_unshift( $expected, $admin);

    $this->utility->set_users();
    $actual = $this->utility->get_users();

    $this->assertSame( $actual, $expected );
  
  }

  // Compare the CSV+exceptions array with users array
  public function testCompareUsers() {
  
    $user_ids = $this->factory->user->create_many(5);

    $this->utility->set_users();

    $this->utility->add_options();

    $path = dirname(__FILE__);
    $file = $path . '/test.csv';

    $this->utility->set_csv( $file );

    $this->utility->set_to_confirm();

    $actual = $this->utility->get_to_confirm();

    $actual = array_values( $actual );

    $expected = array(
      'admin@example.org',
    );

    $this->assertSame( $actual, $expected );
  
  }

  public function testCountToConfirm() {
  
    $user_ids = $this->factory->user->create_many(5);

    $this->utility->set_users();

    $this->utility->add_options();

    $path = dirname(__FILE__);
    $file = $path . '/test.csv';

    $this->utility->set_csv( $file );

    $this->utility->set_to_confirm();

    $actual = $this->utility->count_to_confirm();

    $expected = 1;

    $this->assertSame( $actual, $expected );
  
  }

  public function testNoSubscribers() {
  
    $user_ids = $this->factory->user->create_many(5);

    $this->utility->set_users();

    $this->utility->add_options();

    $path = dirname(__FILE__);
    $file = $path . '/test.csv';

    $this->utility->set_csv( $file );
    $this->utility->set_users();
    $this->utility->set_to_confirm();

    $to_confirm = $this->utility->get_to_confirm();
    foreach( $to_confirm as $c ) {
      $ids[] = email_exists( $c );
    }

    foreach( $ids as $id ) {
      $blogs = get_blogs_of_user( $id );

      foreach( $blogs as $b ) {
        switch_to_blog( $b->userblog_id );
        $theuser = new WP_User( $id, $b->userblog_id );
        if( ! empty( $theuser->roles) && is_array( $theuser->roles ) ) {
          foreach( $theuser->roles as $role ) {
            $userroles[] = $role;
          }
        }
      }
    }

    $this->assertNotContains( 'subscriber', $userroles );
  
  }
  public function teststring() {
  
    return "test@test.com\r\naaron@test.com\r\ntravis@test.com";
  
  }

  public function testarray() {
  
    return array(
      'test@test.com',
      'aaron@test.com',
      'travis@test.com'
    );
  }

} 
