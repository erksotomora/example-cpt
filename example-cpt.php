<?php
/*
Plugin Name: Example CPT
Description: Post Engineer Candidate Coding Exercise
Version: 1.0.0
Author: Erick Soto
Author URI: https://erksoto.com/
Text Domain: excpt
*/



if( !defined( 'EXCPT_VER' ) )
	define( 'EXCPT_VER', '1.0.0' );

// Start up the engine
class WP_Example_CPT
{

	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
        add_action      ('init',            array( $this, 'register_example_cpt'    )); // register cpt
        add_action      ('init',            array( $this, 'register_example_meta'   )); // register meta field
		add_action		( 'do_meta_boxes',  array( $this, 'create_metaboxes'		),	10); // add meta box for text input to update meta data value
		add_action		( 'save_post',      array( $this, 'save_custom_meta'		),	1); // save custom meta data callback
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return WP_Example_CPT
	 */
	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}


	/**
	 * Registers the Example CPT
	 *
	 * @return void
	 */
    public function register_example_cpt(){
        
        $supports = array(
            'title', // post title
            'custom-fields', // needed to support custom meta field
        );
        $labels = array(
            'name' => _x('Example CPT', 'plural'),
            'singular_name' => _x('Example CPT', 'singular'),
            'menu_name' => _x('Example CTP', 'admin menu'),
            'name_admin_bar' => _x('Example CTP', 'admin bar'),
            'add_new' => _x('Add New', 'add new'),
            'add_new_item' => __('Add New Example CPT', 'excpt'),
            'new_item' => __('New Example CPT', 'excpt'),
            'edit_item' => __('Edit Example CPT', 'excpt'),
            'view_item' => __('View Example CPT', 'excpt'),
            'all_items' => __('All Example CPT', 'excpt'),
            'search_items' => __('Search Example CPT', 'excpt'),
            'not_found' => __('No Example CPT found.', 'excpt'),
        );
        $args = array(
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'example_cpt'),
            'has_archive' => true,
            'hierarchical' => false,
            'register_meta_box_cb' => 'create_metaboxes',
        );
        register_post_type('example_cpt', $args);

    }


	/**
	 * Registers the Example Meta
	 *
	 * @return void
	 */
    public function register_example_meta(){
        
        $object_type = 'example_cpt'; // The object type. 

        $args = array(
            'type'		=> 'string', // Validate and sanitize the meta value as a string.
            'description'    => 'Example Meta Field assosiated to the Example CPT', // Shown in the schema for the meta key.
            'single'        => true, // Return a single value of the type. Default: false.
            'show_in_rest'    => true, // Show in the WP REST API response. Default: false.
        );

        register_post_meta( $object_type, 'example-meta', $args );

    }

	/**
	 * call metabox
	 *
	 * @return void
	 */
	public function create_metaboxes( ) {

		add_meta_box( 'wp-excpt', __( 'Example Meta', 'excpt' ), array( $this, 'excpt_meta' ), 'example_cpt', 'advanced', 'high' );

	}

	/**
	 * display meta fields for example meta
	 *
	 * @return void
	 */
	public function excpt_meta( $post ) {

		// Use nonce for verification
		wp_nonce_field( 'excpt_meta_nonce', 'excpt_meta_nonce' );

		$post_id	= $post->ID;

		// get postmeta, and our initial settings
        $example_meta = get_post_meta( $post_id, 'example-meta', true );

        echo '<table class="form-table excpt-notes-table">';

        echo '<tr class="excpt-notes-data excpt-notes-after-text">';
            echo '<th>'.__( 'Meta Value:', 'excpt' ) .'</th>';
            echo '<td>';
                echo '<input type="text" class="widefat" name="example-meta" id="example-meta" value="'. esc_attr( $example_meta ) . '"></input>';
            echo '</td>';
        echo '</tr>';

         echo '</table>';

	}

	/**
	 * save post metadata
	 *
	 * @return void
	 */
	public function save_custom_meta( $post_id ) {

		// make sure we aren't using autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// do our nonce check. ALWAYS A NONCE CHECK
		if ( ! isset( $_POST['excpt_meta_nonce'] ) || ! wp_verify_nonce( $_POST['excpt_meta_nonce'], 'excpt_meta_nonce' ) )
			return $post_id;

        $post_type = get_post_type( $post_id );

		// and make sure the user has the ability to do this
		if ( strcmp($post_type, 'example_cpt') == 0 ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {

				return $post_id;

			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {

				return $post_id;
                
			}

		} else {

            return $post_id;

		}

		// all clear. get data via $_POST and store it
		$example_meta	= ! empty( $_POST['example-meta'] ) ? $_POST['example-meta'] : false;

		// update side meta data
		if ( $example_meta ) {

			update_post_meta( $post_id, 'example-meta', esc_attr( $example_meta ) );

		} else {

			delete_post_meta( $post_id, 'example-meta' );

		}

	}

/// end class
}

// Instantiate our class
$WP_Comment_Notes = WP_Example_CPT::getInstance();