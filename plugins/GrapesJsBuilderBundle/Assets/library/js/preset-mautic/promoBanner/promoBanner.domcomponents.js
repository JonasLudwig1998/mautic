import ContentService from '../content.service';

const PROMO_HEADLINE_TYPE = 'promo-headline';

export default class PromoBannerDomComponents {
  static addPromoHeadlineType(editor) {
    if (!ContentService.isMjmlMode(editor)) return;

    editor.DomComponents.addType(PROMO_HEADLINE_TYPE, {
      extend: 'mj-text',

      isComponent(el) {
        if (
          el.tagName?.toLowerCase() === 'mj-text' &&
          el.getAttribute?.('data-promo-role') === 'headline'
        ) {
          return { type: PROMO_HEADLINE_TYPE };
        }
        return false;
      },

      model: {
        defaults: {
          name: 'Promo Headline',
          droppable: false,
          stylable: [
            'font-size',
            'font-weight',
            'font-family',
            'color',
            'text-transform',
            'letter-spacing',
            'line-height',
            'align',
            'padding',
            'padding-top',
            'padding-left',
            'padding-right',
            'padding-bottom',
            'container-background-color',
          ],
        },
      },
    });
  }
}
