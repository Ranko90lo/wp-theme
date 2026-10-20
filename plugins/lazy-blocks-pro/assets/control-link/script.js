/**
 * Styles
 */
import './styles.scss';

/**
 * External dependencies
 */
import shorthash from 'shorthash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import {
	ToggleControl,
	SelectControl,
	TextControl,
	CheckboxControl,
	PanelBody,
	Button,
	Modal,
} from '@wordpress/components';
import { useState, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import {
	LinkControl,
	__experimentalLinkControl,
} from '@wordpress/block-editor';

// Stabilized in WP 6.8, fallback to experimental for WP < 6.8.
const ResolvedLinkControl = LinkControl || __experimentalLinkControl;

/**
 * Get default link value.
 *
 * @return {Object} Default link object.
 */
function getDefaultValue() {
	return {
		url: '',
		title: '',
		opensInNewTab: false,
		nofollow: false,
		cssClasses: '',
	};
}

/**
 * Build suggestions query from control data.
 *
 * @param {Object} data Control data.
 * @return {Object|null} Query object or null.
 */
function getSuggestionsQuery(data) {
	if (!data.link_suggestions || data.link_suggestions === 'false') {
		return null;
	}

	if (data.link_suggestions === 'attachment') {
		return { type: 'attachment' };
	}

	return {
		type: 'post',
		subtype: data.link_suggestions,
	};
}

/**
 * CSSClassesSettingComponent
 *
 * Presents a toggleable text input for editing link CSS classes. The input
 * is shown when the toggle is enabled or when there is already a value. When
 * toggled off and a value exists, it resets the value to an empty string.
 *
 * @param {Object}   props          - Component props.
 * @param {Object}   props.setting  - Setting configuration object.
 * @param {Object}   props.value    - Current link value object.
 * @param {Function} props.onChange - Callback when value changes.
 */
const CSSClassesSettingComponent = ({ setting, value, onChange }) => {
	const hasValue = value ? value?.cssClasses?.length > 0 : false;
	const [isSettingActive, setIsSettingActive] = useState(hasValue);

	// Sanitize user input: replace commas with spaces, collapse repeated spaces, and trim
	const handleSettingChange = (newValue) => {
		const sanitizedValue =
			typeof newValue === 'string'
				? newValue.replace(/,/g, ' ').replace(/\s+/g, ' ').trim()
				: newValue;
		onChange({
			...value,
			[setting.id]: sanitizedValue,
		});
	};

	const handleCheckboxChange = () => {
		if (isSettingActive) {
			if (hasValue) {
				// Reset the value when hiding the input and a value exists.
				handleSettingChange('');
			}
			setIsSettingActive(false);
		} else {
			setIsSettingActive(true);
		}
	};

	return (
		<div>
			<CheckboxControl
				__nextHasNoMarginBottom
				label={setting.title}
				onChange={handleCheckboxChange}
				checked={isSettingActive || hasValue}
				aria-expanded={isSettingActive}
			/>
			{isSettingActive && (
				<TextControl
					label={__('CSS classes')}
					defaultValue={value?.cssClasses}
					onChange={handleSettingChange}
					help={__('Separate multiple classes with spaces.')}
					className="lzb-pro-control-link-css-classes-input"
					__next40pxDefaultSize
				/>
			)}
		</div>
	);
};

/**
 * Build link settings array based on control configuration.
 *
 * @param {Object} data Control data.
 * @return {Array} Array of setting objects for LinkControl.
 */
function buildLinkSettings(data) {
	const settings = [];

	// Open in new tab setting (built-in to LinkControl).
	if (data.show_target_field !== 'false') {
		settings.push({
			id: 'opensInNewTab',
			title: __('Open in new tab', 'lazy-blocks'),
		});
	}

	// Nofollow setting.
	if (data.show_nofollow_field !== 'false') {
		settings.push({
			id: 'nofollow',
			title: __('Mark as nofollow', 'lazy-blocks'),
		});
	}

	// CSS Classes with custom render.
	if (data.show_classes_field !== 'false') {
		settings.push({
			id: 'cssClasses',
			title: __('Additional CSS class(es)', 'lazy-blocks'),
			render: (setting, value, onChange) => {
				return (
					<CSSClassesSettingComponent
						setting={setting}
						value={value}
						onChange={onChange}
					/>
				);
			},
		});
	}

	return settings;
}

/**
 * Inspector/popup control render using LinkControl with settings prop.
 * This is similar to WordPress Button block and Link format.
 *
 * @param {Object} props - component props.
 */
function ControlEditorInspector(props) {
	const { onChange, getValue, data } = props;

	const [linkKey, setLinkKey] = useState(shorthash.unique(`${new Date()}`));

	const { BaseControl, useBlockControlProps } = useSelect((select) => {
		const components = select('lazy-blocks/components').get();
		const hooks = select('lazy-blocks/hooks').get();

		return {
			BaseControl: components.BaseControl,
			useBlockControlProps: hooks.useBlockControlProps,
		};
	});

	const value = getValue() || getDefaultValue();

	const query = getSuggestionsQuery(data);
	const linkSettings = buildLinkSettings(data);

	// Prepare LinkControl value.
	const linkValue = {
		url: value.url || '',
		title: value.title || '',
		opensInNewTab: value.opensInNewTab || false,
		nofollow: value.nofollow || false,
		cssClasses: value.cssClasses || '',
	};

	const handleChange = (newLink) => {
		onChange({
			...value,
			url: newLink.url || '',
			title: newLink.title || '',
			opensInNewTab: newLink.opensInNewTab || false,
			nofollow: newLink.nofollow || false,
			cssClasses: newLink.cssClasses || '',
		});
	};

	const handleRemove = () => {
		onChange(getDefaultValue());
		setLinkKey(shorthash.unique(`${new Date()}`));
	};

	return (
		<BaseControl {...useBlockControlProps(props)}>
			<div className="lzb-pro-control-link lzb-pro-control-link-inspector">
				<ResolvedLinkControl
					key={linkKey}
					className="wp-block-navigation-link__inline-link-input"
					hasTextControl={data.show_title_field !== 'false'}
					hasRichPreviews={data.rich_preview === 'true'}
					showSuggestions={data.link_suggestions !== 'false'}
					showInitialSuggestions={
						data.link_suggestions !== 'false' &&
						data.show_initial_link_suggestions === 'true'
					}
					suggestionsQuery={query}
					settings={linkSettings}
					value={linkValue}
					onChange={handleChange}
					onRemove={handleRemove}
				/>
			</div>
		</BaseControl>
	);
}

/**
 * Content placement control render - URL field with modal.
 * Similar to Free plugin URL control behavior.
 *
 * @param {Object} props - component props.
 */
function ControlEditorContent(props) {
	const { getValue, data } = props;

	const [isOpen, setIsOpen] = useState(false);

	const { BaseControl, useBlockControlProps } = useSelect((select) => {
		const components = select('lazy-blocks/components').get();
		const hooks = select('lazy-blocks/hooks').get();

		return {
			BaseControl: components.BaseControl,
			useBlockControlProps: hooks.useBlockControlProps,
		};
	});

	// Import Modal from Lazy Blocks core.
	const { LzbModal } = useSelect((select) => {
		const components = select('lazy-blocks/components').get();

		return {
			LzbModal: components.Modal,
		};
	});

	const value = getValue() || getDefaultValue();

	// Fallback to WordPress Modal if core Modal is not available.
	const ModalComponent = LzbModal || Modal;

	return (
		<>
			<div className="lzb-pro-control-link-in-content">
				<Button
					className="lzb-pro-control-link-in-content-button"
					aria-label={__('Edit Link', 'lazy-blocks')}
					onClick={(e) => {
						e.stopPropagation();
						setIsOpen(true);
					}}
					aria-expanded={isOpen ? 'true' : 'false'}
				/>
				<BaseControl {...useBlockControlProps(props)}>
					<TextControl
						value={value.url || ''}
						placeholder={
							data.placeholder || __('Add Link', 'lazy-blocks')
						}
						readOnly
						tabIndex={-1}
					/>
				</BaseControl>
			</div>
			{isOpen && (
				<ModalComponent
					title={__('Link Settings', 'lazy-blocks')}
					onRequestClose={() => setIsOpen(false)}
					focusOnMount="firstContentElement"
					size="medium"
					className="lzb-pro-control-link-modal"
				>
					{props.render}
				</ModalComponent>
			)}
		</>
	);
}

/**
 * Control render in editor - inspector/popup placement.
 */
addFilter('lzb.editor.control.link.render', 'lzb.editor', (render, props) => {
	return <ControlEditorInspector {...props} />;
});

/**
 * Content placement render - wraps control in modal.
 * Uses higher priority (50) to override the base render.
 */
addFilter(
	'lzb.editor.control.link.render',
	'lzb.editor.content',
	(render, props) => {
		if (props.placement !== 'content') {
			return render;
		}

		return <ControlEditorContent {...props} render={render} />;
	},
	50
);

/**
 * Control settings render in block builder.
 *
 * @param {Object} props
 */
function BlockBuilderControlSettings(props) {
	const { updateData, data } = props;
	const {
		link_suggestions: linkSuggestions = '',
		show_initial_link_suggestions: showInitialLinkSuggestions = 'false',
		rich_preview: richPreview = 'false',
		show_title_field: showTitleField = 'true',
		show_target_field: showTargetField = 'true',
		show_nofollow_field: showNofollowField = 'true',
		show_classes_field: showClassesField = 'true',
	} = data;

	const { BaseControl, allPostTypes } = useSelect((select) => {
		const components = select('lazy-blocks/components').get();

		return {
			BaseControl: components.BaseControl,
			allPostTypes: select('core').getPostTypes({ per_page: -1 }),
		};
	});

	const postTypes = useMemo(() => {
		if (!allPostTypes) {
			return [];
		}

		return allPostTypes
			.filter((postTypeData) => postTypeData.viewable)
			.map((postTypeData) => {
				let label = postTypeData.name;
				let thereIsDuplicate = 0;

				// Check if there are duplicate labels.
				allPostTypes.forEach((postTypeDataCheck) => {
					if (label === postTypeDataCheck.name) {
						thereIsDuplicate += 1;
					}
				});

				if (thereIsDuplicate > 1) {
					label = `${label} (${postTypeData.slug})`;
				}

				return {
					value: postTypeData.slug,
					label,
				};
			});
	}, [allPostTypes]);

	return (
		<>
			<PanelBody title={__('Link Fields', 'lazy-blocks')}>
				<BaseControl
					id="lazyblock-pro-control-link-show-title"
					label={__('Show Title Field', 'lazy-blocks')}
					help={__(
						'Allow users to set a custom title/text for the link',
						'lazy-blocks'
					)}
				>
					<ToggleControl
						id="lazyblock-pro-control-link-show-title"
						label={__('Yes', 'lazy-blocks')}
						checked={showTitleField === 'true'}
						onChange={(value) =>
							updateData({
								show_title_field: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
				<BaseControl
					id="lazyblock-pro-control-link-show-target"
					label={__('Show "Open in new tab" Toggle', 'lazy-blocks')}
					help={__(
						'Allow users to toggle opening link in new tab',
						'lazy-blocks'
					)}
				>
					<ToggleControl
						id="lazyblock-pro-control-link-show-target"
						label={__('Yes', 'lazy-blocks')}
						checked={showTargetField === 'true'}
						onChange={(value) =>
							updateData({
								show_target_field: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
				<BaseControl
					id="lazyblock-pro-control-link-show-nofollow"
					label={__('Show "Mark as nofollow" Toggle', 'lazy-blocks')}
					help={__(
						'Allow users to add nofollow attribute to the link',
						'lazy-blocks'
					)}
				>
					<ToggleControl
						id="lazyblock-pro-control-link-show-nofollow"
						label={__('Yes', 'lazy-blocks')}
						checked={showNofollowField === 'true'}
						onChange={(value) =>
							updateData({
								show_nofollow_field: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
				<BaseControl
					id="lazyblock-pro-control-link-show-classes"
					label={__('Show CSS Classes Field', 'lazy-blocks')}
					help={__(
						'Allow users to add custom CSS classes to the link',
						'lazy-blocks'
					)}
				>
					<ToggleControl
						id="lazyblock-pro-control-link-show-classes"
						label={__('Yes', 'lazy-blocks')}
						checked={showClassesField === 'true'}
						onChange={(value) =>
							updateData({
								show_classes_field: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
			</PanelBody>
			<PanelBody title={__('Link Suggestions', 'lazy-blocks')}>
				<BaseControl
					help={__(
						'Display suggestions when typing the URL',
						'lazy-blocks'
					)}
				>
					<SelectControl
						label={__('Link Suggestions', 'lazy-blocks')}
						value={linkSuggestions}
						onChange={(value) =>
							updateData({ link_suggestions: value })
						}
						options={[
							{
								label: __('Automatic', 'lazy-blocks'),
								value: '',
							},
							{
								label: __('Disable suggestions', 'lazy-blocks'),
								value: 'false',
							},
							...postTypes,
						]}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</BaseControl>
				{linkSuggestions !== 'false' ? (
					<BaseControl
						id="lazyblock-pro-control-link-show-suggestions"
						label={__('Show Initial Suggestions', 'lazy-blocks')}
						help={__(
							'Whether to present initial suggestions immediately',
							'lazy-blocks'
						)}
					>
						<ToggleControl
							id="lazyblock-pro-control-link-show-suggestions"
							label={__('Yes', 'lazy-blocks')}
							checked={showInitialLinkSuggestions === 'true'}
							onChange={(value) =>
								updateData({
									show_initial_link_suggestions: value
										? 'true'
										: 'false',
								})
							}
							__nextHasNoMarginBottom
						/>
					</BaseControl>
				) : null}
				<BaseControl
					id="lazyblock-pro-control-link-rich-preview"
					label={__('Rich Preview', 'lazy-blocks')}
					help={__(
						'Display rich link preview with site title and image',
						'lazy-blocks'
					)}
				>
					<ToggleControl
						id="lazyblock-pro-control-link-rich-preview"
						label={__('Yes', 'lazy-blocks')}
						checked={richPreview === 'true'}
						onChange={(value) =>
							updateData({
								rich_preview: value ? 'true' : 'false',
							})
						}
						__nextHasNoMarginBottom
					/>
				</BaseControl>
			</PanelBody>
		</>
	);
}

addFilter(
	'lzb.constructor.control.link.settings',
	'lzb.constructor',
	(render, props) => {
		return <BlockBuilderControlSettings {...props} />;
	}
);

/**
 * Required check.
 * Just check for empty URL value.
 *
 * @param {Object} validationData
 * @param {Object} value
 *
 * @return {Object} validation data.
 */
function validate(validationData, value) {
	if (!value || !value.url) {
		return { valid: false };
	}

	return validationData;
}
addFilter('lzb.editor.control.link.validate', 'lzb.editor', validate);
