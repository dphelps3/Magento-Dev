var AitGiftcard = Class.create(Step,  
{
    afterInit: function()
    {
        this.initGiftCard('giftcard-apply-button');

        this.setReloadSteps(['giftcard']);

        this.addReloadSteps(['payment', 'review']);
        if (this.getCheckout().getStep('aitgiftwrap'))
        {
            this.addReloadSteps(['aitgiftwrap']);     
        }
    },

    initGiftCard: function(applyId, cancelId)
    {
        if ($(applyId))
        {
            $(applyId).observe('click', this.onChangeStepData.bind(this));
        }
        if ($(cancelId))
        {
            $(cancelId).observe('click', this.onChangeStepData.bind(this));
        }
       
    },   
    
    update: function(event)
    {
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});
        var validator = new Validation(this.container);
        
        if (validator && validator.validate())
        { 
            this.reloadSteps.each(
                function(stepName) {
                    this.getCheckout().getStep(stepName).loadWaiting();    
                }.bind(this)
            );    
            
            var request = new Ajax.Request(
                this.urls.giftcardUpdateUrl,
                {
                    method: 'post',
                    onComplete: this.onUpdateChild,
                    onSuccess: this.onUpdate,
                    parameters: params
                }
            );
        }
            
    },
    
    onUpdateResponseAfter: function(response)
    {
        var notice = $('giftcard-notice');
                
        if (response.giftcard.length != 0)
        {
            if (response.giftcard.error == 0)
            {
                notice.addClassName('success-msg');  
            } else if (response.giftcard.error == -1)
            {
                notice.addClassName('error-msg');
            } else if (response.giftcard.error == 1)
            {
                notice.addClassName('notice-msg');    
            }
            notice.update(response.giftcard.message); 
            $('giftcard-notice').show(); 
        }   
    }
          
});