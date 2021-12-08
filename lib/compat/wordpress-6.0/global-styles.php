<?php
/**
 * Bootstraps Global Styles.
 *
 * @package gutenberg
 */

/**
 * Add missing fonts data to the global styles.
 *
 * @param array $data The global styles.
 *
 * @return array The global styles with missing fonts data.
 */
function gutenberg_add_registered_webfonts_to_theme_json( $data ) {
	$font_families_registered = wp_webfonts()->get_fonts();
	$font_families_from_theme = array();
	if ( ! empty( $data['settings'] ) && ! empty( $data['settings']['typography'] ) && ! empty( $data['settings']['typography']['fontFamilies'] ) ) {
		$font_families_from_theme = $data['settings']['typography']['fontFamilies'];
	}

	/**
	 * Helper to get an array of the font-families.
	 *
	 * @param array $families_data The font-families data.
	 *
	 * @return array The font-families array.
	 */
	$get_families = function( $families_data ) {
		$families = array();
		foreach ( $families_data as $family ) {
			if ( isset( $family['font-family'] ) ) {
				$families[] = $family['font-family'];
			} elseif ( isset( $family['fontFamily'] ) ) {
				$families[] = $family['fontFamily'];
			}
		}

		// Micro-optimization: Use array_flip( array_flip( $array ) )
		// instead of array_unique( $array ) because it's faster.
		// The result is the same.
		return array_flip( array_flip( $families ) );
	};

	// Diff the arrays to find the missing fonts.
	$to_add = array_diff(
		$get_families( $font_families_registered ),
		$get_families( $font_families_from_theme )
	);

	// Bail out early if there are no missing fonts.
	if ( empty( $to_add ) ) {
		return $data;
	}

	// Make sure the path to settings.typography.fontFamilies.theme exists
	// before adding missing fonts.
	if ( empty( $data['settings'] ) ) {
		$data['settings'] = array();
	}
	if ( empty( $data['settings']['typography'] ) ) {
		$data['settings']['typography'] = array();
	}
	if ( empty( $data['settings']['typography']['fontFamilies'] ) ) {
		$data['settings']['typography']['fontFamilies'] = array();
	}

	// Add missing fonts.
	foreach ( $to_add as $family ) {
		$data['settings']['typography']['fontFamilies'][] = array(
			'fontFamily' => false !== strpos( $family, ' ' ) ? "'{$family}'" : $family,
			'name'       => $family,
			'slug'       => sanitize_title( $family ),
		);
	}

	return $data;
}
