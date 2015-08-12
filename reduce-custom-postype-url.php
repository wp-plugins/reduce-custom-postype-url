<?php
/*
Plugin Name: Reduce Custom Post Type Slug
Plugin URI: https://github.com/namnguyen2091/remduce-custom-postype-slug
Description: Creates functionality to remove custom post type slug from url. (e.g. `/product/my-banana/` to `/my-banana/`)
Version: 1.0
Author: Nam Nguyen
Author URI: about.me/namnguyen2091
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

if ( ! class_exists('Reduce_Custom_Postype_URL') ) {
	class Reduce_Custom_Postype_URL {

		private $options;

	    public static function Instance() {
	        static $inst = null;
	        if ($inst === null) {
	            $inst = new Reduce_Custom_Postype_URL();
	        }
	        return $inst;
	    }
	    
	    function __construct() {
	        add_filter( 'post_type_link', array(&$this, 'rcps_remove_cpt_slug'), 10, 3 );
	        add_action( 'pre_get_posts', array(&$this, 'rcps_parse_request_tricksy') );
	        add_action( 'admin_menu', array(&$this, 'rcps_add_admin_menu') );
			add_action("admin_init", array($this, "rcps_display_theme_panel_fields"));
	    }

	    // Register the management page
	    function rcps_add_admin_menu() {
	        add_options_page(
	            __('Settings Admin', 'rcps'), 
	            __('Reduce Custom Postype Slug', 'rcps'),
	            'manage_options', 
	            'reduce_custom_postype_slug', 
	            array( $this, 'create_admin_page' )
	        );
	    }

	    public function create_admin_page() { ?>
	        <div class="wrap">
	            <h2><?php _e('Reduce Custom Postype Slug Settings', 'rcps'); ?></h2>       
	            <form method="post" action="options.php">
	            <?php
	                // This prints out all hidden setting fields
	                settings_fields( 'rcps_field' );   
	                do_settings_sections( 'reduce_custom_postype_slug' );
	                submit_button(); 
	            ?>
	            </form>
	        </div>
	        <?php
	    }

	    function rcps_remove_cpt_slug( $post_link, $post, $leavename ) {

	        $slugs = get_option('rcps_slugs');
	     
	        if ( ! in_array( $post->post_type, explode(',', $slugs)) || 'publish' != $post->post_status )
	            return $post_link;
	     
	        $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
	     
	        return $post_link;
	    }

	    function rcps_parse_request_tricksy( $query ) {
	 
	        // Only noop the main query
	        if ( ! $query->is_main_query() )
	            return;
	     
	        // Only noop our very specific rewrite rule match
	        if ( 2 != count( $query->query )
	            || ! isset( $query->query['page'] ) )
	            return;
	     
	        // 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
	        if ( ! empty( $query->query['name'] ) ) {
	            $slugs = get_option('rcps_slugs');
	            $post_types = array('post');

	            $slugs = explode(',', $slugs);
	            if (!empty($slugs)) {
	                foreach ($slugs as $val) {
	                    array_push($post_types, $val);
	                }
	            }
	            array_push($post_types, 'page');

	            $query->set( 'post_type', $post_types );
	            //$query->set( 'post_type', array( 'post', 'product', 'loi-khach-hang', 'page' ) );
	        }
	    }

		function rcps_display_theme_panel_fields() {

		    add_settings_section("rcps_field", __("All Settings", "rcps"), null, "reduce_custom_postype_slug");
		    
		    // register all fields 
		    add_settings_field(
		    	"rcps_slugs",
		    	__("Slugs to remove", "rcps"),
		    	array($this, "rcps_display_element"), // call back function
		    	"reduce_custom_postype_slug",
		    	"rcps_field"
		    );
		    register_setting("rcps_field", "rcps_slugs");
		}

		function rcps_display_element() { ?>
	        <input type="text" name="rcps_slugs" id="rcps_slugs" value="<?php echo get_option('rcps_slugs'); ?>" style="width: 50%;" placeholder="product,shop" />
	        <span>ex: product,shop ...</span>
	    	<?php
		}

	}

	new Reduce_Custom_Postype_URL();
}