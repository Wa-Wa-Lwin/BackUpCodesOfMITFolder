define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^[0-9+]*$/.test(value)) {
          return /^(09|959|\+)([0-9]{7,15})$/i.test(value);
        } else {
          return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
        }
      },
      $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    )
  }
});



define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^[0-9+]*$/.test(value)) {
          return /^(09|959|\+)([0-9]{7,15})$/i.test(value);
        } else {
          return /^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9_\.\-]*[a-zA-Z0-9])@[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9]\.[a-zA-Z]{2,}$/.test(value);
        }
      },
      $.mage.__('Please enter a valid phone number (09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or a valid email address (Ex: johndoe@domain.com).')
    );
  };
});

//////////////////////////

define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^[0-9+]*$/.test(value)) {
          return /^(09|959|\+)([0-9]{7,15})$/i.test(value);
        } else {
          return utils.isEmptyNoTrim(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);
        }
      },
      $.mage.__('Please enter a valid phone number (09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or a valid email address (Ex: johndoe@domain.com).')
    );
  };
});

//////////////////////////
'validate-phone-number-custom': [
  function (value) {
      return utils.isEmptyNoTrim(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
  },
  $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
],
//////////////////////////

'validate-email': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        ],


define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^[0-9]*$|^[0-9+]*$/.test(value)) {
          return /^[0-9+]*$/.test(value);
        } else {
          return /^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9_\.\-]*[a-zA-Z0-9])@[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9]\.[a-zA-Z]{2,}$/.test(value);
        }
      },
      $.mage.__('Please enter a valid phone number (09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or a valid email address (Ex: johndoe@domain.com).')
    );
  };
});


////////////////////


define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^(09|959|\+)([0-9]{7,15})$/i.test(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value)) {
          return true;
        } else {
          return false;
        }
      },
      $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    )
  }
});



define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value) || /^(09|959|\+)([0-9]{7,15})$/i.test(value))
        {
          return true;
        } else {
          return false;
        }
      },
      $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    )
  }
});



define(['jquery'], function ($) {
  'use strict'; 
  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value) || /^(09|959|\+)([0-9]{7,15})$/i.test(value))
        {
          return true;
        } else {
          return false;
        }
      },
      $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    )
  }
});


'validate-email': [
  function (value) {
      return utils.isEmptyNoTrim(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
  },
  $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
],

/////////////------------------------------

define(['jquery'], function ($) {
  'use strict'; 
  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom', [
        function (value) {
          if (/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value) || /^(09|959|\+)([0-9]{7,15})$/i.test(value))
          {
            return true;
          } else {
            return false;
          }
        },
        $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')

      ]
      
    )
  }
});

/////////////------------------------------

define(['jquery'], function ($) {
  'use strict'; 
  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom', [
        function (value) {
          if (/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value))
          {
            return true;
          } else {
            return false;
          }
        },
        $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')

      ]
      
    )
  }
});