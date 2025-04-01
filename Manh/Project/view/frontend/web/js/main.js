define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/lib/knockout/bindings/range',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery-ui',
    'jquery-ui-modules/slider'
], function ($) {
    $.widget('mage.mpProjects', {
        options: {
            modeControl: '[data-role="mode-switcher"]',
            limitControl: '[data-role="limiter"]',
            pageControl: '.project-pager-container .item a',
            mode: 'project_list_mode',
            limit: 'project_list_limit',
            modeDefault: 'grid',
            page: 'p',
            pageDefault: '1'
        },
        projectListIdentifier: '',
        selectors: {
        },
        _create: function () {
            this._bind(
                $(this.options.pageControl),
                this.options.page,
                this.options.pageDefault
            );
            this._bind(
                $(this.options.modeControl),
                this.options.mode,
                this.options.modeDefault
            );
            this._bind(
                $(this.options.limitControl, this.element),
                this.options.limit,
                this.options.limitDefault
            );
        },

        _bind: function (element, paramName, defaultValue) {
            if (element.is('select')) {
                element.on('change', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processLink, this));
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _processLink: function (event) {
            event.preventDefault();
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        /**
         * @private
         */
        getUrlParams: function () {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                params = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                params[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }

            return params;
        },

        /**
         * @returns {String}
         * @private
         */
        getCurrentLimit: function () {
            return this.getUrlParams()[this.options.limit] || this.options.limitDefault;
        },

        /**
         * @returns {String}
         * @private
         */
        getCurrentPage: function () {
            return this.getUrlParams()[this.options.page] || 1;
        },

        /**
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        changeUrl: function (paramName, paramValue, defaultValue) {
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                paramData = this.getUrlParams(),
                currentPage = this.getCurrentPage(),
                form, params, key, input, formKey, newPage;

            if (currentPage > 1 && paramName === this.options.mode) {
                delete paramData[this.options.page];
            }

            if (currentPage > 1 && paramName === this.options.limit) {
                newPage = Math.floor(this.getCurrentLimit() * (currentPage - 1) / paramValue) + 1;

                if (newPage > 1) {
                    paramData[this.options.page] = newPage;
                } else {
                    delete paramData[this.options.page];
                }
            }

            paramData[paramName] = paramValue;

            if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                delete paramData[paramName];
            }

            paramData = $.param(paramData);
            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        },
    });

    return $.mage.mpProjects;
});
