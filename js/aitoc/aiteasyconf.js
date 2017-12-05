// says translated phrases
var EC_Translator = new Class.create();
EC_Translator.prototype =
{
    phrases : null,

    initialize  : function()
    {
        this.phrases = $H({});
    },

    addPhrase : function(key, phrase)
    {
        this.phrases.set(key, phrase);
    },

    say: function(key)
    {
        return this.phrases.get(key);
    }
}

// displays messages in the upper menu stripe
var EC_Messenger = new Class.create();
EC_Messenger.prototype =
{
    blockArea : null,
    types : null,

    initialize : function()
    {
        this.blockArea = $('easyconf-messages').down();
        this.types = new Array(
            'success-msg',
            'notice-msg',
            'error-msg'
        );
    },

    message : function(txt, type)
    {
        var cls = this.types[type];
        var msg = new Element('li', {'class': cls}).update('<ul><li><span>' + txt + '</span></li></ul>');
        this.blockArea.insert(msg, {position: "bottom"});
    },

    clear : function()
    {
        this.blockArea.update('');
    }
}

// manages ajax requests
var EC_Courier = new Class.create();
EC_Courier.prototype =
{
    url : null,
    tries : 3,
    
    initialize : function(url)
    {
        this.url = url;
    },

    // a wrapper, to try to request {this.tries} times
    request : function(data)
    {
        var _result = this.sendRequest(data);

        if (true !== _result.status)
        {
            var txt = ec_translator.say('erroroccured');
            txt += '\n\n';

            if (_result.text)
            {
                txt += _result.text.replace(/\\n/g, '\n');
                txt += '\n\n';
            }
            txt += ec_translator.say('tryagain');

            if (confirm(txt))
            {
                var s = this.tries;
                while ((--s >= 0) && (true !== _result.status))
                {
                    _result = this.sendRequest(data);
                }
            }
            else
            {
                return false;
            }
            
            if (true !== _result.status)
            {
                alert(ec_translator.say('retriesfailed').replace('###', this.tries));
                return false;
            }
        }

        return _result;
    },

    sendRequest : function(data)
    {
        var result = new Object();
        
        new Ajax.Request(
            this.url,
            {
                method : 'post',
                asynchronous : false, // we need to ensure each package delivered
                parameters : data,
                onSuccess : function(response)
                {
                    if (response && response.responseText)
                    {
                        try
                        {
                            response = eval('(' + response.responseText + ')');
                        }
                        catch (e)
                        {
                            response = {};
                        }
                        
                        if (response.error)
                        {
                            result.status = false;
                            result.text = response.error;
                        }
                        else if (response.success)
                        {
                            result.status = true;
                            result.text = response.success;
                        }
                        else
                        {
                            result.status = false;
                        }
                    }
                },
                
                onFailure : function()
                {
                    result.status = false;
                }
            }
        );

        return result;
    }
};

// a simple product
var EC_Product = new Class.create();
EC_Product.prototype =
{
    id : null,
    parameters : null,
    attributes : null,
    backup : null,

    initialize : function(id)
    {
        this.id = id;
        this.parameters = $H({});
        this.attributes = $H({});
    },

    load : function()
    {
        for (var i = 0; i < ec_helper.productParametes.length; i++)
        {
            var parameter =  ec_helper.productParametes[i];
            this.parameters.set(parameter, $('products[' + this.id + '][' + parameter + ']').value);
        }

        for (i = 0; i < ec_helper.productAttributes.length; i++)
        {
            var attribute =  ec_helper.productAttributes[i];
            var attributeForm = $('form-product-' + this.id + '-attribute-' + attribute);
            var radioName = 'product-' + this.id + '-attribute-' + attribute;

            Form.getInputs(attributeForm, 'radio', radioName).each(function(radio)
            {
                if (radio.checked)
                {
                    this.attributes.set(attribute, radio.value);
                }
            }.bind(this));
        }
        this.backup = this.toQueryString();

        return this;
    },

    update : function()
    {
        if (this.backup != this.toQueryString())
        {
            for (var i = 0; i < ec_helper.productParametes.length; i++)
            {
                var parameter =  ec_helper.productParametes[i];
                $('products[' + this.id + '][' + parameter + ']').value = this.parameters.get(parameter);
            }

            for (i = 0; i < ec_helper.productAttributes.length; i++)
            {
                var attribute =  ec_helper.productAttributes[i];
                var attributeForm = $('form-product-' + this.id + '-attribute-' + attribute);
                var radioName = 'product-' + this.id + '-attribute-' + attribute;
                var newValue = this.attributes.get(attribute);

                Form.getInputs(attributeForm, 'radio', radioName).each(function(radio)
                {
                    radio.checked = (radio.value == newValue);
                });
            }
            ec_helper.decorate(this.id);
            this.setStatus('changed');
        }

        return this;
    },

    remove : function()
    {
        $('product-column-' + this.id).remove();
        ec_helper.setHasChanges();
    },

    setStatus : function(status)
    {
        var _status;
        for (var i = 0; i < ec_helper.productStatuses.length; i++)
        {
            _status = ec_helper.productStatuses[i];
            if (_status == status)
            {
                $('product-' + _status + '-' + this.id).show();
            } 
            else
            {
                $('product-' + _status + '-' + this.id).hide();
            }
        }

        if ('unchanged' != status)
        {
            $('products[' + this.id + '][is_changed]').value = 1;
            ec_helper.setHasChanges();
        }

        return this;
    },

    getStatus : function()
    {
        var _status;
        for (var i = 0; i < ec_helper.productStatuses.length; i++)
        {
            _status = ec_helper.productStatuses[i];
            if ($('product-' + _status + '-' + this.id).getStyle('display') != 'none')
            {
                return _status;
            }
        }

        return false;
    },

    toQueryString : function()
    {
        var s = '';
        for (var i = 0; i < ec_helper.productParametes.length; i++)
        {
            var parameter =  ec_helper.productParametes[i];
            s += '&products[' + this.id + '][' + parameter + ']=' + this.parameters.get(parameter);
        }

        for (i = 0; i < ec_helper.productAttributes.length; i++)
        {
            var attribute =  ec_helper.productAttributes[i];
            s += '&products[' + this.id + '][attributes][' + attribute + ']=' + this.attributes.get(attribute);
        }

        return s;
    },

    exists : function()
    {
        return ($('product-column-' + this.id)) ? true : false;
    }
}

// progress bar
var EC_Progressbar = new Class.create();
EC_Progressbar.prototype =
{
    blockWindow : null,
    blockText : null,
    blockLog : null,

    initialize : function()
    {
        this.blockWindow    = $('easyconf-progress');
        this.blockText      = $('easyconf-progress-text');
        this.blockLog       = $('easyconf-progress-log');

        this.blockWindow.hide();
    },

    setText : function(text)
    {
        this.blockText.update(text);
    },

    logSuccess : function(text)
    {
        this.addLogEntry(text, 'success');
    },

    logError : function(text)
    {
        this.addLogEntry(text, 'error');
    },

    addLogEntry : function(text, status)
    {
        var _timestamp = new Date().toTimeString().substr(0, 8);

        var cls = 'log-' + status;
        var log = new Element('li', {'class': cls}).update('[' + _timestamp + ']: ' +text);
        this.blockLog.insert(log, {position: "bottom"});
        $('easyconf-progress-log-container').scrollTop = $('easyconf-progress-log-container').scrollTop + 50;
    },

    show : function()
    {
        this.blockWindow.show();
        ec_helper.toggleLitebox(true);
    },
    
    hide : function()
    {
        ec_helper.toggleLitebox(false);
        this.blockWindow.hide();
        this.clear();
    },

    clear : function()
    {
        this.setText('');
        this.blockLog.update('');
    }
}

// product configuration form
var EC_ConfigurationForm = new Class.create();
EC_ConfigurationForm.prototype =
{
    blockForm : null,
    form : null,
    product : null,

    initialize : function()
    {
        this.blockForm = $('product-config-form');
        this.form = new VarienForm('product-config-form-validate');
        this.blockForm.hide();
    },

    open : function(id)
    {
        this.product = new EC_Product(id);
        this.product.load();
        this.load();
        this.blockForm.show();
        ec_helper.toggleLitebox(true);
        Event.observe(document, 'keypress', this.closeOnEsc.bind(this));

        return this;
    },

    load : function()
    {
        var i, field;
        for (i = 0; i < ec_helper.productParametes.length; i++)
        {
            var parameter =  ec_helper.productParametes[i];
            field = $('product-config[' + parameter + ']');

            if ('checkbox' == field.type)
            {
                field.checked = Boolean(Number(this.product.parameters.get(parameter)));
                toggleValueElements(field, field.up().up(), field.checked)
            } 
            else
            {
                field.value = this.product.parameters.get(parameter) || '';
            }
        }

        for (i = 0; i < ec_helper.productAttributes.length; i++)
        {
            var attribute =  ec_helper.productAttributes[i];
            field = $('product-config[attributes][' + attribute + ']');
            field.value = this.product.attributes.get(attribute) || '';
        }

        return this;
    },

    close : function()
    {
        ec_helper.toggleLitebox(false);
        this.blockForm.hide();
        this.clear();
        Event.stopObserving(document, 'keypress', this.closeOnEsc);
        return true;
    },

    closeOnEsc : function(event)
    {
        if (Event.KEY_ESC == event.keyCode)
        {
            this.close();
        }
    },

    validate : function()
    {
        return (this.form.validator && this.form.validator.validate());
    },

    apply : function()
    {
        if (this.validate())
        {
            var i, field;
            for (i = 0; i < ec_helper.productParametes.length; i++)
            {
                var parameter =  ec_helper.productParametes[i];
                field = $('product-config[' + parameter + ']');

                if ('checkbox' == field.type)
                {
                    this.product.parameters.set(parameter, Number(field.checked));
                } 
                else
                {
                    this.product.parameters.set(parameter, field.value);
                }
            }

            for (i = 0; i < ec_helper.productAttributes.length; i++)
            {
                var attribute =  ec_helper.productAttributes[i];
                field = $('product-config[attributes][' + attribute + ']');
                this.product.attributes.set(attribute, field.value);
            }
            this.product.update();
            this.close();
            return true;
        }
        return false;
    },

    clear : function()
    {
        this.blockForm.select('input', 'select').each(function(field)
        {
            if ('checkbox' == field.type)
            {
                field.checked = false;
                toggleValueElements(field, field.up().up(), field.checked)
            } 
            else
            {
                field.value = '';
            }
        });
    }
}

// generation form
var EC_GenerationForm = new Class.create();
EC_GenerationForm.prototype =
{
    blockForm : null,
    blockTip : null,
    form : null,
    options : null,
    
    initialize : function()
    {
        this.options = $H({});
        this.blockForm = $('generation-form');
        this.blockTip = $('generation-form-tip');
        this.form = new VarienForm('generation-form-validate');
        this.blockForm.hide();
    },

    open : function()
    {
        this.options = $H({});
        if (this.checkOptionsSelected())
        {
            var combinationsNumber = this.getCombinationsNumber();

            if (!ec_helper.checklimit(combinationsNumber))
            {
                alert(
                    ec_translator.say('limitexceeded_generate').replace('###', combinationsNumber)
                );
                return false;
            }

            this.blockTip.update(
                ec_translator.say('combinations').replace('###', combinationsNumber.toString())
            );
            this.blockForm.show();
            ec_helper.toggleLitebox(true);
            Event.observe(document, 'keypress', this.closeOnEsc.bind(this));
            
            return this;
        }
        
        return false;
    },

    checkOptionsSelected: function()
    {
        var i, attribute, values;
        for (i = 0; i < ec_helper.productAttributes.length; i++)
        {
            attribute = ec_helper.productAttributes[i];
            values = new Array();
            ec_helper.blockMain.select('input[id^="generate-' + attribute + '"]').each(function(checkbox)
            {
                if (checkbox.checked == 1)
                {
                    values.push(checkbox.id.replace('generate-' + attribute + '-', ''));
                }
            });

            if (values.length == 0)
            {
                alert(ec_translator.say('selectvalues'));
                return false;
            } 
            else
            {
                this.options.set(attribute, values);
            }
        }
        
        return true;
    },

    getCombinationsNumber : function()
    {
        var x = 1;
        this.options.each(function(option)
        {
            x *= option.value.length;
        });

        return x;
    },

    close : function()
    {
        ec_helper.toggleLitebox(false);
        this.blockForm.hide();
        this.clear();
        Event.stopObserving(document, 'keypress', this.closeOnEsc);

        return true;
    },

    closeOnEsc : function(event)
    {
        if (Event.KEY_ESC == event.keyCode)
        {
            this.close();
        }
    },

    validate : function()
    {
        return (this.form.validator && this.form.validator.validate());
    },

    clear : function()
    {
        this.blockForm.select('input', 'select').each(function(field)
        {
            field.value = '';
        });
        this.blockTip.update('');

        return this;
    },

    getCommon : function()
    {
        var common = $H({});
        this.blockForm.select('input', 'select').each(function(field)
        {
            common.set(field.id.replace('generation-common-', ''), field.value);
        });

        return common;
    },

    generate : function()
    {
        if (this.validate())
        {
            var _options = Object.toJSON(this.options);
            var _common = Object.toJSON(this.getCommon());
            this.close();
            ec_helper.ajax_generate(_options, _common);

            return true;
        }

        return false;
    }
}

// all other functions
var EC_Helper = new Class.create();
EC_Helper.prototype =
{
    blockMain : null,
    blockLitebox : null,
    
    productParametes : Array(
        'name',
        'autogenerate_name',
        'sku',
        'autogenerate_sku',
        'weight',
        'price',
        'status',
        'visibility',
        'stock',
        'qty',
        'is_changed'),
    productAttributes : null,
    productStatuses : Array(
        'unchanged',
        'changed',
        'invalid',
        'duplicate'),
    
    parameters : $H({}),
    newProductIndex : 0,

    temporaryIds : null,
    timer : null,
    running : false,

    initialize : function()
    {
        this.blockMain = $('easyconf-main-table');
        this.blockLitebox = $('easyconf-litebox');
        this.productAttributes = new Array();
    },

    createNewProductId : function()
    {
        return "new" + (++this.newProductIndex);
    },

    addAttributeId  : function(attributeId)
    {
        this.productAttributes.push(attributeId);
    },

    setParameter : function(parameter, value)
    {
        this.parameters.set(parameter, value);
    },

    getParameter : function(parameter)
    {
        return this.parameters.get(parameter);
    },

    setHasChanges : function()
    {
        $('aiteasyconf_has_changes').value = 1;
        return this;
    },

    getHasChanges : function()
    {
        return $('aiteasyconf_has_changes').value;
    },

    getActionUrl : function(action)
    {
        var _url = this.getParameter('controllerUrl').replace('xxxxx', action);
        return _url;
    },

    getProductsIds: function()
    {
        var ids = new Array();
        this.blockMain.select('td[id^="product-column-"]').each(function(column)
        {
            ids.push(column.id.replace('product-column-', ''));
        });
        
        return ids;
    },

    // add blank product
    createProduct : function()
    {
        if (!this.checklimit(1))
        {
            alert(
                ec_translator.say('limitexceeded_create')
            );
            return false;
        }

        var newId = this.createNewProductId();
        var columnContent = $('product-column-blank').innerHTML.replace(new RegExp('blank','g'), newId.toString());
        var column = new Element('td', {id: "product-column-" + newId }).update(columnContent);
        $('easyconf-main-row').insert(column, {position: "bottom"});

        this.setHasChanges()

        return newId;
    },

    // remove product from table
    deleteProduct : function(id)
    {
        if (confirm(ec_translator.say('confirmdelete')))
        {
            var _product = new EC_Product(id);
            _product.remove()
        }
    },

    // remove all products from table
    deleteAll : function(confirmation)
    {
        if (typeof confirmation == "undefined")
        {
            confirmation = true;
        }

        var ids = this.getProductsIds();
        var _count = ids.length;
        if (_count > 0)
        {
            if (confirmation && !confirm(ec_translator.say('confirmdelete')))
            {
                return false;
            }

            var _id;
            while (_id = ids.pop())
            {
                var _product = new EC_Product(_id);
                _product.remove()
            }

            if (confirmation)
            {
                ec_messenger.clear();
                ec_messenger.message(
                    ec_translator.say('allproductsremoved').replace('###', _count),
                    1
                );
            }
        }

        return true;
    },

    toggleAllOptions : function(checkbox)
    {
        var id = checkbox.id.replace('generate-all-', '');
        this.blockMain.select('input[id^="generate-' + id + '-"]').each(function(option)
        {
            option.checked = checkbox.checked;
        }); 
    },

    setProductStatus : function(id, status)
    {
        var _product = new EC_Product(id);
        _product.setStatus(status);
    },

    // color selected cells in product column or in the whole table
    decorate : function(id)
    {
        var block = (id) ? ($('product-attributes-' + id)) : ($('easyconf-container'));
        var radiobuttons = block.select('input[type="radio"]');

        radiobuttons.each(function(radiobutton)
        {
            radiobutton.up().up().removeClassName('cell-selected');
            if (radiobutton.checked == true)
            {
                radiobutton.up().up().addClassName('cell-selected');
            }
        });
    },

    toggleLitebox : function(flag)
    {
        if (true == flag)
        {
            this.blockLitebox.show();
        }
        else
        {
            this.blockLitebox.hide();
        }
    },

    store_statuses : function(ids)
    {
        var tmpStatuses = $H({});
        ids.each(function(_id)
        {
            var _product = new EC_Product(_id);
            tmpStatuses.set(_id, _product.getStatus());
        });

        this.setParameter('tmpStatuses', tmpStatuses);
    },

    restore_statuses : function()
    {
        // if we had previously detected some duplicates/invalids
        // we must restore their status before next validation
        var tmpStatuses = this.getParameter('tmpStatuses');
        if (tmpStatuses)
        {
            tmpStatuses.each(function(pair)
            {
                var _product = new EC_Product(pair.key);
                if (_product.exists())
                {
                    _product.setStatus(pair.value);
                }
            });
        }
    },

    /* saving process */

    // onclick "save" || "save & continue"
    save : function()
    {
        if (1 == this.getHasChanges())
        {
            ec_messenger.clear();
            ec_progressBar.setText(ec_translator.say('pleasewait'));
            ec_progressBar.show();
            this.ajax_cleartemp();
            return true;
        }
        else
        {
            $('product_info_tabs_easyconf_content').innerHTML = "";
            this.saveMainProduct();
        }
        return true;
    },

    // prepare - clear temp
    // standalone used if we only need to clear temp, for example when ignoring restoration
    ajax_cleartemp : function(standalone)
    {
        if (standalone)
        {
            ec_progressBar.show();
        }

        ec_progressBar.setText(ec_translator.say('ajax_cleartemp'));
        
        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        var _courier = new EC_Courier(this.getActionUrl('cleartemp'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        ec_progressBar.logSuccess(
            ec_translator.say('ajax_cleartemp_success')
        );

        if (standalone)
        {
            ec_progressBar.hide();
            return true;
        }

        this.temporaryIds = this.getProductsIds();
        this.timer = setInterval(this.ajax_storetemp.bind(this), 500);
        return true;
    },

    // first step - sending all table data to server by batches
    ajax_storetemp : function()
    {
        if (true == this.running) { return false; }

        ec_progressBar.setText(ec_translator.say('ajax_storetemp'));

        this.running = true;

        var s = this.getParameter('batchSize');
        var _id;
        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');

        while ((--s >= 0) && (_id = this.temporaryIds.shift()))
        {
            var _product = new EC_Product(_id).load();
            data += _product.toQueryString();
        }

        var _courier = new EC_Courier(this.getActionUrl('storetemp'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();

            clearInterval(this.timer);
            this.running = false;
            return false;
        }

        ec_progressBar.logSuccess(
            ec_translator.say('ajax_storetemp_success_batch').replace('###', _result.text.count)
        );
        if (0 == this.temporaryIds.length)
        {
            ec_progressBar.logSuccess(
                ec_translator.say('ajax_storetemp_success_all')
            );
            clearInterval(this.timer);
            this.running = false;
            this.ajax_validate();
            return true;
        }

        this.running = false;
        return true;
    },

    // sending request to validate temporary data
    ajax_validate : function()
    {
        ec_progressBar.setText(ec_translator.say('ajax_validate'));

        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        var _courier = new EC_Courier(this.getActionUrl('validate'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        this.restore_statuses();

        if ('invalid' == _result.text.status)
        {
            // highlight invalid
            var ids = eval(_result.text.ids);
            this.store_statuses(ids);
            ids.each(function(_id)
            {
                var _product = new EC_Product(_id);
                _product.setStatus('invalid');
            });

            ec_messenger.clear();
            ec_messenger.message(
                ec_translator.say('productsinvalid').replace('###', _result.text.count),
                2
            );
            ec_progressBar.hide();
            return true;
        }

        if ('duplicates' == _result.text.status)
        {
            if (confirm(ec_translator.say('deleteduplicates').replace('###', _result.text.count)))
            {
                this.ajax_remove_duplicates();
                return true;
            }
            else
            {
                // highlight duplicates
                var ids = eval(_result.text.ids);
                this.store_statuses(ids);
                ids.each(function(_id)
                {
                    var _product = new EC_Product(_id);
                    _product.setStatus('duplicate');
                });

                ec_messenger.clear();
                ec_messenger.message(ec_translator.say('duplicatesmarked'), 1);
                ec_progressBar.hide();             
                return true;
            }
        }
        
        if ('allok' == _result.text.status)
        {
            ec_progressBar.logSuccess(
                ec_translator.say('ajax_validate_success')
            );
            this.ajax_delete_products();
            return true;
        }

        ec_progressBar.hide();
        return false;
    },

    // sending request to remove duplicates in temporary data
    ajax_remove_duplicates : function()
    {
        ec_progressBar.setText(ec_translator.say('ajax_remove_duplicates'));

        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        var _courier = new EC_Courier(this.getActionUrl('removeduplicates'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        ec_progressBar.logSuccess(
            ec_translator.say('ajax_remove_duplicates_success').replace('###', _result.text.count)
        );
        this.ajax_delete_products();
        return true;
    },

    // send request to delete simple products that are not in temporary data
    ajax_delete_products : function()
    {
        ec_progressBar.setText(ec_translator.say('ajax_delete_products'));

        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        var _courier = new EC_Courier(this.getActionUrl('delete'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        ec_progressBar.logSuccess(
            ec_translator.say('ajax_delete_products_success').replace('###', _result.text.count)
        );
        this.timer = setInterval(this.ajax_save.bind(this), 500);
        return true;
    },

    // save products in temporary data
    ajax_save : function()
    {
        if (true == this.running) { return false; }

        ec_progressBar.setText(ec_translator.say('ajax_save'));

        this.running = true;

        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        var _courier = new EC_Courier(this.getActionUrl('save'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            clearInterval(this.timer);
            this.running = false;

            ec_progressBar.hide();
            return false;
        }

        if ('savedAll' == _result.text.status)
        {
            ec_progressBar.logSuccess(
                ec_translator.say('ajax_save_success_all')
            );
            clearInterval(this.timer);
            this.running = false;

            ec_messenger.message(
                ec_translator.say('savingmain'),
                1
            );
            ec_progressBar.hide();
            this.saveMainProduct();
            return true;
        }
        else
        {
            ec_progressBar.logSuccess(
                ec_translator.say('ajax_save_success_batch').replace('###', _result.text.count)
            );
            this.running = false;
            return true;
        }

        ec_progressBar.hide();
        return false;
    },

    // remove forms that may prevent validation when saving main configurable product
    beforeSave : function()
    {
        this.ignoreRestoration(true);

        if ($('generation-form'))
        {
            $('generation-form').remove();
        }
        if ($('product-config-form'))
        {
            $('product-config-form').remove();
        }
    },

    // finally - submit the original form
    saveMainProduct: function()
    {
        this.beforeSave();
        productForm.submitOriginal();
    },

    /* generation */

    ajax_generate : function(options, common)
    {
        ec_progressBar.setText(ec_translator.say('ajax_generate'));
        ec_progressBar.show();
        
        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');
        data    += '&options=' + options;
        data    += '&common=' + common;

        var _courier = new EC_Courier(this.getActionUrl('generate'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        $('easyconf-main-row').insert(_result.text.html);
        this.setHasChanges();
        ec_helper.decorate();
        
        ec_messenger.clear();
        ec_messenger.message(
            ec_translator.say('generated').replace('###', _result.text.count),
            1
        );
        ec_progressBar.hide();
        return true;
    },

    /* temporary data restoration */

    ajax_restore : function()
    {
        $('easyconf-restore').remove();

        ec_progressBar.setText(ec_translator.say('ajax_restore'));
        ec_progressBar.show();

        var data = 'form_key=' + FORM_KEY + '&mainId=' + this.getParameter('mainId');

        var _existingIds = Object.toJSON(this.getProductsIds());
        data += '&existingIds=' + _existingIds;

        var _courier = new EC_Courier(this.getActionUrl('restore'));
        var _result = _courier.request(data);
        if (false == _result)
        {
            ec_progressBar.hide();
            return false;
        }

        $('easyconf-main-row').insert(_result.text.html);
        this.setHasChanges();
        ec_helper.decorate();

        ec_messenger.clear();
        ec_messenger.message(
            ec_translator.say('restored').replace('###', _result.text.count),
            1
        );
        ec_progressBar.hide();
        return true;
    },

    ignoreRestoration : function(clear)
    {
        if ($('easyconf-restore'))
        {
            $('easyconf-restore').remove();
            if (clear)
            {
                this.ajax_cleartemp(true);
            }
        }
    },

    // check if products limit will be exceeded when creating or generating products
    checklimit : function(addQty)
    {
        if (this.getParameter('useLimit'))
        {
            var existingQty = this.getProductsIds().length;
            var limit = this.getParameter('productsLimit');
            return ((existingQty + addQty) <= limit);
        }
        else
        {
            return true;
        }
    }
}