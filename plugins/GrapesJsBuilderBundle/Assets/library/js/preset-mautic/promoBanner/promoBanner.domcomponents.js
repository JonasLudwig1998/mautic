import ContentService from '../content.service';

const PROMO_TYPE = 'promo-banner';
const PROMO_SLOT = 'promo-banner';
const TOOLBAR_ID = 'toolbar-promo-banner';
const OPEN_COMMAND = 'promo-banner:open-edit';

export default class PromoBannerDomComponents {
  static addPromoBannerType(editor) {
    if (!ContentService.isMjmlMode(editor)) return;

    editor.DomComponents.addType(PROMO_TYPE, {
      extend: 'mj-section',

      isComponent(el) {
        if (el.tagName?.toLowerCase() === 'mj-section' &&
            el.getAttribute?.('data-slot') === PROMO_SLOT) {
          return { type: PROMO_TYPE };
        }
        return false;
      },

      model: {
        defaults: {
          droppable: false,
          attributes: { 'data-slot': PROMO_SLOT },
          components: `
            <mj-column>
              <mj-text css-class="promo-headline" data-slot="promo-headline">Your Headline</mj-text>
              <mj-text css-class="promo-subtext" data-slot="promo-subtext">Your subtext.</mj-text>
              <mj-button css-class="promo-cta" href="https://example.com">Shop Now</mj-button>
            </mj-column>
          `,
        },

        init() {
          const toolbar = this.get('toolbar') || [];
          if (!toolbar.some((item) => item.id === TOOLBAR_ID)) {
            toolbar.unshift({
              id: TOOLBAR_ID,
              command: OPEN_COMMAND,
              label: `<svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/></svg>`,
              attributes: { title: 'Edit CTA Link' },
            });
          }
          this.set('toolbar', toolbar);
        },
      },

      view: {
        onRender({ model }) {
          const href = model.getAttributes()['data-cta-href'] || 'https://example.com';
          const btn = this.el.querySelector('[data-slot="promo-cta"]');
          if (btn) btn.setAttribute('title', `Link: ${href}`);
        },
      },
    });
  }
}
