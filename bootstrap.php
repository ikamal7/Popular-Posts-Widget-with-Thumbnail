<?php 

    function ppw_plugin_loaded(){
        load_plugin_textdomain( 'popular-post-widget', false, dirname( __FILE__) . '/languages');
    }

if (! function_exists('ppw_bootstrap')) {
	
	function ppw_bootstrap(){
		wp_enqueue_style( 'ppw-style', plugins_url( '/assets/ppw.css', __FILE__ ), 'all' );

	}
	add_action( 'wp_enqueue_scripts', 'ppw_bootstrap' );
}