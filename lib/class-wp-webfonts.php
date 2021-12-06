<?php
/**
 * Webfonts API class.
 *
 * @package Gutenberg
 */

/**
 * Class WP_Webfonts
 */
class WP_Webfonts {

	/**
	 * An array of registered webfonts.
	 *
	 * @static
	 * @access private
	 * @var array
	 */
	private static $webfonts = array();

	/**
	 * Stylesheet handle.
	 *
	 * @var string
	 */
	private $stylesheet_handle = '';

	/**
	 * Init.
	 */
	public function init() {

		// Register callback to generate and enqueue styles.
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			$this->stylesheet_handle = 'webfonts-footer';
			$hook                    = 'wp_print_footer_scripts';
		} else {
			$this->stylesheet_handle = 'webfonts';
			$hook                    = 'wp_enqueue_scripts';
		}
		add_action( $hook, array( $this, 'generate_and_enqueue_styles' ) );

		// Enqueue webfonts in the block editor.
		add_action( 'admin_init', array( $this, 'generate_and_enqueue_editor_styles' ) );
	}

	/**
	 * Get the list of fonts.
	 *
	 * @return array
	 */
	public function get_fonts() {
		return self::$webfonts;
	}

	/**
	 * Register a webfont.
	 *
	 * @param array $font The font arguments.
	 */
	public function register_font( $font ) {
		$font = $this->validate_font( $font );
		if ( $font ) {
			$id                    = $this->get_font_id( $font );
			self::$webfonts[ $id ] = $font;
		}
	}

	/**
	 * Get the font ID.
	 *
	 * @param array $font The font arguments.
	 * @return string
	 */
	public function get_font_id( $font ) {
		return sanitize_title( "{$font['font-family']}-{$font['font-weight']}-{$font['font-style']}" );
	}

	/**
	 * Validate a font.
	 *
	 * @param array $font The font arguments.
	 *
	 * @return array|false The validated font arguments, or false if the font is invalid.
	 */
	public function validate_font( $font ) {
		$font = wp_parse_args(
			$font,
			array(
				'font-family'  => '',
				'font-style'   => 'normal',
				'font-weight'  => '400',
				'font-display' => 'fallback',
			)
		);

		// Check the font-family.
		if ( empty( $font['font-family'] ) || ! is_string( $font['font-family'] ) ) {
			trigger_error( __( 'Webfont font family must be a non-empty string.', 'gutenberg' ) );
			return false;
		}

		// Fonts need a "src".
		if ( empty( $font['src'] ) || ( ! is_string( $font['src'] ) && ! is_array( $font['src'] ) ) ) {
			trigger_error( __( 'Webfont src must be a non-empty string or an array of strings.', 'gutenberg' ) );
			return false;
		}

		// Validate the 'src' property.
		foreach ( (array) $font['src'] as $src ) {
			if ( empty( $src ) || ! is_string( $src ) ) {
				trigger_error( __( 'Each webfont src must be a non-empty string.', 'gutenberg' ) );
				return false;
			}

			if (
				// Validate data URLs.
				! preg_match( '/^data:.+;base64/', $src ) &&
				// Validate URLs.
				! filter_var( $src, FILTER_VALIDATE_URL ) &&
				// Check if it's a URL starting with "//" (omitted protocol).
				0 !== strpos( $src, '//' )
			) {
				trigger_error( __( 'Webfont src must be a valid URL or a data URI.', 'gutenberg' ) );
				return false;
			}
		}

		// Check the font-style.
		$valid_font_styles = array( 'normal', 'italic', 'oblique', 'inherit', 'initial', 'revert', 'unset' );
		if ( ! in_array( $font['font-style'], $valid_font_styles, true ) && ! preg_match( '/^oblique\s+(\d+)%/', $font['font-style'] ) ) {
			return false;
		}

		// Check the font-weight.
		if ( // Bail out if the font-weight is not a valid value.
			( ! is_string( $font['font-weight'] ) && ! is_int( $font['font-weight'] ) ) ||
			(
				// Check if value is a single font-weight, formatted as a number.
				! in_array( $font['font-weight'], array( 'normal', 'bold', 'bolder', 'lighter', 'inherit' ), true ) &&
				// Check if value is a single font-weight, formatted as a number.
				! preg_match( '/^(\d+)$/', $font['font-weight'], $matches ) &&
				// Check if value is a range of font-weights, formatted as a number range.
				! preg_match( '/^(\d+)\s+(\d+)$/', $font['font-weight'], $matches )
			)
		) {
			trigger_error( __( 'Webfont font weight must be a properly formatted string or integer.', 'gutenberg' ) );
			return false;
		}

		// Check the font-display.
		if ( ! in_array( $font['font-display'], array( 'auto', 'block', 'fallback', 'swap' ), true ) ) {
			$font['font-display'] = 'fallback';
		}

		$valid_props = array(
			'ascend-override',
			'descend-override',
			'font-display',
			'font-family',
			'font-stretch',
			'font-style',
			'font-weight',
			'font-variant',
			'font-feature-settings',
			'font-variation-settings',
			'line-gap-override',
			'size-adjust',
			'src',
			'unicode-range',
		);

		foreach ( $font as $prop => $value ) {
			if ( ! in_array( $prop, $valid_props, true ) ) {
				unset( $font[ $prop ] );
			}
		}

		return $font;
	}

	/**
	 * Generate and enqueue webfonts styles.
	 */
	public function generate_and_enqueue_styles() {
		// Generate the styles.
		$styles = $this->get_css();

		// Bail out if there are no styles to enqueue.
		if ( '' === $styles ) {
			return;
		}

		// Enqueue the stylesheet.
		wp_register_style( $this->stylesheet_handle, '' );
		wp_enqueue_style( $this->stylesheet_handle );

		// Add the styles to the stylesheet.
		wp_add_inline_style( $this->stylesheet_handle, $styles );
	}

	/**
	 * Generate and enqueue editor styles.
	 */
	public function generate_and_enqueue_editor_styles() {
		// Generate the styles.
		$styles = $this->get_css();

		// Bail out if there are no styles to enqueue.
		if ( '' === $styles ) {
			return;
		}

		wp_add_inline_style( 'wp-block-library', $styles );
	}

	/**
	 * Gets the `@font-face` CSS styles for locally-hosted font files.
	 *
	 * This method does the following processing tasks:
	 *    1. Orchestrates an optimized `src` (with format) for browser support.
	 *    2. Generates the `@font-face` for all its webfonts.
	 *
	 * For example, when given these webfonts:
	 * <code>
	 * array(
	 *      'source-serif-pro-200-900-normal' => array(
	 *          'font_family' => 'Source Serif Pro',
	 *          'font_weight' => '200 900',
	 *          'font_style'  => 'normal',
	 *          'src'         => 'https://example.com/wp-content/themes/twentytwentytwo/assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2' ),
	 *      ),
	 *      'source-serif-pro-400-900-italic' => array(
	 *          'font_family' => 'Source Serif Pro',
	 *          'font_weight' => '200 900',
	 *          'font_style'  => 'italic',
	 *          'src'         => 'https://example.com/wp-content/themes/twentytwentytwo/assets/fonts/source-serif-pro/SourceSerif4Variable-Italic.ttf.woff2' ),
	 *      ),
	 * )
	 * </code>
	 *
	 * the following `@font-face` styles are generated and returned:
	 * <code>
	 *
	 * @font-face{
	 *      font-family:"Source Serif Pro";
	 *      font-style:normal;
	 *      font-weight:200 900;
	 *      font-stretch:normal;
	 *      src:local("Source Serif Pro"), url('/assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2') format('woff2');
	 * }
	 * @font-face{
	 *      font-family:"Source Serif Pro";
	 *      font-style:italic;
	 *      font-weight:200 900;
	 *      font-stretch:normal;
	 *      src:local("Source Serif Pro"), url('/assets/fonts/source-serif-pro/SourceSerif4Variable-Italic.ttf.woff2') format('woff2');
	 * }
	 * </code>
	 *
	 * @since 6.0.0
	 *
	 * @return string The `@font-face` CSS.
	 */
	public function get_css() {
		$css   = '';
		$fonts = $this->get_fonts();

		foreach ( $fonts as $font ) {
			// Order the font's `src` items to optimize for browser support.
			$font = $this->order_src( $font );

			// Build the @font-face CSS for this webfont.
			$css .= '@font-face{' . $this->build_font_face_css( $font ) . '}';
		}

		return $css;
	}

	/**
	 * Order `src` items to optimize for browser support.
	 *
	 * @since 6.0.0
	 *
	 * @param array $webfont Webfont to process.
	 * @return array
	 */
	private function order_src( $webfont ) {
		if ( ! is_array( $webfont['src'] ) ) {
			$webfont['src'] = (array) $webfont['src'];
		}

		$src         = array();
		$src_ordered = array();

		foreach ( $webfont['src'] as $url ) {
			// Add data URIs first.
			if ( 0 === strpos( trim( $url ), 'data:' ) ) {
				$src_ordered[] = array(
					'url'    => $url,
					'format' => 'data',
				);
				continue;
			}
			$format         = pathinfo( $url, PATHINFO_EXTENSION );
			$src[ $format ] = $url;
		}

		// Add woff2.
		if ( ! empty( $src['woff2'] ) ) {
			$src_ordered[] = array(
				'url'    => $src['woff2'],
				'format' => 'woff2',
			);
		}

		// Add woff.
		if ( ! empty( $src['woff'] ) ) {
			$src_ordered[] = array(
				'url'    => $src['woff'],
				'format' => 'woff',
			);
		}

		// Add ttf.
		if ( ! empty( $src['ttf'] ) ) {
			$src_ordered[] = array(
				'url'    => $src['ttf'],
				'format' => 'truetype',
			);
		}

		// Add eot.
		if ( ! empty( $src['eot'] ) ) {
			$src_ordered[] = array(
				'url'    => $src['eot'],
				'format' => 'embedded-opentype',
			);
		}

		// Add otf.
		if ( ! empty( $src['otf'] ) ) {
			$src_ordered[] = array(
				'url'    => $src['otf'],
				'format' => 'opentype',
			);
		}
		$webfont['src'] = $src_ordered;

		return $webfont;
	}

	/**
	 * Builds the font-family's CSS.
	 *
	 * @since 6.0.0
	 *
	 * @param array $webfont Webfont to process.
	 * @return string This font-family's CSS.
	 */
	private function build_font_face_css( $webfont ) {
		$css = '';

		// Wrap font-family in quotes if it contains spaces.
		if (
			false !== strpos( $webfont['font-family'], ' ' ) &&
			false === strpos( $webfont['font-family'], '"' ) &&
			false === strpos( $webfont['font-family'], "'" )
		) {
			$webfont['font-family'] = '"' . $webfont['font-family'] . '"';
		}

		foreach ( $webfont as $key => $value ) {
			// Compile the "src" parameter.
			if ( 'src' === $key ) {
				$value = $this->compile_src( $webfont['font-family'], $value );
			}

			// If font-variation-settings is an array, convert it to a string.
			if ( 'font-variation-settings' === $key && is_array( $value ) ) {
				$value = $this->compile_variations( $value );
			}

			if ( ! empty( $value ) ) {
				$css .= "$key:$value;";
			}
		}

		/**
		 * Filters the font-family's CSS.
		 *
		 * @since 6.0.0
		 *
		 * @param string $css The font-family's CSS.
		 * @param array  $webfont The font-family's data.
		 *
		 * @return string The font-family's CSS.
		 */
		return apply_filters( 'font_face_css', $css, $webfont );
	}

	/**
	 * Compiles the `src` into valid CSS.
	 *
	 * @since 6.0.0
	 *
	 * @param string $font_family Font family.
	 * @param array  $value       Value to process.
	 * @return string The CSS.
	 */
	private function compile_src( $font_family, $value ) {
		$src = "local($font_family)";

		foreach ( $value as $item ) {

			if ( 0 === strpos( $item['url'], get_site_url() ) ) {
				$item['url'] = wp_make_link_relative( $item['url'] );
			}

			$src .= ( 'data' === $item['format'] )
				? ", url({$item['url']})"
				: ", url('{$item['url']}') format('{$item['format']}')";
		}
		return $src;
	}

	/**
	 * Compiles the font variation settings.
	 *
	 * @since 6.0.0
	 *
	 * @param array $font_variation_settings Array of font variation settings.
	 * @return string The CSS.
	 */
	private function compile_variations( array $font_variation_settings ) {
		$variations = '';

		foreach ( $font_variation_settings as $key => $value ) {
			$variations .= "$key $value";
		}

		return $variations;
	}
}
