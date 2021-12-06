<?php
/**
 * Webfonts API: Webfonts functions
 *
 * @since 6.0.0
 *
 * @package WordPress
 * @subpackage Webfonts
 */

/**
 * Instantiates the webfonts controller, if not already set, and returns it.
 *
 * @since 6.0.0
 *
 * @return WP_Webfonts Instance of the controller.
 */
function wp_webfonts() {
	static $instance;

	if ( ! $instance instanceof WP_Webfonts ) {
		$instance = new WP_Webfonts();
		$instance->init();
	}

	return $instance;
}

/**
 * Registers a collection of webfonts.
 *
 * Example of how to register Source Serif Pro font with font-weight range of 200-900
 * and font-style of normal and italic:
 *
 * If the font files are contained within the theme:
 * <code>
 * wp_register_webfonts(
 *      array(
 *          array(
 *              'font_family' => 'Source Serif Pro',
 *              'font_weight' => '200 900',
 *              'font_style'  => 'normal',
 *              'src'         => get_theme_file_uri( 'assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2' ),
 *          ),
 *          array(
 *              'font_family' => 'Source Serif Pro',
 *              'font_weight' => '200 900',
 *              'font_style'  => 'italic',
 *              'src'         => get_theme_file_uri( 'assets/fonts/source-serif-pro/SourceSerif4Variable-Italic.ttf.woff2' ),
 *          ),
 *      )
 * );
 * </code>
 *
 * @since 6.0.0
 *
 * @param array $webfonts Webfonts to be registered.
 *                        This contains an array of webfonts to be registered.
 *                        Each webfont is an array.
 *                        See {@see WP_Webfonts_Registry::register()} for a list of
 *                        supported arguments for each webfont.
 */
function wp_register_webfonts( array $webfonts = array() ) {
	foreach ( $webfonts as $webfont ) {
		wp_register_webfont( $webfont );
	}
}

/**
 * Registers a single webfont.
 *
 * Example of how to register Source Serif Pro font with font-weight range of 200-900:
 *
 * If the font file is contained within the theme:
 * ```
 * wp_register_webfont(
 *      array(
 *          'font_family' => 'Source Serif Pro',
 *          'font_weight' => '200 900',
 *          'font_style'  => 'normal',
 *          'src'         => get_theme_file_uri( 'assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2' ),
 *      )
 * );
 * ```
 *
 * @since 6.0.0
 *
 * @param array $webfont Webfont to be registered.
 *                       See {@see WP_Webfonts_Registry::register()} for a list of supported arguments.
 */
function wp_register_webfont( array $webfont ) {
	/**
	 * Filters the webfonts to be registered.
	 *
	 * @since 6.0.0
	 *
	 * @param array $webfont Webfont to be registered.
	 *                       See {@see WP_Webfonts_Registry::register()} for a list of supported arguments.
	 *
	 * @return array $webfont Webfont to be registered.
	 */
	$webfont = apply_filters( 'wp_register_webfont', $webfont );

	wp_webfonts()->register_font( $webfont );
}

