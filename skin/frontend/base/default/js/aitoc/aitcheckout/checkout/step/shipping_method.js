var AitShippingMethod = Class.create(Step,  
    {
        initShippingMethod: function(shippingMethodContainerId)
        {
            this.giftSaveButtonId = 'shipping-method-save-gift-options-button';
            this.giftErrorDivId = 'ait-shipping-method-not-selected-error';
            this.initEvents(shippingMethodContainerId);
            this._initEventsForHideGiftOptsErrors(shippingMethodContainerId);            
            this.shippMethodValidator = new AitValidation(this.container);            
        },

        afterInit: function()
        {
            this.initShippingMethod(this.ids.loadContainer);

            if (aitCheckout.getStep('shipping'))
            {
                aitCheckout.getStep('shippinglocation').setReloadSteps(['shipping_method']);
            }
            else
            {
                aitCheckout.getStep('billinglocation').setReloadSteps(['shipping_method']);
                aitCheckout.getStep('billinglocation').initVirtualUpdate();
            }

            this.setReloadSteps(['payment', 'review']);
        },

        _initEventsForHideGiftOptsErrors: function(containerId)
        {
            if ($(containerId))
            {
                $(containerId).select('input', 'select', 'textarea').each(
                    function(input)
                    {
                        if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') 
                        {
                            Event.observe(input, 'click', this._hideDomElement.bind(this,this.giftErrorDivId)
                            );
                        } 
                        else {
                            Event.observe(input, 'change', this._hideDomElement.bind(this,this.giftErrorDivId));
                        }
                    }.bind(this)
                );
            }    
        },

        initDeselectGiftOptionsCheckboxEvents: function(elemId)
        {
            if ($(elemId))
            {            
                Event.observe($(elemId), 'click', this._updateOnUncheck.bind(this, elemId)
                    );                
            }    
        },

        _updateWithValidation: function()
        {
            if (this.shippMethodValidator && this.shippMethodValidator.validate())
            {
                this.update();
                return true;
            }
            return false;
        },

        initSaveGiftOptionsEvents: function(elemId)
        {
            if ($(elemId))
            {     
                if (this._updateWithValidation() != true)
                {
                    //this._showDomElement(this.giftErrorDivId);
                    return false;
                } 
            }    
        },    

        _hideDomElement: function(elemId)
        {
            if($(elemId)){
                $(elemId).setStyle({display:'none'});
            }
        },

        _showDomElement: function(elemId)
        {
            if($(elemId)){
                $(elemId).setStyle({display:'block'});
            }
        },

        _updateOnUncheck: function(elemId)
        {
            if($(elemId).checked == false)
            {
                this._updateWithValidation();
                this._hideDomElement(this.giftSaveButtonId);
                this._hideDomElement(this.giftErrorDivId);
            }
            else
            {
                this._showDomElement(this.giftSaveButtonId);
            }
        }
    });