/* Native dynamic blocks; commerce runs only on the published frontend. */
(function (blocks, element, editor, components, render, i18n) {
  const el = element.createElement;
  const __ = i18n.__;
  const ServerSideRender = render.default || render;
  [
    ['products', 'WireGum products', { perPage: { type: 'number', default: 4 }, paginate: { type: 'boolean', default: false } }],
    ['buy-box', 'WireGum buy box', { productId: { type: 'number', default: 0 } }],
    ['cart-link', 'WireGum cart', {}],
    ['shop-link', 'WireGum shop link', {}]
  ].forEach(function ([name, title, attributes]) {
    blocks.registerBlockType('wiregum/' + name, {
      apiVersion: 3, title, category: 'widgets', icon: 'cart', attributes,
      usesContext: ['postId', 'postType'], supports: { html: false, align: ['wide', 'full'] },
      edit: function (props) {
        const controls = [];
        if (name === 'products') {
          controls.push(el(components.RangeControl, { key: 'count', label: __('Number of products', 'wiregum-wordpress'), min: 1, max: 48, value: props.attributes.perPage, onChange: function (perPage) { props.setAttributes({ perPage }); } }));
          controls.push(el(components.ToggleControl, { key: 'pages', label: __('Pagination', 'wiregum-wordpress'), checked: props.attributes.paginate, onChange: function (paginate) { props.setAttributes({ paginate }); } }));
        }
        if (name === 'buy-box') {
          controls.push(el(components.TextControl, { key: 'product', type: 'number', min: 0, label: __('WordPress product post ID (0 = current product)', 'wiregum-wordpress'), value: props.attributes.productId, onChange: function (value) { props.setAttributes({ productId: Math.max(0, parseInt(value, 10) || 0) }); } }));
        }
        return el('div', editor.useBlockProps(),
          el(editor.InspectorControls, {}, el(components.PanelBody, { title }, controls)),
          el(components.Disabled, {}, el(ServerSideRender, { block: 'wiregum/' + name, attributes: props.attributes, urlQueryArgs: { post_id: props.context.postId || 0 } })),
          name === 'buy-box' ? el('p', {}, __('Preview purchasing on the published page. Set the public WireGum reference in the product sidebar.', 'wiregum-wordpress')) : null
        );
      },
      save: function () { return null; }
    });
  });
})(wp.blocks, wp.element, wp.blockEditor, wp.components, wp.serverSideRender, wp.i18n);
