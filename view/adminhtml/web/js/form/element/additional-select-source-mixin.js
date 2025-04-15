define(
    [
        'jquery',
        'underscore',
        'uiRegistry'
    ],
    function ($, _, reg) {
        'use strict';

        var mixin = _.extend({
            defaults: {
                useApi: 0,
                canUseApi: false,
                imports: {
                    toggleApi: '${$.ns}.${$.ns}.settings.use_api:value',
                }
            },
            setOptionsAdv: function (value) {
                var useApiSettings = reg.get('import_job_form.import_job_form.settings.use_api'),
                    isApi = 0;

                if (useApiSettings !== undefined) {
                    this.canUseApi = true;
                    isApi = useApiSettings.value();
                }

                if (this.sourceOptions == null) {
                    this.sourceOptions = this.options();
                }
                var options = this.sourceOptions;
                var prevValue = this.value();
                var newOptions = [];
                var self = this;
                _.each(options, function (element, index) {
                    if ((parseInt(isApi) !== parseInt(element.api) && self.canUseApi)) {
                        return;
                    }
                    if (element.depends === "" || $.inArray(value, element.depends) !== -1) {
                        newOptions.push(element);
                    }
                });
                this.options(newOptions);
            },

            toggleApi: function (value) {
                if (this.canUseApi) {
                    return;
                }

                this.useApi = parseInt(value);

                // Ensure value exists
                var typeFile = reg.get('import_job_form.import_job_form.source.type_file').value();

                // If you can get the current value for `type_file`, call `setOptionsAdv` logic here
                var currentTypeFileValue = typeFile || this.value();

                // Run the original setOptionsAdv logic here
                if (this.sourceOptions == null) {
                    this.sourceOptions = this.options();
                }

                var options = this.sourceOptions;
                var newOptions = [];

                _.each(options, function (element, index) {
                    if (parseInt(this.useApi) !== parseInt(element.api)) {
                        return;
                    }
                    if (element.depends === "" || $.inArray(currentTypeFileValue, element.depends) !== -1) {
                        newOptions.push(element);
                    }
                }, this);

                this.options(newOptions);
            }

        });
        return function (target) {
            return target.extend(mixin);
        };
    }
);
