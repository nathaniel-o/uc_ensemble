/**
 * Border styles the core picker does not offer (double, groove, ridge, inset, outset).
 * Writes the same style.border attribute the Border panel uses, including per-side values.
 */
(function (wp) {
	'use strict';

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var SelectControl = wp.components.SelectControl;
	var ToolsPanelItem = wp.components.__experimentalToolsPanelItem;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;

	var SIDES = ['top', 'right', 'bottom', 'left'];

	var STYLE_OPTIONS = [
		{ label: __('Not set', 'uc-theme-ui'), value: '' },
		{ label: __('Solid', 'uc-theme-ui'), value: 'solid' },
		{ label: __('Dashed', 'uc-theme-ui'), value: 'dashed' },
		{ label: __('Dotted', 'uc-theme-ui'), value: 'dotted' },
		{ label: __('Double', 'uc-theme-ui'), value: 'double' },
		{ label: __('Groove', 'uc-theme-ui'), value: 'groove' },
		{ label: __('Ridge', 'uc-theme-ui'), value: 'ridge' },
		{ label: __('Inset', 'uc-theme-ui'), value: 'inset' },
		{ label: __('Outset', 'uc-theme-ui'), value: 'outset' },
		{ label: __('None', 'uc-theme-ui'), value: 'none' }
	];

	function isSplitBorder(border) {
		return SIDES.some(function (side) {
			return border[side] && typeof border[side] === 'object';
		});
	}

	function currentStyle(border) {
		if (typeof border.style === 'string') {
			return border.style;
		}
		for (var i = 0; i < SIDES.length; i++) {
			var side = border[SIDES[i]];
			if (side && typeof side.style === 'string') {
				return side.style;
			}
		}
		return '';
	}

	function withStyle(border, next) {
		var updated = Object.assign({}, border);

		if (isSplitBorder(updated)) {
			SIDES.forEach(function (side) {
				var sideValue = updated[side] && typeof updated[side] === 'object'
					? Object.assign({}, updated[side])
					: {};
				if (next) {
					sideValue.style = next;
				} else {
					delete sideValue.style;
				}
				updated[side] = sideValue;
			});
		}

		if (next) {
			updated.style = next;
		} else {
			delete updated.style;
		}

		return updated;
	}

	var withExtraBorderStyles = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!props.isSelected || !wp.blocks.hasBlockSupport(props.name, '__experimentalBorder')) {
				return createElement(BlockEdit, props);
			}

			var style = props.attributes.style || {};
			var border = style.border || {};
			var value = currentStyle(border);

			function onChange(next) {
				props.setAttributes({
					style: Object.assign({}, style, {
						border: withStyle(border, next)
					})
				});
			}

			var control = createElement(SelectControl, {
				label: __('Border style', 'uc-theme-ui'),
				help: __('Double, groove, ridge, inset, and outset need a width of at least 3px. For any other CSS, use Additional CSS under Advanced — property and value only, no selector.', 'uc-theme-ui'),
				value: value,
				options: STYLE_OPTIONS,
				onChange: onChange,
				__nextHasNoMarginBottom: true
			});

			var panelChild = ToolsPanelItem
				? createElement(ToolsPanelItem, {
					hasValue: function () { return value !== ''; },
					label: __('Border style', 'uc-theme-ui'),
					onDeselect: function () { onChange(''); },
					isShownByDefault: true,
					panelId: props.clientId
				}, control)
				: control;

			return createElement(
				Fragment,
				null,
				createElement(BlockEdit, props),
				createElement(InspectorControls, { group: 'border' }, panelChild)
			);
		};
	}, 'withExtraBorderStyles');

	addFilter('editor.BlockEdit', 'uc/extra-border-styles', withExtraBorderStyles);
})(window.wp);
