export default (editor) => {
  editor.Commands.add('promo-banner:open-edit', {
    run(edtr, sender, opts = {}) {
      const target = opts.target || edtr.getSelected();
      if (!target) return;
      const current = target.getAttributes()['data-cta-href'] || '';
      // eslint-disable-next-line no-alert
      const newHref = window.prompt('CTA Link URL:', current);
      if (newHref === null) return;
      target.setAttributes({ 'data-cta-href': newHref });
      target.trigger('rerender');
    },
  });
};
