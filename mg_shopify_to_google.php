<?php
/**
* Plugin Name: Shopify to Google Feed
* Plugin URI: http://momentgear.com/
* Description: Converts Shopify product list to Google Shopping feed
* Author: Jake Aldrich
* Version: 1.0
*/

/*
 * This function encapsulates creating the admin page
 * for this plugin
 */
function mg_shopify_to_google() {
	// This function creates a link for our admin page
	// in the Tools menu on the WordPress dashboard
	add_management_page( 'Shopify to Google Feed', 'Shopify to Google Feed', 'export', 'shopify-to-google-feed', 'mg_shopify_to_google_admin_form');
}

// The proper time to add Admin Menu Pages, is when
// the 'admin_menu' action takes place
add_action( 'admin_menu', 'mg_shopify_to_google' );

function mg_shopify_to_google_admin_form() {
	// This line gets the admin page from another file
	// in this directory
	require( dirname( __FILE__ ) . '/mg_shopify_to_google_admin_form.php' );
}
?>