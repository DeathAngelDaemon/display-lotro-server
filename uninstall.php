<?php

/* security request */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

global $wpdb;

// For Single site
if ( !is_multisite() ) 
{
        // delete the options
        delete_option( 'lotroserver_options' );
        // clean up the database
        $wpdb->query("OPTIMIZE TABLE `" .$wpdb->options. "`");
} 
// For Multisite
else 
{
    // For regular options.
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) 
    {
        switch_to_blog( $blog_id );
        delete_option( 'lotroserver_options' );  
    }
    switch_to_blog( $original_blog_id );
}

?>
