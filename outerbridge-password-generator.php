<?php
/* 
Plugin Name: Password Generator 
Plugin URI: https://outerbridge.co.uk/ 
Description: Password Generator is a plugin written by Outerbridge which adds a widget to WordPress which generates various length random passwords (with or without special characters).
Author: Outerbridge
Version: 1.7
Author URI: https://outerbridge.co.uk/
Tags: password generator, special characters, strong password
Text Domain: password-generator
License: GPL v2
*/

/**
 *
 * v1.7 220901 Various fixes. Tested and stable up to WP6.0
 *
 * v1.6 170523 Made translatable
 * v1.5 170503 Updated to remove old style constructor and general tidy up
 * v1.4 150818 Updated WP_Widget functionality for WP4.3
 * v1.3 140829 Tested and stable up to WP4.0
 * v1.2 140430 Tested and stable up to WP3.9
 * v1.1 131212 Tested and stable up to WP3.8 and updated author name
 * v1.0 120103 stable up to WP3.3
 * v0.1 110827 initial release
 *
 */

class obr_password_generator extends WP_Widget {
	// version
	public $obr_password_generator = '1.7';
	
	//contructor
	function __construct() {
		parent::__construct( 'obr_pass_gen', __( 'Outerbridge Password Generator', 'password-generator' ), array( 'classname' => 'oouterbridge_pass_gen_widget', 'description' => __( 'Create strong passwords quickly and easily using this widget.  Various password lengths available as well as the option to use symbols as well as alphanumerics.', 'password-generator' ) ) );
		register_activation_hook( __FILE__, array( $this, 'obr_install' ) );		
		register_deactivation_hook( __FILE__, array( $this, 'obr_uninstall' ) );		
		add_action( 'wp_loaded', array( $this, 'obr_plugin_update_check' ) );
		add_action( 'wp_head', array( $this, 'obr_header' ) );
	}
	
	// functions
	function widget( $args, $instance ) {
		extract( $args );
		$title = '';
		if ( isset( $instance[ 'title' ] ) ) {
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		}
		echo $before_widget;
		if ( strlen( $title ) > 0 ) {
			echo $before_title, $title, $after_title;
		} else {
			echo $before_title, __( 'Password Generator', 'password-generator' ), $after_title;
		}
		if ( isset( $_POST[ 'pg_length' ] ) ) {
			$formposted = true;
			$pg_length = strip_tags( stripslashes( $_POST[ 'pg_length' ] ) );
			if ( $pg_length > 20 || $pg_length < 8 ) {
				$pg_length = 14;
			}
			$chk_symbols = '';
			if ( isset( $_POST[ 'chk_symbols' ] ) ) {
				$chk_symbols = strip_tags( stripslashes( $_POST[ 'chk_symbols' ] ) );
			}
		} else {
			$formposted = false;
			$pg_length = 14;
			$chk_symbols = true;
		}
		echo '<form action="';
		$path = $_SERVER[ 'REQUEST_URI' ];
		if ( strlen( $path ) ) {
			echo $path;
		} else {
			echo './';
		}
		echo '" method="POST">';
		echo '<ul><li>';
		_e( 'Password length?', 'password-generator' );
		echo '<select name="pg_length" title="';
		_e( 'Password length?', 'password-generator' );
		echo '"><optgroup label="';
		_e( 'Recommended Lengths', 'password-generator' );
		echo '">';
		for ( $i = 14; $i <= 20; $i++ ) {
			echo '<option';
			if ( $i == $pg_length ) {
				echo ' selected="selected"';
			}
			echo ' value="', $i, '">', $i, '</option>';
		}
		echo '</optgroup>';
		echo '<optgroup label="';
		_e( 'Shorter Lengths', 'password-generator' );
		echo '">';
		for ( $i = 8; $i <= 13; $i++ ) {
			echo '<option';
			if ( $i == $pg_length ) {
				echo ' selected="selected"';
			}
			echo ' value="', $i, '">', $i, '</option>';
		}	
		echo '</optgroup></select></li>';
		echo '<li><label for="chk_symbols">';
		_e( 'Include symbols?', 'password-generator' );
		echo '</label><input name="chk_symbols"';
		if ( $chk_symbols ) {
			echo ' checked="checked"';
		}
		echo 'type="checkbox" title="';
		_e( 'Include symbols?', 'password-generator' );
		echo '" /></li></ul>';
		echo '<input type="submit" value="';
		_e( 'Generate password', 'password-generator' );
		echo '" name="submit" /><br />';
		if ( $formposted ) {
			echo '<p>';
			printf( __( 'New Password: %s', 'password-generator' ), '<strong>' . $this->obr_generate_password( $pg_length, $chk_symbols ) . '</strong>' );
			echo '</p>';
		}
		echo '</form>';
		echo $after_widget;
	}

	function obr_generate_password( $length, $symbols ) {
		$random_chars = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ( $symbols ) {
			$random_chars .= "+-=_*!@#$%+-=_*!@#$%+-=_*!@#$%+-=_*!@#$%";
		}
		$getstring = "";
		$password = "";
		while( strlen( $password ) < $length ) {
			$addstring = substr( $random_chars, mt_rand( 0, strlen( $random_chars ) - 1 ), 1 );
			// Avoid duplicates
			if ( strlen( $getstring ) > 0 ) {
				if ( !strstr( $password, $getstring ) ) {
					//append to the password
					$password .= $addstring;
				}
			} else {
				$password .= $addstring;
			}
		}
		return $password;
	}

	function obr_header(){
		echo "\n<!-- ";
		printf( __( 'Using Outerbridge Password Generator.  Find out more at %s', 'password-generator' ), 'https://outerbridge.co.uk/' );
		echo "-->\n";
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( stripslashes( $new_instance[ 'title' ] ) );
		return $instance;
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title'=>'Password Generator' ) );
		$title = htmlspecialchars( $instance[ 'title' ] );
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name( 'title' ) . '">' . __( 'Title:', 'password-generator' ) . ' <input style="width: 250px;" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $title . '" /></label></p>';
	}

	function obr_install() {
		add_option( 'obr_password_generator', $this->obr_password_generator );
		$this->obr_plugin_update_check();
	}

	function obr_uninstall() {
		delete_option( 'obr_password_generator' );
	}

	function obr_plugin_update_check() {
		$installed_ver = get_option( 'obr_password_generator' );
		if( $installed_ver != $this->obr_password_generator ) {
			$charset_collate = '';
			if ( !empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( !empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			$mysql = "";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $mysql );

			echo '<div id="message" class="updated fade"><p><strong>';
			printf( __( 'Outerbridge Password Generator updated to version %s', 'password-generator' ), $this->obr_password_generator );
			echo '</strong></p></div>';
			update_option( 'obr_password_generator', $this->obr_password_generator );
		}
	}
}

function obr_password_generator_init() {
	register_widget( 'obr_password_generator' );
}
add_action( 'widgets_init', 'obr_password_generator_init' );

