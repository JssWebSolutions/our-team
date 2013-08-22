<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'woothemes_get_our_team' ) ) {
/**
 * Wrapper function to get the team members from the Woothemes_Our_Team class.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return array/boolean       Array if true, boolean if false.
 */
function woothemes_get_our_team ( $args = '' ) {
	global $woothemes_our_team;
	return $woothemes_our_team->get_our_team( $args );
} // End woothemes_get_our_team()
}

/**
 * Enable the usage of do_action( 'woothemes_our_team' ) to display team members within a theme/plugin.
 *
 * @since  1.0.0
 */
add_action( 'woothemes_our_team', 'woothemes_our_team' );

if ( ! function_exists( 'woothemes_our_team' ) ) {
/**
 * Display or return HTML-formatted testimonials.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return string
 */
function woothemes_our_team ( $args = '' ) {
	global $post;

	$defaults = array(
		'limit' => 5,
		'per_row' => null,
		'orderby' => 'menu_order',
		'order' => 'DESC',
		'id' => 0,
		'display_author' => true,
		'display_avatar' => true,
		'display_url' => true,
		'effect' => 'fade', // Options: 'fade', 'none'
		'pagination' => false,
		'echo' => true,
		'size' => 50,
		'title' => '',
		'before' => '<div class="widget widget_woothemes_our_team">',
		'after' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
		'category' => 0
	);

	$args = wp_parse_args( $args, $defaults );

	// Allow child themes/plugins to filter here.
	$args = apply_filters( 'woothemes_our_team_args', $args );
	$html = '';

	do_action( 'woothemes_our_team_before', $args );

		// The Query.
		$query = woothemes_get_our_team( $args );

		// The Display.
		if ( ! is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			$class = '';

			if ( is_numeric( $args['per_row'] ) ) {
				$class .= ' columns-' . intval( $args['per_row'] );
			}

			if ( 'none' != $args['effect'] ) {
				$class .= ' effect-' . $args['effect'];
			}

			$html .= $args['before'] . "\n";
			if ( '' != $args['title'] ) {
				$html .= $args['before_title'] . esc_html( $args['title'] ) . $args['after_title'] . "\n";
			}
			$html .= '<div class="team-members component' . esc_attr( $class ) . '">' . "\n";

			$html .= '<div class="team-member-list">' . "\n";

			// Begin templating logic.
			$tpl = '<div id="quote-%%ID%%" class="%%CLASS%%"><blockquote class="team-member-text">%%TEXT%%</blockquote>%%AVATAR%% %%AUTHOR%%<div class="fix"></div></div>';
			$tpl = apply_filters( 'woothemes_our_team_item_template', $tpl, $args );

			$count = 0;
			foreach ( $query as $post ) { $count++;
				$template = $tpl;

				$css_class = 'quote';
				if ( 1 == $count ) { $css_class .= ' first'; }
				if ( $per_row == $count || count( $query ) == $count ) { $css_class .= ' last'; }

				// Add a CSS class if no image is available.
				if ( isset( $post->image ) && ( '' == $post->image ) ) {
					$css_class .= ' no-image';
				}

				setup_postdata( $post );

				$author = '';
				$author_text = '';

				// If we need to display the author, get the data.
				if ( ( get_the_title( $post ) != '' ) && true == $args['display_author'] ) {
					$author .= '<cite class="author">';

					$author_name = get_the_title( $post );

					$author .= $author_name;

					if ( isset( $post->byline ) && '' != $post->byline ) {
						$author .= ' <span class="excerpt">' . $post->byline . '</span><!--/.excerpt-->' . "\n";
					}

					if ( true == $args['display_url'] && '' != $post->url ) {
						$author .= ' <span class="url"><a href="' . esc_url( $post->url ) . '">' . $post->url . '</a></span><!--/.excerpt-->' . "\n";
					}

					$author .= '</cite><!--/.author-->' . "\n";

					// Templating engine replacement.
					$template = str_replace( '%%AUTHOR%%', $author, $template );
				} else {
					$template = str_replace( '%%AUTHOR%%', '', $template );
				}

				// Templating logic replacement.
				$template = str_replace( '%%ID%%', get_the_ID(), $template );
				$template = str_replace( '%%CLASS%%', esc_attr( $css_class ), $template );

				if ( isset( $post->image ) && ( '' != $post->image ) && true == $args['display_avatar'] ) {
					$template = str_replace( '%%AVATAR%%', '<a href="' . esc_url( $post->url ) . '" class="avatar-link">' . $post->image . '</a>', $template );
				} else {
					$template = str_replace( '%%AVATAR%%', '', $template );
				}

				// Remove any remaining %%AVATAR%% template tags.
				$template = str_replace( '%%AVATAR%%', '', $template );
				$content = apply_filters( 'woothemes_our_team_content', get_the_content(), $post );
				$template = str_replace( '%%TEXT%%', $content, $template );

				// Assign for output.
				$html .= $template;

				if( 0 < $count && ( 0 == $count % $args['per_row'] ) ) {
					$html .= '<div class="fix"></div>' . "\n";
				}
			}

			wp_reset_postdata();

			$html .= '</div><!--/.testimonials-list-->' . "\n";

			if ( $args['pagination'] == true && count( $query ) > 1 && $args['effect'] != 'none' ) {
				$html .= '<div class="pagination">' . "\n";
				$html .= '<a href="#" class="btn-prev">' . apply_filters( 'woothemes_our_team_prev_btn', '&larr; ' . __( 'Previous', 'woothemes-our-team' ) ) . '</a>' . "\n";
		        $html .= '<a href="#" class="btn-next">' . apply_filters( 'woothemes_our_team_next_btn', __( 'Next', 'woothemes-our-team' ) . ' &rarr;' ) . '</a>' . "\n";
		        $html .= '</div><!--/.pagination-->' . "\n";
			}
				$html .= '<div class="fix"></div>' . "\n";
			$html .= '</div><!--/.testimonials-->' . "\n";
			$html .= $args['after'] . "\n";
		}

		// Allow child themes/plugins to filter here.
		$html = apply_filters( 'woothemes_our_team_html', $html, $query, $args );

		if ( $args['echo'] != true ) { return $html; }

		// Should only run is "echo" is set to true.
		echo $html;

		do_action( 'woothemes_our_team_after', $args ); // Only if "echo" is set to true.
} // End woothemes_our_team()
}

if ( ! function_exists( 'woothemes_our_team_shortcode' ) ) {
/**
 * The shortcode function.
 * @since  1.0.0
 * @param  array  $atts    Shortcode attributes.
 * @param  string $content If the shortcode is a wrapper, this is the content being wrapped.
 * @return string          Output using the template tag.
 */
function woothemes_our_team_shortcode ( $atts, $content = null ) {
	$args = (array)$atts;

	$defaults = array(
		'limit' => 5,
		'per_row' => null,
		'orderby' => 'menu_order',
		'order' => 'DESC',
		'id' => 0,
		'display_author' => true,
		'display_avatar' => true,
		'display_url' => true,
		'effect' => 'fade', // Options: 'fade', 'none'
		'pagination' => false,
		'echo' => true,
		'size' => 50,
		'category' => 0
	);

	$args = shortcode_atts( $defaults, $atts );

	// Make sure we return and don't echo.
	$args['echo'] = false;

	// Fix integers.
	if ( isset( $args['limit'] ) ) $args['limit'] = intval( $args['limit'] );
	if ( isset( $args['size'] ) &&  ( 0 < intval( $args['size'] ) ) ) $args['size'] = intval( $args['size'] );
	if ( isset( $args['category'] ) && is_numeric( $args['category'] ) ) $args['category'] = intval( $args['category'] );

	// Fix booleans.
	foreach ( array( 'display_author', 'display_url', 'pagination', 'display_avatar' ) as $k => $v ) {
		if ( isset( $args[$v] ) && ( 'true' == $args[$v] ) ) {
			$args[$v] = true;
		} else {
			$args[$v] = false;
		}
	}

	return woothemes_our_team( $args );
} // End woothemes_our_team_shortcode()
}

add_shortcode( 'woothemes_our_team', 'woothemes_our_team_shortcode' );

if ( ! function_exists( 'woothemes_our_team_content_default_filters' ) ) {
/**
 * Adds default filters to the "woothemes_our_team_content" filter point.
 * @since  1.0.0
 * @return void
 */
function woothemes_our_team_content_default_filters () {
	add_filter( 'woothemes_our_team_content', 'do_shortcode' );
} // End woothemes_our_team_content_default_filters()

add_action( 'woothemes_our_team_before', 'woothemes_our_team_content_default_filters' );
}
?>