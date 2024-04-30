/**
 * TheArchitectAI_ProductColorDetection
 *
 * @category TheArchitectAI
 * @package TheArchitectAI_ProductColorDetection
 */

define([
    'uiComponent',
    'jquery',
    'underscore'
], function (Component, $, _) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function() {
            this._super();
            this.images = [];
            this.bindCustomEvent();
            this.addNewColorOptions();
        },

        capitalize: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        componentToHex: function(c) {
            var hex = c.toString(16);
            return hex.length == 1 ? "0" + hex : hex;
        },

        getRGBColorText: function (color) {
            return "rgb("+ color[0] +"," + color[1] + "," + color[2] + ")"
        },

        rgbToHex: function(r, g, b) {
            return "#" + this.componentToHex(r) + this.componentToHex(g) + this.componentToHex(b);
        },

        saveOption: async function(colorName, colorHex) {
            const {
                location: {
                    origin
                }
            } = window;

            const requestUrl = origin + this.addColorOptionUrl;
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
                body: JSON.stringify({data: {colorName: colorName, colorHex: colorHex,  detect_color_key: `${cookie}`}})
              });

              const responseData = await response.json();
              $('body').trigger('processStop');

              return JSON.parse(responseData);
        },

        addTopColor: function(colorValue, colorName, top_colors_closest_color) {
            var html = '';
            const hexValue = this.rgbToHex(colorValue[0], colorValue[1], colorValue[2]);
            const colorNameLabel = $.mage.__('Color Name');
            const color = $.mage.__('Color');
            const colorNameComment = $.mage.__('Suggested color name.');
            const addColorOption = $.mage.__('Add option');
            const closestColor = $.mage.__('Closest Color');
            const closestColorComment = $.mage.__('Closest saved color by numbers.');
            const closestColorValue = top_colors_closest_color[0] ?? '';

            html += '<div class="admin__field DetectButton-ColorWrapper">';
            html += `<label for="DetectButton-ColorValue" class="admin__field-label"><span>${color}</span></label>`;
            html += `<input id="DetectButton-ColorValue" style="background-color: ${this.getRGBColorText(colorValue)};" class="admin__control-text DetectButton-ColorValue" type="text" readonly />`;
            html += `<label for="DetectButton-ColorName" class="admin__field-label"><span>${colorNameLabel}</span></label>`;
            html += '<div class="DetectButton-InputWrapper">';
            html += `<input id="DetectButton-ColorName" class="admin__control-text DetectButton-ColorName" type="text" value="${colorName}" />`;
            html += `<div class="admin__field-note">${colorNameComment}</div>`;
            html += '</div>';
            html += '<label for="DetectButton-ColorHex" class="admin__field-label"><span>Hex</span></label>';
            html += `<input id="DetectButton-ColorHex" class="admin__control-text DetectButton-ColorHex" type="text" value="${hexValue}" readonly />`;
            html += `<button class="DetectButton-AddOptionButton action-primary">${addColorOption}</button>`;
            html += `<label for="DetectButton-ClosestColor" class="admin__field-label"><span>${closestColor}</span></label>`
            html += '<div class="DetectButton-InputWrapper">';
            html += `<input id="DetectButton-ClosestColor" class="admin__control-text DetectButton-ClosestColor" type="text" readonly value="${closestColorValue}"/>`;
            html += `<div class="admin__field-note">${closestColorComment}</div>`;
            html += '</div>';
            html += '</div>';

            return html;
        },

        sendPostRequest: async function(image, isRemoveSkin) {
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
                body: JSON.stringify({data: {image: image, detect_color_key: `${cookie}`, isRemoveSkin: isRemoveSkin}})
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

                $(this).parents().children(".gallery").find('.product-image').each(function() {
                    const imgSrc = $(this).attr("src");
                    if ($(this).parent().is(":visible")) {
                        imagesSources.push(imgSrc);
                    }
                });

                self.images = imagesSources;

                const isRemoveSkin = $(this).siblings(".DetectButton-RemoveSkin").find('.DetectButton-RemoveSkinCheckBox')[0].checked;

                const {
                    object_dominant_color_rgb,
                    approximate_color_name,
                    object_dominant_color_hex,
                    top_colors,
                    object_closest_saved_color,
                    top_colors_closest_colors
                } = await self.sendPostRequest(self.images[0], isRemoveSkin);

                $(this).siblings(".DetectButton-Colors").find('#DetectButton-ColorValue').css({ "background-color": self.getRGBColorText(object_dominant_color_rgb) });
                $(this).siblings(".DetectButton-Colors").find('#DetectButton-ColorName').val(approximate_color_name);
                $(this).siblings(".DetectButton-Colors").find('#DetectButton-ColorHex').val(object_dominant_color_hex);
                $(this).siblings(".DetectButton-Colors").find('#DetectButton-ClosestColor').val(object_closest_saved_color[0]);
                $(this).siblings(".DetectButton-Colors").find('.DetectButton-TopColors').html('');

                top_colors.forEach((color, index) => {
                    $(this).siblings(".DetectButton-Colors").find('.DetectButton-TopColors')
                        .append(self.addTopColor(color[1], color[2], top_colors_closest_colors[index]));
                });
            });
        },

        addNewColorOptions: async function() {
            self = this;

            $(document).on('click', '.DetectButton-AddOptionButton', async function() {
                const colorName = $(this).parent().find('#DetectButton-ColorName').val();
                const colorHex = $(this).parent().find('#DetectButton-ColorHex').val();
                $(this).parents().children('.DetectButton-Message')
                    .find('.DetectButton-SuccessMessage, .DetectButton-ErrorMessage, .DetectButton-BulkSuccessMessage')
                    .css({"display": "none"});

                if (colorName && colorHex) {
                    const {
                        is_added
                    } = await self.saveOption(self.capitalize(colorName), colorHex);

                    if(is_added && !$('.steps-wizard-title').html()) {
                        $(this).parents().children('.DetectButton-Message').find('.DetectButton-SuccessMessage').css({"display": "block"});
                    } else if (is_added && $('.steps-wizard-title').html()) {
                        $(this).parents().children('.DetectButton-Message').find('.DetectButton-BulkSuccessMessage').css({"display": "block"});
                    } else {
                        $(this).parents().children('.DetectButton-Message').find('.DetectButton-ErrorMessage').css({"display": "block"});
                    }
                }
            });
        },
    });
});
