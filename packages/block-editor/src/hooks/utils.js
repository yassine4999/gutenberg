/**
 * External dependencies
 */
import {
	pickBy,
	isEmpty,
	isObject,
	identity,
	mapValues,
	forEach,
	get,
	set,
} from 'lodash';

/**
 * Removed falsy values from nested object.
 *
 * @param {*} object
 * @return {*} Object cleaned from falsy values
 */
export const cleanEmptyObject = ( object ) => {
	if ( ! isObject( object ) || Array.isArray( object ) ) {
		return object;
	}
	const cleanedNestedObjects = pickBy(
		mapValues( object, cleanEmptyObject ),
		identity
	);
	return isEmpty( cleanedNestedObjects ) ? undefined : cleanedNestedObjects;
};

export function transformStyles(
	activeSupports,
	migrationPaths,
	result,
	source
) {
	const firstBlockAttributes = source[ 0 ]?.attributes;
	forEach( activeSupports, ( isActive, support ) => {
		if ( isActive ) {
			migrationPaths[ support ].forEach( ( path ) => {
				const styleValue = get( firstBlockAttributes, path );
				if ( styleValue ) {
					set( result.attributes, path, styleValue );
				}
			} );
		}
	} );
	return result;
}
