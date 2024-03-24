/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Catalog/js/product-gallery',
    'jquery/file-uploader',
    'mage/translate'
], function (Component, $, ko, _, Collapsible, mageTemplate, alert) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function() {
            this._super();
            this.images = [];
            this.bindCustomEvent();
        },

        bindCustomEvent: function () {
            var self = this;
            // Bind the click event to your custom function
            $(document).on('click', '.DetectButton', function() {
                const imagesSources = [];
                self.images = [];

                $(this).parent().siblings(".gallery").find('.product-image').each(function() {
                    const imgSrc = $(this).attr("src");
                    if ($(this).parent().is(":visible")) {
                        imagesSources.push(imgSrc);
                    }
                });

                self.images = imagesSources;
                $('body').trigger('processStart');
                $(this).siblings(".DetectButton-ColorWrapper").find('#DetectButton-ColorValue').css({ "background-color": "#FFF" });
                console.log(self.images);
                $('body').trigger('processStop');
            });
        },
    });
});

// $.ajax({
//     ...// ajax setting,
//     showLoader: true, // enable loader
//     context: jqueryElementorSelector // element that will be coverer by loader, default body, optional
// }).than(...)