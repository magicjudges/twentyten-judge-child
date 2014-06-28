<?php

add_action( 'wp_enqueue_scripts', 'enqueue_dashicons_front_end' );

function enqueue_dashicons_front_end() {
	wp_enqueue_style( 'dashicons-style', get_stylesheet_uri(), array( 'dashicons' ), '1.0' );
}

if ( ! function_exists( 'twentyten_category_classes' ) ) :
	function twentyten_category_classes() {
		echo 'cat-links';
		$categories = get_the_category();
		foreach ( $categories as $cat ) {
			echo ' ' . $cat->slug;
		}
	}
endif;

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
					foreach ( $external_authors as $index => $author ) {
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
						if ( $index < count( $external_authors ) - 2 ) {
							$author_html .= ', ';
						} else if ( $index < count( $external_authors ) - 1 ) {
							$author_html .= ' ' . __( ' and ' );
						}
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

if ( ! function_exists( 'twentyten_continue_reading_link' ) ) :
	function twentyten_continue_reading_link() {
		return '<div class="continue-reading"><a class="pure-button" href="' . get_permalink() . '">' . __( 'Continue reading', 'twentyten' ) . '</a></div>';
	}
endif;

add_filter( 'post_thumbnail_size', 'twentyten_judge_child_thumbnail_size' );

function twentyten_judge_child_thumbnail_size( $size ) {
	return;
}

add_action( 'after_setup_theme', 'language_setup' );

function language_setup() {
	load_theme_textdomain( 'twentyten', get_stylesheet_directory() . '/languages' );
	register_nav_menus( array(
		'language' => __( 'Language Navigation', 'twentyten-judge' ),
	) );
}

function judge_child_customizer( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_setting( 'show_excerpts' , array(
		'default'     => 'true',
		'transport'   => 'refresh',
	) );

	$wp_customize->add_section( 'judge_child_customizer' , array(
		'title'      => __( 'Content Options', 'twentyten-judge' ),
		'priority'   => 100,
	) );

	$wp_customize->add_control( 'judge_child_excerpt_control', array(
		'label' => __('Content Excerpts'),
		'section' => 'judge_child_customizer',
		'settings' => 'show_excerpts',
		'type' => 'radio',
		'choices' => array(
			'true' => __('Only Excerpts', 'twentyten-judge'),
			'false' => __('Full Posts', 'twentyten-judge')
		)
	) );

}
add_action( 'customize_register', 'judge_child_customizer' );