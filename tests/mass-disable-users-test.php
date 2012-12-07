<?php

/**
 * Tests all functions of the Mass Disable Users plugin
 */

require_once( ABSPATH . 'wp-content/plugins/mass-disable-users/mass-disable-users.php' );

class Tests_Mass_Disable_Users extends WP_UnitTestCase {

  private $plugin;
  private $utility;

  public function setUp() {

    parent::setUp();
    $this->plugin = $GLOBALS['mass-disable-users'];

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
} 
