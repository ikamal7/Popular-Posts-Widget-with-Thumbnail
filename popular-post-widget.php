<?php
    
    /**
     * Plugin Name: Popular Post Widget
     * Plugin URI: https://github.com/ikamal7/Popular-Posts-Widget-with-Thumbnail/
     * Description: Popular posts widget show popular posts in your site with thumbnail
     * Author: Kamal Hosen
     * Version: 1.0
     * Author URI: https://ikamal.me/
     * License: GPLv2 or later
     * Text Domain: popular-post-widget
     * Domain Path: /languages/
     */
    
    /*
        Copyright (C) 2018  Kamal kamalhosen8920@gmail.com
    
        This program is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License, version 2, as
        published by the Free Software Foundation.
    
        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.
    
        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */


//Include file 
    
    require( plugin_dir_path( __FILE__ ) . '/bootstrap.php' );
    
    /**
     * Set Post views
     */
    
    function ppw_set_popular_post_view( $postID ) {
        $countkey = 'views';
        $count    = get_post_meta( $postID, $countkey, true );
        if ( '' == $countkey ){
            delete_post_meta( $postID, $countkey );
            add_post_meta( $postID, $countkey, '0' );
        }
        else {
            $count++;
            update_post_meta( $postID, $countkey, $count );
        }
    }
    
    
    /**
     * Track Post views
     */
    
    function ppw_track_popular_post_view( $post_id ) {
        if ( ! is_single() ){
            return;
        }
        if ( empty( $post_id ) ){
            global $post;
            $post_id = $post->ID;
        }
        ppw_set_popular_post_view( $post_id );
        
    }
    
    add_action( 'wp_head', 'ppw_track_popular_post_view' );
    
    
    /**
     * new WordPress Widget format
     * Wordpress 2.8 and above
     * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
     */
    class Popular_Post_Widget extends WP_Widget {
        
        /**
         * Constructor
         *
         * @return void
         */
        function __construct() {
            $widget_ops = array(
                'classname'   => 'popularpost',
                'description' => __('Popular Posts Widget with Thumbnail', 'popular-post-widget')
            );
            parent::__construct( 'popularpost', __('Popular Posts Widget with Thumbnail', 'popular-post-widget'), $widget_ops );
        }
        
        /**
         * Outputs the HTML for this widget.
         *
         * @param array  An array of standard parameters for widgets in this theme
         * @param array  An array of settings for this widget instance
         *
         * @return void Echoes it's output
         */
        function widget( $args, $instance ) {
            $title  = ! empty( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Popular Posts', 'popular-post-widget' );
            $title  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
            $number = ( ! empty( $instance[ 'number' ] ) ) ? absint( $instance[ 'number' ] ) : 5;
            if ( ! $number ){
                $number = 5;
            }
            
            $show_date = isset( $instance[ 'show_date' ] ) ? $instance[ 'show_date' ] : false;
            
            
            echo $args[ 'before_widget' ];
            if ( $title ){
                echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
            }
            $ppw_loop = new WP_Query( array(
                'posts_per_page'      => $number,
                'meta_key'            => 'views',
                'orderby'             => 'meta_value_num',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
            ) );
            
            ?>
            <?php if ( $ppw_loop->have_posts() ) : ?>
                <ul>
                    <?php
                        while ( $ppw_loop->have_posts() ) : $ppw_loop->the_post(); ?>

                            <li>
                                <div class="thumbnail">
                                    <?php the_post_thumbnail( 'thumbnail' ) ?>
                                </div>
                                <div class="post-info">

                                    <a href="<?php the_permalink(); ?>"><h4><?php the_title(); ?></h4></a>
                                    
                                    <?php if ( $show_date ){
                                        echo get_the_date();
                                    } ?>
                                </div>
                            </li>
                        <?php endwhile;
                        wp_reset_query(); ?>

                </ul>
            
            <?php endif; ?>
            <?php
            
            echo $args[ 'after_widget' ];
        }
        
        /**
         * Deals with the settings when they are saved by the admin. Here is
         * where any validation should be dealt with.
         *
         * @param array  An array of new settings as submitted by the admin
         * @param array  An array of the previous settings
         *
         * @return array The validated and (if necessary) amended settings
         */
        function update( $new_instance, $old_instance ) {
            
            // update logic goes here
            $instance                = $new_instance;
            $instance[ 'title' ]     = sanitize_text_field( $new_instance[ 'title' ] );
            $instance[ 'number' ]    = (int) $new_instance[ 'number' ];
            $instance[ 'show_date' ] = isset( $new_instance[ 'show_date' ] ) ? (bool) $new_instance[ 'show_date' ] : false;
            
            return $instance;
        }
        
        /**
         * Displays the form for this widget on the Widgets page of the WP Admin area.
         *
         * @param array  An array of the current settings for this widget
         *
         * @return void Echoes it's output
         */
        function form( $instance ) {
            $title     = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
            $number    = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 5;
            $show_date = isset( $instance[ 'show_date' ] ) ? (bool) $instance[ 'show_date' ] : false;
            
            ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'popular-post-widget'); ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       value="<?php echo $title; ?>" name="<?php echo $this->get_field_name( 'title' ); ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'popular-post-widget') ?></label>
                <input type="numberr" class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>"
                       name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1"
                       value="<?php echo $number; ?>" size="3"/>
            </p>
            <p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?>
                      id="<?php echo $this->get_field_id( 'show_date' ); ?>"
                      name="<?php echo $this->get_field_name( 'show_date' ); ?>"/>
                <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'popular-post-widget' ); ?></label>
            </p>
            
            
            <?php
            
        }
    }
    
    function ppw_register_widget() {
        register_widget( 'Popular_Post_Widget' );
    }
    
    add_action( 'widgets_init', 'ppw_register_widget' );