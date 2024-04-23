/**
 * TheArchitectAI_ProductColorDetection
 *
 * @category TheArchitectAI
 * @package TheArchitectAI_ProductColorDetection
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

        getRGBColorText: function (color) {
            return "rgb("+ color[0] +"," + color[1] + "," + color[2] + ")"
        },

        sendPostRequest: async function(image) {
            const {
                location: {
                    origin
                }
            } = window;

            const requestUrl = origin + this.controllerUrl;
            const cookie = $.cookie("detect_color_key");

            $('body').trigger('processStart');
            const response = await fetch(requestUrl, {
                method: "POST",
                mode: "cors",
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({data: {image: image, detect_color_key: `${cookie}`}})
              });

              const responseData = await response.json();
              $('body').trigger('processStop');

              return JSON.parse(responseData);
        },

        bindCustomEvent: function () {
            var self = this;
            // Bind the click event to your custom function
            $(document).on('click', '.DetectButton', async function() {
                const imagesSources = [];
                self.images = [];

                $(this).parent().siblings(".gallery").find('.product-image').each(function() {
                    const imgSrc = $(this).attr("src");
                    if ($(this).parent().is(":visible")) {
                        imagesSources.push(imgSrc);
                    }
                });

                self.images = imagesSources;

                const {
                    object_dominant_color_rgb,
                    approximate_color_name,
                    object_dominant_color_hex,
                    top_colors
                } = await self.sendPostRequest(self.images[0]);

                $(this).siblings(".DetectButton-ColorWrapper").find('#DetectButton-ColorValue').css({ "background-color": self.getRGBColorText(object_dominant_color_rgb) });
                $(this).siblings(".DetectButton-ColorWrapper").find('#DetectButton-ColorName').val(approximate_color_name).css('textTransform', 'capitalize');
                $(this).siblings(".DetectButton-ColorWrapper").find('#DetectButton-ColorHex').val(object_dominant_color_hex);
                console.log(object_dominant_color_rgb, approximate_color_name, top_colors);
            });
        },
    });
});
