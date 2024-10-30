<?php
/**
 * Used to exclude write-only review child pages from being visible on the site.
 * 
 * Majority based on the Exclude Pages plugin by Simon Wheatley (http://profiles.wordpress.org/users/simonwheatley/profile/public/)
 * @see http://wordpress.org/extend/plugins/exclude-pages/
 * 
 * function names changed to avoid collisions
 */

define('LDV_EXCLUDE_OPTION_NAME', 'ep_exclude_pages');
define('LDV_EXCLUDE_OPTION_SEP', ',');

// Take the pages array, and return the pages array without the excluded pages
// Doesn't do this when in the admin area
function ldv_exclude_pages( $pages ) {
	// If the URL includes "wp-admin", just return the unaltered list
	// This constant, WP_ADMIN, only came into WP on 2007-12-19 17:56:16 rev 6412, i.e. not something we can rely upon unfortunately.
	// May as well check it though.
	// Also check the URL... let's hope they haven't got a page called wp-admin (probably not)
	// SWTODO: Actually, you can create a page with an address of wp-admin (which is then inaccessible), I consider this a bug in WordPress (which I may file a report for, and patch, another time).
        
        $bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
	$bail_out = apply_filters( 'ldv_admin_bail_out', $bail_out );
	if ( $bail_out ) return $pages;
	$excluded_ids = ldv_get_excluded_ids();
        
	$length = count($pages);
	// Ensure we catch all descendant pages, so that if a parent
	// is hidden, it's children are too.
	for ( $i=0; $i<$length; $i++ ) {
		$page = & $pages[$i];
		// If one of the ancestor pages is excluded, add it to our exclude array
		if ( ldv_ancestor_excluded( $page, $excluded_ids, $pages ) ) {
			// Can't actually delete the pages at the moment, 
			// it'll screw with our recursive search.
			// For the moment, just tag the ID onto our excluded IDs
			$excluded_ids[] = $page->ID;
		}
	}

	// Ensure the array only has unique values
	$delete_ids = array_unique( $excluded_ids );
	
	// Loop though the $pages array and actually unset/delete stuff
	for ( $i=0; $i<$length; $i++ ) {
		$page = & $pages[$i];
		// If one of the ancestor pages is excluded, add it to our exclude array
		if ( in_array( $page->ID, $delete_ids ) ) {
			// Finally, delete something(s)
			unset( $pages[$i] );
		}
	}

	// Reindex the array, for neatness
	// SWFIXME: Is reindexing the array going to create a memory optimisation problem for large arrays of WP post/page objects?
	if ( ! is_array( $pages ) ) $pages = (array) $pages;
	$pages = array_values( $pages );

	return $pages;
}


/**
 * Recurse down an ancestor chain, checking if one is excluded
 *
 * @param  
 * @return boolean|int The ID of the "nearest" excluded ancestor, otherwise false
 * @author Simon Wheatley
 **/
function ldv_ancestor_excluded( $page, $excluded_ids, $pages ) {
	$parent = & ldv_get_page( $page->post_parent, $pages );
	// Is there a parent?
	if ( ! $parent )
		return false;
	// Is it excluded?
	if ( in_array( $parent->ID, $excluded_ids ) )
		return (int) $parent->ID;
	// Is it the homepage?
	if ( $parent->ID == 0 )
		return false;
	// Otherwise we have another ancestor to check
	return ldv_ancestor_excluded( $parent, $excluded_ids, $pages );
}

/**
 * {no description}
 *
 * @param int $page_id The ID of the WP page to search for
 * @param array $pages An array of WP page objects
 * @return boolean|object the page from the $pages array which corresponds to the $page_id
 * @author Simon Wheatley
 **/
function ldv_get_page( $page_id, $pages ) {
	// PHP 5 would be much nicer here, we could use foreach by reference, ah well.
	$length = count($pages);
	for ( $i=0; $i<$length; $i++ ) {
		$page = & $pages[$i];
		if ( $page->ID == $page_id ) return $page;
	}
	// Unusual.
	return false;
}

// Is this page we're editing (defined by global $post_ID var) 
// currently NOT excluded (i.e. included),
// returns true if NOT excluded (i.e. included)
// returns false is it IS excluded.
// (Tricky this upside down flag business.)
function ldv_this_page_included() {
	global $post_ID;
	// New post? Must be included then.
	if ( ! $post_ID ) return true;
	$excluded_ids = ldv_get_excluded_ids();
	// If there's no exclusion array, we can return true
	if ( empty($excluded_ids) ) return true;
	// Check if our page is in the exclusion array
	// The bang (!) reverses the polarity [1] of the boolean
	return ! in_array( $post_ID, $excluded_ids );
	// fn1. (of the neutron flow, ahem)
}

// Check the ancestors for the page we're editing (defined by 
// global $post_ID var), return the ID if the nearest one which
// is excluded (if any);
function ldv_nearest_excluded_ancestor() {
	global $post_ID, $wpdb;
	// New post? No problem.
	if ( ! $post_ID ) return false;
	$excluded_ids = ldv_get_excluded_ids();
	// Manually get all the pages, to avoid our own filter.
	$sql = "SELECT ID, post_parent FROM $wpdb->posts WHERE post_type = 'page'";
	$pages = $wpdb->get_results( $sql );
	// Start recursively checking the ancestors
	$parent = ldv_get_page( $post_ID, $pages );
	return ldv_ancestor_excluded( $parent, $excluded_ids, $pages );
}

function ldv_get_excluded_ids() {
	$exclude_ids_str = get_option( LDV_EXCLUDE_OPTION_NAME );
	// No excluded IDs? Return an empty array
	if ( empty($exclude_ids_str) ) return array();
	// Otherwise, explode the separated string into an array, and return that
	return explode( LDV_EXCLUDE_OPTION_SEP, $exclude_ids_str );
}

// This function gets all the exclusions out of the options
// table, updates them, and resaves them in the options table.
// We're avoiding making this a postmeta (custom field) because we
// don't want to have to retrieve meta for every page in order to
// determine if it's to be excluded. Storing all the exclusions in
// one row seems more sensible.
function ldv_update_exclusions( $post_ID ) {
	// Bang (!) to reverse the polarity of the boolean, turning include into exclude
	// $exclude_this_page = ! (bool) @ $_POST['ldv_this_page_included'];
        
	// SWTODO: Also check for a hidden var, which confirms that this checkbox was present
	// If hidden var not present, then default to including the page in the nav (i.e. bomb out here rather
	// than add the page ID to the list of IDs to exclude)
	// $ctrl_present = (bool) @ $_POST['ldv_ctrl_present'];
	// if ( ! $ctrl_present )
	// 	return;
	
	$excluded_ids = ldv_get_excluded_ids();
	// If we need to EXCLUDE the page from the navigation...
	// if ( $exclude_this_page ) {
		// Add the post ID to the array of excluded IDs
		array_push( $excluded_ids, $post_ID );
		// De-dupe the array, in case it was there already
		$excluded_ids = array_unique( $excluded_ids );
	// }
        
	// If we need to INCLUDE the page in the navigation...
        /*
	if ( ! $exclude_this_page ) {
		// Find the post ID in the array of excluded IDs
		$index = array_search( $post_ID, $excluded_ids );
		// Delete any index found
		if ( $index !== false ) unset( $excluded_ids[$index] );
	}*/
	$excluded_ids_str = implode( LDV_EXCLUDE_OPTION_SEP, $excluded_ids );
	ldv_set_option( LDV_EXCLUDE_OPTION_NAME, $excluded_ids_str );
}

// Take an option, delete it if it exists, then add it.
function ldv_set_option( $name, $value ) {
	// Delete option	
	delete_option($name);
	// Insert option
	add_option( $name, $value );
}