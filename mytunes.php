<?php if(!defined('ABSPATH')) { die(); }
/**
 * Plugin Name: MyTunes
 * Description: Keep track of the tunes you play
 * Author: Pierce Gresham
 * Author URI:
 * Version 0.1.0
 * Text Domain: mytunes
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
if( !class_exists('MyTunes') ) {
    class MyTunes {

        private static $version = '0.1.0';
        private static $_this;
        private $settings;
        private $debugPath;

        // Instance Method
        public static function Instance() {
            static $instance = null;
            if( $instance == null ) {
                $instance = new self();
            }
            return $instance;
        }

        // Constructor
        private function __construct()
        {
            add_action('init', array($this, 'myt_register_post_type') );

            register_activation_hook(__FILE__, array($this, 'register_activation' ) );
            register_activation_hook(__FILE__, array($this, 'register_deactivation' ) );
            register_deactivation_hook( __FILE__, array($this, 'flush_rewrites'));
            add_action( 'wp_enqueue_scripts', array($this, 'add_scripts' ));

            add_filter( 'single_template' , array($this, 'get_mytunes_single_template'));
            add_filter( 'page_template' , array($this, 'get_mytunes_page_template'));

//            add_action('wp_enqueue_scripts', array( $this, 'add_scripts' ));
            // Init Setttings
            $this->initialize_settings();

            if( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX) ) {
                add_action('admin_enqueue_scripts', array( $this, 'add_admin_scripts' ));
                add_action('admin_init', array( $this, 'admin_init' ));
                add_action('add_meta_boxes', array( $this, 'add_meta_boxes') );
                add_action('save_post', array( $this, 'save_post_meta') );
            } else {
                // Could add Shortcodes for frontEnd //
                // add_action('wp_head', array($this, 'headFunctions') );
            }
        }



        // front end includes
        public function add_scripts() {
            // bs, mycss, popper, bsJs, myJs
            wp_register_style( 'bs_css', "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css", array(), '4.0-alpha', 'all');
            wp_register_style( 'front_end_css', plugins_url('resources/css/style.css', __FILE__), array(), '1.0', 'all');
            wp_register_script( 'popper', "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js", array(), true);
            wp_register_script( 'bs_js', "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js", array('jquery'), true);
            wp_register_script( 'my_js', plugins_url( 'resources/js/app.js', __FILE__), array('jquery'), true );

            wp_enqueue_style( 'bs_css' );

            wp_enqueue_script( 'popper' );
            wp_enqueue_script( 'bs_js' );
            wp_enqueue_script( 'my_js' );
            wp_enqueue_style( 'front_end_css');
        }
        // admin includes
        public function add_admin_scripts() {
            // bs, popper, bsJs, myJs
            wp_register_style( 'bs_css', "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css", array(), '4.0-alpha', 'all');
            wp_register_style( 'admin_css', plugins_url('resources/css/admin.css', __FILE__), array(), '2-21-18', 'all');
            wp_register_script( 'popper', "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js", array(), true);
            wp_register_script( 'admin_bs_js', "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js", array(), true);
            wp_register_script( 'my_admin_js', plugins_url( 'resources/js/admin.js', __FILE__), array('jquery'), true );

            wp_enqueue_style( 'bs_css' );
            wp_enqueue_style( 'admin_css');
            wp_enqueue_script( 'popper' );
            wp_enqueue_script( 'admin_bs_js' );
            wp_enqueue_script( 'my_admin_js' );

        }


        public function register_activation() {
            flush_rewrite_rules();
        }
        public function register_deactivation() {
        }
        public function admin_init() {
        }

        /**
         * Adds MyTunes MetaBox to the appropriate page
         */
        public function add_meta_boxes() {

            $postTypes = $this->get_settings( 'post_types' );
            if( is_array( $postTypes )) {

                foreach ( $postTypes as $type ) {
                    add_meta_box(
                        'mytunes_metabox',
                        __('MyTune Information', 'mytunes'),
                        array( $this, 'mytunes_metabox' ),
                        $type,
                        'normal',
                        'high',
                        null
                    );
                }
            }
        }

        function mytunes_metabox( $post ) {
            $selected_key = get_post_meta( $post->ID, '_mytunes_key', true );
            $status = get_post_meta( $post->ID, '_mytunes_status', true );
            $links = get_post_meta( $post->ID, '_mytunes_links', false );
            $source = get_post_meta( $post->ID, '_mytunes_source', true );
            $keys = array('A','Bb','B','C','C#','D','Eb','E','F','F#','G','Ab');
            // mytunes-key, mytunes-status, mytunes-source, mytunes-video-link
            ?>

            <div class="wrap mytunes-metabox" id="mytunes-metabox">
                <div class="form-group row">
                    <label class="col-sm-3" for="mytunes-key">Primary Key:</label>
                    <select class="form-control-sm col-sm-1  mytunes-key-input" name="mytunes-key" id="mytunes-key">
                        <?php
                        print "<option value=''></option>";
                        foreach( $keys as $key) {
                            if($selected_key && $key == $selected_key) {
                                print "<option value='$key' selected>$key</option>";
                            } else {
                                print "<option value='$key'>$key</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <div class="mytunes-range-lable row">
                        <label class="col-3" for="mytunes-status">Difficulty Rating:</label>

                        <input class="form-control-sm col-2" name="mytunes-status" type="number" id="mytunes-status-display" readonly>
                        <div class="col-7">
                            <input class="" name="range-status" type="range" id="mytunes-range" value="<?php echo ($status) ? ($status * 10) : 5 ?>"  max="100", min="0" step="1">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-3" for="mytunes-source">Source: </label>
                    <input class="col-9 form-control-sm" type="text" value="<?php echo ($source) ? $source : "" ?>" name="mytunes-source" id="mytunes-source">
                </div>
                <div class="form-group row">
                    <label class="col-3" for="mytunes-source">Add
                        <a href="https://www.youtube.com/" rel="noopener noreferrer" target="_blank">Video</a>
                        Link: </label>
                    <input class="col-9 form-control-sm" type="url" name="mytunes-video-link" id="mytunes-source">
                </div>

                <div>



                </div>



                <div>
                    <ul>
                        <?php

                        if($links && count($links)) {
                            print "<table class='table table-striped table-bordered' >";
                            print "<th>Here</th><th>Current Links:</th><th>Remove</th>";
                            foreach ($links[0] as $key => $link) {

                                print "<tr>";
                                print "<td></td>";
                                print "<td>" . $link . "</td>";
                                print "<td style='width:5em;'>";
                                ?>
                                <input type="checkbox" name="mytunes-link-table[]" value="<?php echo $key ?>"><span class="text-danger dashicons dashicons-no"></span>
                                <?php
                                print "</tr>";
                            }
                            print "</table>";
                        }
                        ?>
                    </ul>
                </div>
                <!-- name of action / name of nonce field -->
                <?php wp_nonce_field( basename( __FILE__ ), 'mytunes_meta_nonce', true, true ); ?>
            </div>
            <?php
        }


        /**
         * * https://youtu.be/aL-dUZJydcw - one example of a 'share' address
         * https://www.youtube.com/watch?v=aL-dUZJydcw - example of a raw url
         * either https://youtu.be || https://www.youtube
         * - if first, get rest of characters after .be/
         * - if second, get characters after v=
         */

        public function check_youtube_link( $link ) {

            $share_link = 'https://youtu.be/';
            $raw_link = 'https://www.youtube.com/watch?v=';

            // $share_link is in the url
            if( strpos($link, $raw_link) !== false ) {
                print_r($link);
                print_r("<br>");
                $code = substr($link, strlen($raw_link));
                return $code;
            }
            // $raw_link is in the url
            else if( strpos($link, $share_link) !== false ) {
                print_r($link);
                print_r("<br>");
                $code = substr($link, strlen($share_link));
                return $code;
            }
            return false;
        }



        public function save_post_meta( $post_id ) {

            $keyKey = 'mytunes-key';
            $statusKey = 'mytunes-status';
            $sourceKey = 'mytunes-source';
            $linkKey = 'mytunes-video-link';
            $nonceName = 'mytunes_meta_nonce';
            $deleteLinksKey = 'mytunes-link-table';
            // get the array that holds all of the existing links //
            $links = get_post_meta( $post_id, '_mytunes_links', true );

            if( !isset( $_POST[$nonceName]) ) { // nonce not set
                $this->mytunes_log( "Nonce NOT found! \n" );
            } else { // nonce is set

                // If links list is empty, the key will not exist
                // If nothing is checked, the key does not exist //
                // something must be checked for this array key to show up //

                if( array_key_exists( $deleteLinksKey, $_POST) ) {
                    // I expect this to be an array of values - links[] //
                    $linksToDelete = $_POST[$deleteLinksKey];
                    // This array has the ids from the $links[0] array to delete

                    foreach ( $linksToDelete as $link ) {
                        unset($links[$link]);
                    }

                    update_post_meta( $post_id, '_mytunes_links', $links );
                }

                if( array_key_exists( $keyKey, $_POST) ) {
                    $_POST[$keyKey] = sanitize_text_field( $_POST[$keyKey] );
                    update_post_meta(
                            $post_id,
                            '_mytunes_key',
                            $_POST[$keyKey]
                    );
                }
                if( array_key_exists( $statusKey, $_POST) ) {
                    $_POST[$statusKey] = intval( sanitize_text_field( $_POST[$statusKey] ) );
                    update_post_meta(
                        $post_id,
                        '_mytunes_status',
                        $_POST[$statusKey]
                    );
                }
                if( array_key_exists( $sourceKey, $_POST) ) {
                    $_POST[$sourceKey] = sanitize_text_field( $_POST[$sourceKey] );
                    update_post_meta(
                        $post_id,
                        '_mytunes_source',
                        $_POST[$sourceKey]
                    );
                }

                // Looks for the new link string to add to the list
                if( array_key_exists( $linkKey, $_POST) && $_POST[$linkKey] != '' ) {

                    $youTubeCode = "";
                    // Only storing urls if they pass the youtube test //
                    if($this->check_youtube_link( $_POST[$linkKey] )) {
//                        $youTubeCode = $this->check_youtube_link( $_POST[$linkKey] );

                        $_POST[$linkKey] = sanitize_text_field( $_POST[$linkKey] );

                        // if $links is already an array and has something in it //
                        if(is_array( $links ) && count($links)) {
                            $links[] = $_POST[$linkKey];
                        } else { // create new array and save as $links //
                            $links = array( $_POST[$linkKey] );
                        }
                        update_post_meta(
                            $post_id,
                            '_mytunes_links',
                            $links
                        );
                    }
                }
            }
        }

        /**
         * Custom error_log function
         * @param $log
         */
        public function mytunes_log( $log ) {
            if( true === WP_DEBUG ) {
                if( is_array( $log ) || is_object( $log ) ) {
                    error_log( print_r(  $log ) );
                } else {
                    error_log("MyTunesLog: " . $log );
                }
            }
        }

        public function myt_register_post_type() {

            $labels = array(
                'name' => 'MyTunes',
                'singular_name' => __('MyTune'),
                'add_new' => __("Add New Tune"),
                'add_new_item' => __("Add New Tune"),
                'edit_item' => __("Edit Tune"),
                'new_item' => __("New MyTune"),
                'all_items' => __('All MyTunes'),
                'view_items' => __('View MyTunes'),
                'search_items' => __('Search MyTunes'),
                'not_found' => __('No Tune Found'),
                'not_found_in_trash' => __('No Tune Found in Trash'),
                'menu_name' => __('MyTunes')
            );

            register_post_type('mytunes',
                    array(
                        'public' => true,
                        'show_ui' => true,
                        'has_archive' => true,
                        'rewrite' => array('slug' => 'mytune'),
                        'menu_icon' => 'dashicons-format-audio',
                        'labels' => $labels,
                        'supports' => array('title', 'editor', 'thumbnail', 'comments'),
                        'taxonomies' => array( 'post_tag' ),
                    )
                );
        }

        // Other Functions linked to in constructor/else block - notAdmin //
        // Any Shortcode Function //
        // Any the_content hook functions //

        public function get_mytunes_single_template($single_template) {
            global $wp_query, $post;

            if($post->post_type == 'mytunes') {
                $single_template = plugin_dir_path(__FILE__) . 'templates/single-mytunes.php';
            } // end if my custom post type

            return $single_template;
        } // end get_mytunes_single function

        // If page is 'mytunes-page' than this template is used //
        public function get_mytunes_page_template($page_template) {
            // so for this to work, user has to create a page with the slug "archive-page"
            if( is_page('mytunes-page') ) {
                $page_template = plugin_dir_path(__FILE__) . 'templates/mytunes-page.php';
            } // end if my custom post type
            return $page_template;
        } // end get_mytunes_archive function




        // Private Function //

        /**
         * Set up Settings dialog, under Settings in the Dashboard
         */
        private function initialize_settings() {
            // create an object to hold the settings
            $default_settings = array(
                'post_types' => array('mytunes')
            );
            // init the settings prop of the plugin class
            $this->settings = get_option( 'MyTunes_option', $default_settings );
        }

        // Used to access settings in $this->settings
        private function get_settings( $key ) {
            if( $key && isset( $this->settings[$key] )) {
                return $this->settings[$key];
            }
        }
    }
    MyTunes::Instance();
}