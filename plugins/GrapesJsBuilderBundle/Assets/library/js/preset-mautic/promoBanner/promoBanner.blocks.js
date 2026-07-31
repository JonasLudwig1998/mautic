export default class PromoBannerBlocks {
  constructor(editor) {
    this.blockManager = editor.BlockManager;
  }

  addPromoBannerBlock() {
    if (this.blockManager.get('promo-banner')) return;

    this.blockManager.add('promo-banner', {
      label: 'Promo Banner',
      media: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40">
        <path fill="currentColor" d="M21 3H3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H3V5h18v14zM5 10h14v2H5zm0-3h14v2H5zm0 6h7v2H5z"/>
      </svg>`,
      content: `<mj-section data-slot="promo-banner">
        <mj-column>
          <mj-text css-class="promo-headline" data-slot="promo-headline">Your Headline</mj-text>
          <mj-text css-class="promo-subtext" data-slot="promo-subtext">Your subtext goes here.</mj-text>
          <mj-button css-class="promo-cta" href="https://example.com">Shop Now</mj-button>
        </mj-column>
      </mj-section>`,
    });
  }
}
