define([
    'jquery',
    'mage/utils/wrapper'
], function($, Wrapper) {
    "use strict";

    return function(origRules) {
        origRules.getObservableFields = Wrapper.wrap(
            origRules.getObservableFields,
            function(originalAction) {
                var fields = originalAction();
                fields.push('country_id');
                fields.push('region_id');
                fields.push('region_id_input');
                let uniqueFields = [...new Set(fields)];
                return uniqueFields;
            }
        );

        return origRules;
    };
});
