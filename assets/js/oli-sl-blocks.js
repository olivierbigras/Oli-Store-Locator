(function (blocks, element, i18n, blockEditor, components, serverSideRender) {
	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var ServerSideRender = serverSideRender;

	function editWithAttrs(label, attrs) {
		return function (props) {
			var controls = attrs.map(function (attr) {
				return el(TextControl, {
					key: attr.key,
					label: attr.label,
					value: props.attributes[attr.key] || '',
					onChange: function (value) {
						var next = {};
						next[attr.key] = value;
						props.setAttributes(next);
					}
				});
			});
			return el(
				element.Fragment,
				{},
				el(InspectorControls, {}, el(PanelBody, { title: label }, controls)),
				el(ServerSideRender, { block: props.name, attributes: props.attributes })
			);
		};
	}

	blocks.registerBlockType('oli-store-locator/store-locator', {
		apiVersion: 2,
		title: __('Oli Store Locator', 'oli-store-locator'),
		icon: 'location-alt',
		category: 'widgets',
		attributes: {
			radius: { type: 'string', default: '' },
			max_results: { type: 'string', default: '' },
			category: { type: 'string', default: '' },
			height: { type: 'string', default: '' }
		},
		edit: editWithAttrs(__('Locator settings', 'oli-store-locator'), [
			{ key: 'radius', label: __('Radius', 'oli-store-locator') },
			{ key: 'max_results', label: __('Max results', 'oli-store-locator') },
			{ key: 'category', label: __('Category ID', 'oli-store-locator') },
			{ key: 'height', label: __('Map height', 'oli-store-locator') }
		]),
		save: function () {
			return null;
		}
	});

	blocks.registerBlockType('oli-store-locator/store-address', {
		apiVersion: 2,
		title: __('Store Address', 'oli-store-locator'),
		icon: 'building',
		category: 'widgets',
		attributes: { id: { type: 'string', default: '' } },
		edit: editWithAttrs(__('Store address', 'oli-store-locator'), [{ key: 'id', label: __('Store ID', 'oli-store-locator') }]),
		save: function () {
			return null;
		}
	});

	blocks.registerBlockType('oli-store-locator/store-hours', {
		apiVersion: 2,
		title: __('Store Hours', 'oli-store-locator'),
		icon: 'clock',
		category: 'widgets',
		attributes: { id: { type: 'string', default: '' } },
		edit: editWithAttrs(__('Store hours', 'oli-store-locator'), [{ key: 'id', label: __('Store ID', 'oli-store-locator') }]),
		save: function () {
			return null;
		}
	});

	blocks.registerBlockType('oli-store-locator/store-map', {
		apiVersion: 2,
		title: __('Store Map', 'oli-store-locator'),
		icon: 'location',
		category: 'widgets',
		attributes: {
			id: { type: 'string', default: '' },
			height: { type: 'string', default: '400px' }
		},
		edit: editWithAttrs(__('Store map', 'oli-store-locator'), [
			{ key: 'id', label: __('Store ID', 'oli-store-locator') },
			{ key: 'height', label: __('Height', 'oli-store-locator') }
		]),
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender);
