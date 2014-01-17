<?php

if ( ! function_exists( 'twentyten_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 *
	 * @since Twenty Ten 1.0
	 */
	function twentyten_posted_on() {
		$author_html = sprintf( ' <span class="meta-sep">by</span> <span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'twentyten' ), get_the_author() ) ),
			get_the_author()
		);

		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if ( is_plugin_active( 'external-author/external-author.php' ) ) {
			if ( get_post_meta( get_the_ID(), '_external_authors_no_author', true ) ) {
				$author_html = '';
			} else {
				$external_authors = get_post_meta( get_the_ID(), '_external_authors', true );
				if ( $external_authors && count( $external_authors ) > 0 ) {
					$author_html = ' <span class="meta-sep">by</span> ';
					foreach ( $external_authors as $index => $author ) {
						if ( is_plugin_active( 'lems-judge-image-helper/lems-judge-image-helper.php' ) && ! empty($author['dci']) ) {
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