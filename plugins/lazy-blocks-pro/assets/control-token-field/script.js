/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import {
	PanelBody,
	BaseControl,
	FormTokenField,
	ToggleControl,
	RadioControl,
} from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Control render in editor.
 *
 * @param {Object} props
 */
function ControlEditor(props) {
	const { onChange, getValue, data } = props;

	const { Base, useBlockControlProps } = useSelect((select) => {
		const components = select('lazy-blocks/components').get();
		const hooks = select('lazy-blocks/hooks').get();

		return {
			Base: components.BaseControl,
			useBlockControlProps: hooks.useBlockControlProps,
		};
	});

	// Memoize choices to prevent unnecessary re-renders.
	const choices = useMemo(() => data.choices || [], [data.choices]);

	// Prepare suggestions from choices.
	const suggestions = useMemo(
		() => choices.map((choice) => choice.value),
		[choices]
	);

	// Get the current value as array.
	const tokenValue = useMemo(() => {
		const value = getValue();
		if (data.multiple === 'true') {
			return Array.isArray(value) ? value : [];
		}
		return value ? [value] : [];
	}, [getValue, data.multiple]);

	// Display transform to show labels instead of values.
	const displayTransform = (token) => {
		const choice = choices.find((c) => c.value === token);
		return choice ? choice.label : token;
	};

	// Validate input - only allow predefined choices or custom values if allowed.
	const validateInput = (token) => {
		if (data.allow_custom === 'true') {
			return true;
		}
		// Check if token matches any choice value or label.
		return choices.some((c) => c.value === token || c.label === token);
	};

	// Handle token change.
	const handleChange = (tokens) => {
		// Convert labels back to values if needed.
		const normalizedTokens = tokens.map((token) => {
			// Check if token is a label and convert to value.
			const choice = choices.find((c) => c.label === token);
			return choice ? choice.value : token;
		});

		if (data.multiple === 'true') {
			onChange(normalizedTokens);
		} else {
			// For single selection, only keep the last token.
			onChange(
				normalizedTokens.length > 0
					? normalizedTokens[normalizedTokens.length - 1]
					: ''
			);
		}
	};

	return (
		<Base {...useBlockControlProps(props)}>
			<FormTokenField
				label={false}
				suggestions={suggestions}
				value={tokenValue}
				onChange={handleChange}
				displayTransform={displayTransform}
				__experimentalExpandOnFocus
				__experimentalShowHowTo={false}
				__experimentalValidateInput={validateInput}
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>
		</Base>
	);
}

addFilter(
	'lzb.editor.control.token_field.render',
	'lzb.editor',
	(render, props) => <ControlEditor {...props} />
);

/**
 * Required check.
 *
 * @param {Object}       validationData
 * @param {Array|string} value
 * @param {Object}       data
 *
 * @return {Object} validation data.
 */
function validate(validationData, value, data) {
	if (!value || (data.multiple === 'true' && !value.length)) {
		return {
			valid: false,
			message: __('Please add at least one item.', 'lazy-blocks'),
		};
	}

	return validationData;
}

addFilter('lzb.editor.control.token_field.validate', 'lzb.editor', validate);

/**
 * Control settings render in block builder.
 *
 * @param {Object} props
 */
function BlockBuilderControlSettings(props) {
	const { updateData, data } = props;
	const { choices } = data;

	const { Choices } = useSelect((select) => {
		return select('lazy-blocks/components').get();
	}, []);

	return (
		<>
			<PanelBody>
				<Choices
					value={choices}
					onChange={(val) => updateData({ choices: val })}
				/>
			</PanelBody>
			<PanelBody>
				<BaseControl
					id="lzb-pro-control-token-field-allow-custom"
					label={__('Allow Custom Values', 'lazy-blocks')}
					help={__(
						'Allows users to add custom values that are not in the predefined choices',
						'lazy-blocks'
					)}
					__nextHasNoMarginBottom
				>
					<ToggleControl
						id="lzb-pro-control-token-field-allow-custom"
						label={__('Yes', 'lazy-blocks')}
						checked={data.allow_custom === 'true'}
						onChange={(value) =>
							updateData({
								allow_custom: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
			</PanelBody>
			<PanelBody>
				<BaseControl
					id="lzb-pro-control-token-field-multiple"
					label={__('Multiple', 'lazy-blocks')}
					help={__(
						'Allows you to select multiple values',
						'lazy-blocks'
					)}
					__nextHasNoMarginBottom
				>
					<ToggleControl
						id="lzb-pro-control-token-field-multiple"
						label={__('Yes', 'lazy-blocks')}
						checked={data.multiple === 'true'}
						onChange={(value) =>
							updateData({
								multiple: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
			</PanelBody>
			<PanelBody>
				<BaseControl
					id="lzb-pro-control-token-field-output-format"
					label={__('Output Format', 'lazy-blocks')}
					help={__(
						'Allows you to change attribute output format',
						'lazy-blocks'
					)}
					__nextHasNoMarginBottom
				>
					<RadioControl
						options={[
							{
								label: __('Value', 'lazy-blocks'),
								value: '',
							},
							{
								label: __('Label', 'lazy-blocks'),
								value: 'label',
							},
							{
								label: __('Both (Array)', 'lazy-blocks'),
								value: 'array',
							},
						]}
						selected={data.output_format || ''}
						onChange={(value) =>
							updateData({ output_format: value })
						}
					/>
				</BaseControl>
			</PanelBody>
		</>
	);
}

addFilter(
	'lzb.constructor.control.token_field.settings',
	'lzb.constructor',
	(render, props) => <BlockBuilderControlSettings {...props} />
);
