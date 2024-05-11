/**
 * TheArchitect_ProductColorDetection
 *
 * @category TheArchitect
 * @package TheArchitect_ProductColorDetection
 */

define([
    'jquery',
    'mageUtils',
    'Magento_ConfigurableProduct/js/variations/steps/attributes_values'
], function ($, utils, Component) {
    'use strict';

    return Component.extend({
        refreshOptions: async function() {
            const {
                location: {
                    origin
                }
            } = window;

            const requestUrl = origin + '/rest/V1/getColorOptions';
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
                body: JSON.stringify({data: {detect_color_key: `${cookie}`}})
              });

            const responseData = await response.json();
            $('body').trigger('processStop');

            const oldOptions = this.options();
            const options =  JSON.parse(responseData);
            const newOptions = [];

            options.forEach((option) => {
                if (!oldOptions.some((oldOption) => oldOption.label === option.label)) {
                    newOptions.push(option);
                }
            });

            const mappedOptions = newOptions.map((option) => { return {...option, id: utils.uniqueid()}});
            const mergedOptions = [...oldOptions, ...mappedOptions];
            this.options(mergedOptions);
        },

        isColorAttribute: function() {
            return this.code === 'color';
        }
    })
});
