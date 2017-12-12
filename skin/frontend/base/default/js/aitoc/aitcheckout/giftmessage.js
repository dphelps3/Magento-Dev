/*
 * Following code moved here from 'enterprise/default/template/giftmessage/inline.phtml file
 */
if(!window.toogleVisibilityOnObjects) {
    var toogleVisibilityOnObjects = function(source, objects) {
        if($(source) && $(source).checked) {
            objects.each(function(item){
                $(item).show();
                $$('#' + item + ' .input-text').each(function(item) {
                    item.removeClassName('validation-passed');
                });
            });
        } else {
            objects.each(function(item){
                if ($(item)) {
                    $(item).hide();
                    $$('#' + item + ' .input-text').each(function(sitem) {
                        sitem.addClassName('validation-passed');
                    });
                    $$('#' + item + ' .giftmessage-area').each(function(sitem) {
                        sitem.value = '';
                    });
                    $$('#' + item + ' .checkbox').each(function(sitem) {
                        sitem.checked = false;
                    });
                    $$('#' + item + ' .select').each(function(sitem) {
                        sitem.value = '';
                    });
                    $$('#' + item + ' .price-box').each(function(sitem) {
                        sitem.addClassName('no-display');
                    });
                }
            });
        }
    }
}

if(!window.toogleVisibility) {
    var toogleVisibility = function(objects, show) {
        objects.each(function(item){
            if (show) {
                $(item).show();
                $(item).removeClassName('no-display');
            }
            else {
                $(item).hide();
                $(item).addClassName('no-display');
            }
        });
    }
}

if(!window.displayContainer) {
    var displayContainer = function(source) {
       if ($(source)) {
           if ($(source).hasClassName('no-display')) {
               $(source).removeClassName('no-display');
           } else {
               $(source).addClassName('no-display');
           }
       }
       return false;
    }
}

if(!window.toogleRequired) {
    var toogleRequired = function (source, objects)
    {
        if(!$(source).value.blank()) {
            objects.each(function(item) {
               $(item).addClassName('required-entry');
            });
        } else {
            objects.each(function(item) {
                if (typeof shippingMethod != 'undefined' && shippingMethod.validator) {
                   shippingMethod.validator.reset(item);
                }
                $(item).removeClassName('required-entry');
            });
        }
    }
}