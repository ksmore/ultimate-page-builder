<?php

    defined( 'ABSPATH' ) or die( 'Keep Silent' );

    function upb_elements_register_action() {
        do_action( 'upb_register_element', upb_elements() );
    }

    add_action( 'wp_loaded', 'upb_elements_register_action' );

    function upb_tabs_register_action() {
        do_action( 'upb_register_tab', upb_tabs() );
    }

    add_action( 'wp_loaded', 'upb_tabs_register_action' );

    function upb_settings_register_action() {
        do_action( 'upb_register_setting', upb_settings() );
    }

    add_action( 'wp_loaded', 'upb_settings_register_action' );


    // Content Load

    add_filter( 'upb-before-contents', function ( $contents, $shortcodes ) {
        ob_start();

        upb_get_template( 'wrapper/before.php', compact( 'contents', 'shortcodes' ) );

        return ob_get_clean();
    }, 10, 2 );

    add_filter( 'upb-on-contents', function ( $contents, $shortcodes ) {
        ob_start();
        upb_get_template( 'wrapper/contents.php', compact( 'contents', 'shortcodes' ) );

        return ob_get_clean();
    }, 10, 2 );

    add_filter( 'upb-after-contents', function ( $contents, $shortcodes ) {
        ob_start();

        upb_get_template( 'wrapper/after.php', compact( 'contents', 'shortcodes' ) );

        return ob_get_clean();
    }, 10, 2 );

    add_filter( 'the_content', function ( $contents ) {

        if ( upb_is_enabled() ):
            $position   = get_post_meta( get_the_ID(), '_upb_settings_page_position', TRUE );
            $shortcodes = get_post_meta( get_the_ID(), '_upb_shortcodes', TRUE );

            return apply_filters( $position, $contents, $shortcodes );
        endif;

        return $contents;


    } );

    // Body Class
    add_filter( 'body_class', function ( $classes ) {
        if ( upb_is_enabled() ):
            array_push( $classes, 'ultimate-page-builder' );
        endif;

        return $classes;
    } );

    // Scripts
    add_action( 'wp_enqueue_scripts', function () {

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_script( 'upb-scoped-css-polyfill', UPB_PLUGIN_ASSETS_URI . "js/upb-scoped-polyfill$suffix.js", array(), FALSE, TRUE );


        if ( upb_is_enabled() ):
            wp_enqueue_style( 'upb-grid' );
            wp_enqueue_script( 'upb-scoped-css-polyfill' );

            // Load Shortcodes Styles
            upb_enqueue_shortcode_assets();
        endif;
    } );

    // Add Toolbar Menus
    add_action( 'wp_before_admin_bar_render', function () {
        global $wp_admin_bar;

        $enabled = array(
            'id'    => 'load-upb',
            'title' => esc_html__( 'Load Page Builder', 'ultimate-page-builder' ),
            'href'  => esc_url( add_query_arg( 'upb', '1', get_permalink() ) ),
            'group' => FALSE
        );

        $use = array(
            'id'    => 'use-upb',
            'title' => esc_html__( 'Use Build Page', 'ultimate-page-builder' ),
            'href'  => esc_url( add_query_arg( 'upb', '1', get_permalink() ) ),
            'group' => FALSE
        );

        if ( upb_is_buildable() && upb_is_enabled() ):
            $wp_admin_bar->add_menu( $enabled );
        endif;

        if ( upb_is_buildable() && ! upb_is_enabled() ):
            $wp_admin_bar->add_menu( $use );
        endif;

    } );


