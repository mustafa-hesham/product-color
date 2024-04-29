/**
 * TheArchitectAI_ProductColorDetection
 *
 * @category TheArchitectAI
 * @package TheArchitectAI_ProductColorDetection
 */

define([
    'jquery',
    'mageUtils',
    'Magento_ConfigurableProduct/js/variations/steps/attributes_values'
], function ($, utils, Component) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
        },

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

            const options =  JSON.parse(responseData);
            const mappedOptions = options.map((option) => { return {...option, id: utils.uniqueid()}});
            this.options(mappedOptions);
        },
    })
});
