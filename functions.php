<?php

/**
 * Adds categories slugs as classes to the category in the loop to allow styling.
 */
if ( ! function_exists( 'twentyten_category_classes' ) ) :
	function twentyten_category_classes() {
		echo 'cat-links';
		$categories = get_the_category();
		foreach ( $categories as $cat ) {
			echo ' ' . $cat->slug;
		}
	}
endif;

/**
 * Replaces the meta data line of the parent theme with a custom one to allow the external author plugin to overwrite it.
 */
if ( ! function_exists( 'twentyten_posted_on' ) ) :
	function twentyten_posted_on() {
		$author_html = sprintf( ' <span class="meta-sep">by</span> <span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'twentyten' ), get_the_author() ) ),
			get_the_author()
		);

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'external-author/external-author.php' ) ) {
			if ( get_post_meta( get_the_ID(), '_external_authors_no_author', true ) ) {
				$author_html = '';
			} else {
				$external_authors = get_post_meta( get_the_ID(), '_external_authors', true );
				if ( $external_authors && count( $external_authors ) > 0 ) {
					$author_html = ' <span class="meta-sep">by</span> ';
					$i = 0;
					foreach ( $external_authors as $author ) {
						if ( is_plugin_active( 'lems-judge-image-helper/lems-judge-image-helper.php' ) && ! empty( $author['dci'] ) ) {
							$single_author_html = do_shortcode( sprintf( '[judge dci=%2$s]%1$s[/judge]',
								$author['name'],
								$author['dci']
							) );
						} else {
							$single_author_html = $author['name'];
						}

						$author_html .= do_shortcode( sprintf( '<span class="author vcard">%1$s</span>',
							$single_author_html
						) );

						if ( $i < count( $external_authors ) - 2 ) {
							$author_html .= ', ';
						} else if ( $i < count( $external_authors ) - 1 ) {
							$author_html .= ' ' . __( ' and ' );
						}
						$i++;
					}
				}
			}
		}

		printf( __( '<span class="%1$s">Posted on</span> %2$s%3$s', 'twentyten' ),
			'meta-prep meta-prep-author',
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
				get_permalink(),
				esc_attr( get_the_time() ),
				get_the_date()
			),
			$author_html
		);
	}
endif;

/**
 * Custom continue reading link.
 */
if ( ! function_exists( 'twentyten_continue_reading_link' ) ) :
	function twentyten_continue_reading_link() {
		return '<div class="continue-reading"><a class="pure-button" href="' . get_permalink() . '">' . __( 'Continue reading', 'twentyten' ) . '</a></div>';
	}
endif;

function twentyten_judge_child_thumbnail_size( $size ) {
	return;
}

add_filter( 'post_thumbnail_size', 'twentyten_judge_child_thumbnail_size' );

function theme_setup() {
	load_theme_textdomain( 'twentyten', get_stylesheet_directory() . '/languages' );
	register_nav_menus( array(
		'language'        => __( 'Language Navigation', 'twentyten-judge' ),
		'language-single' => __( 'Language Navigation Single', 'twentyten-judge' ),
	) );

	if ( ! get_theme_mod( 'show_excerpts' ) ) {
		set_theme_mod( 'show_excerpts', 'excerpts' );
	}

	add_filter( 'get_the_excerpt', 'do_shortcode' );
	remove_filter( 'get_the_excerpt', 'wp_trim_excerpt', 10 );
}

add_action( 'after_setup_theme', 'theme_setup' );

function twentyten_judge_child_wp_trim_excerpt( $text = '' ) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		$text = get_the_content( '' );

		/** This filter is documented in wp-includes/post-template.php */
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		/**
		 * Filter the number of words in an excerpt.
		 *
		 * @since 2.7.0
		 *
		 * @param int $number The number of words. Default 55.
		 */
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		/**
		 * Filter the string in the "more" link displayed after a trimmed excerpt.
		 *
		 * @since 2.9.0
		 *
		 * @param string $more_string The string shown within the more link.
		 */
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
	}

	/**
	 * Filter the trimmed excerpt string.
	 *
	 * @since 2.8.0
	 *
	 * @param string $text The trimmed text.
	 * @param string $raw_excerpt The text prior to trimming.
	 */

	return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
}

add_filter( 'get_the_excerpt', 'twentyten_judge_child_wp_trim_excerpt', 99, 1 );

function judge_child_customizer( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_setting( 'show_excerpts', array(
		'default'   => 'excerpts',
		'transport' => 'refresh',
	) );

	$wp_customize->add_section( 'judge_child_customizer', array(
		'title'    => __( 'Content Options', 'twentyten-judge' ),
		'priority' => 100,
	) );

	$wp_customize->add_control( 'judge_child_excerpt_control', array(
		'label'    => __( 'Content Excerpts' ),
		'section'  => 'judge_child_customizer',
		'settings' => 'show_excerpts',
		'type'     => 'radio',
		'choices'  => array(
			'excerpts' => __( 'Only Excerpts', 'twentyten-judge' ),
			'full'     => __( 'Full Posts', 'twentyten-judge' ),
		),
	) );

}

add_action( 'customize_register', 'judge_child_customizer' );

function show_author_info() {
	if ( is_plugin_active( 'external-author/external-author.php' ) ) {
		if ( get_post_meta( get_the_ID(), '_external_authors_no_author', true ) ||
		     count( get_post_meta( get_the_ID(), '_external_authors', true ) ) > 0
		) {
			return false;
		}
	}

	return true;
}

/**
 * Show the featured author image, if there is one set
 * @requires external-author and lems-judge-image-helper plugins
 */
if ( ! function_exists( 'twentyten_featured_author' ) ) {
	function twentyten_featured_author() {
		$image_html = '';
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'external-author/external-author.php' )
		     && is_plugin_active( 'lems-judge-image-helper/lems-judge-image-helper.php' )
		) {
			if ( get_post_meta( get_the_ID(), '_external_authors_no_author', true ) == false ) {
				$external_authors = get_post_meta( get_the_ID(), '_external_authors', true );
				$featured_index = get_post_meta( get_the_ID(), '_external_authors_featured', true );
				$featured_author = false;
				if ( isset( $external_authors[ $featured_index ] ) ) {
					$featured_author = $external_authors[ $featured_index ];
				}

				if ( $featured_author && ! empty( $featured_author['dci'] ) ) {
					$image_html =
						'<div class="featured-image">
							<img src="' . get_source_from_dci( $featured_author['dci'] ) . '" class="wp-post-image" alt="' . htmlentities( $featured_author['name'] ) . '">
						</div>';
				}
			}
		}
		echo $image_html;
	}
}