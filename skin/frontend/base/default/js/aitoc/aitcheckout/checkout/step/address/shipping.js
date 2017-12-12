var AitShipping = Class.create(AitAddress,  
{
    initShipping: function(checkboxId)
    {   
        if ($(checkboxId)) 
        {
            $(checkboxId).observe('click', this.onChangeUseForShipping.bind(this, checkboxId));
        } 
        Event.observe(window, 'load', this.onChangeUseForShipping.bind(this, checkboxId));
    },
    
    afterInit: function()
    {
        this.initAddress(this.ids.addressSelect, this.ids.addressForm, 'shipping');        
        this.initShipping(this.ids.useForShipping);
        this.initShipping(this.ids.notUseForShipping);        
        Event.observe(window, 'load', this.initGiftAddress.bind(this,'use_gr_address'));  
        Event.observe(window, 'load', this.initShipping.bind(this, this.ids.useGiftAddressForShipping));  

        aitCheckout.createStep(this.name + 'location',this.urls, {
            doCheckErrors : this.doCheckErrors,
            isLoadWaiting : this.isLoadWaiting,
            isUpdateOnReload : this.isUpdateOnReload,
            container : this.container + 'location',
            parent : this
        });
    },

    initGiftAddress: function(giftAddressId)
    {        
        if ($(giftAddressId)) 
        {
            $(giftAddressId).observe('click', function(event) 
            {
                if (Event.element(event).value) {  
                    this.update();
                }                  
            }.bind(this));                       
        }            
    },

    onChangeUseForShipping: function(checkboxId, event)
    {  
        if (typeof this.billingCurrentReloadSteps == "undefined") {
            this.billingCurrentReloadSteps = this.getCheckout().getStep('billinglocation').reloadSteps;
        }

        if ($(checkboxId)) 
        {
            if ($(checkboxId).checked) 
            {
                Element.hide(this.container);

                this.getCheckout().getStep('billing').update(event);
                this.getCheckout().getStep('billinglocation').setReloadSteps(this.getCheckout().getStep('shippinglocation').reloadSteps);
                this.getCheckout().getStep('billinglocation').update(event);
                return;
            }            
        } 
        Element.show(this.container);
        Element.show(this.container + '-child');

        this._reloadSteps();
        return;
    },

    _reloadSteps: function(event)
    {
        this.getCheckout().getStep('billing').update(event);
        this.getCheckout().getStep('shipping').update(event);
        this.getCheckout().getStep('billinglocation').setReloadSteps(this.billingCurrentReloadSteps);
        this.getCheckout().getStep('billinglocation').update(event);
        this.getCheckout().getStep('shippinglocation').update(event);
    },

    afterInit: function()
    {
        this.initAddress(this.ids.addressSelect, this.ids.addressForm, 'shipping');
        this.initShipping(this.ids.useForShipping);


        aitCheckout.createStep(this.name + 'location',this.urls, {
            doCheckErrors : this.doCheckErrors,
            isLoadWaiting : this.isLoadWaiting,
            isUpdateOnReload : this.isUpdateOnReload,
            container : this.container + 'location',
			parent : this
        });
    }
});