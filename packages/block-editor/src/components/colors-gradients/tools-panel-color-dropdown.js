/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	ColorIndicator,
	Dropdown,
	FlexItem,
	__experimentalHStack as HStack,
	__experimentalItem as Item,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import ColorGradientControl from './control';
import useMultipleOriginColorsAndGradients from './use-multiple-origin-colors-and-gradients';

export default function ToolsPanelColorDropdown( { settings, ...otherProps } ) {
	const colorGradientSettings = useMultipleOriginColorsAndGradients();
	const controlSettings = {
		...colorGradientSettings,
		clearable: false,
		label: settings.label,
		onColorChange: settings.onColorChange,
		onGradientChange: settings.onGradientChange,
		colorValue: settings.colorValue,
		gradientValue: settings.gradientValue,
	};

	return (
		<ToolsPanelItem
			hasValue={ settings.hasValue }
			label={ settings.label }
			onDeselect={ settings.onDeselect }
			isShownByDefault={ settings.isShownByDefault }
			resetAllFilter={ settings.resetAllFilter }
			{ ...otherProps }
			as={ Dropdown }
			className="block-editor-panel-color-gradient-settings__dropdown block-editor-tools-panel-color-dropdown"
			contentClassName="block-editor-panel-color-gradient-settings__dropdown-content"
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Item
					onClick={ onToggle }
					className={ classnames(
						'block-editor-panel-color-gradient-settings__item',
						{ 'is-open': isOpen }
					) }
				>
					<HStack justify="flex-start">
						<ColorIndicator
							className="block-editor-panel-color-gradient-settings__color-indicator"
							colorValue={
								settings.gradientValue ?? settings.colorValue
							}
						/>
						<FlexItem>{ settings.label }</FlexItem>
					</HStack>
				</Item>
			) }
			renderContent={ () => (
				<ColorGradientControl
					showTitle={ false }
					__experimentalHasMultipleOrigins
					__experimentalIsRenderedInSidebar
					enableAlpha
					{ ...controlSettings }
				/>
			) }
		/>
	);
}
