var AitGiftwrap = Class.create(Step,  
{
    initGiftwrap: function()
    {
        this.initEvents(this.container);
    },

    afterInit: function()
    {
        this.initGiftwrap();
        this.setReloadSteps(['payment', 'review']);

        if (this.isShowCartInCheckout)
        {
            this.addReloadSteps(['messages']);
        }

        if (aitCheckout.getStep('coupon'))
        {
            aitCheckout.getStep('coupon').addReloadSteps(['aitgiftwrap']);
        }

        if (aitCheckout.getStep('review') && this.isShowCartInCheckout)
        {
            aitCheckout.getStep('review').addReloadSteps(['aitgiftwrap']);
        }

        aitCheckout.setStep('aitgiftwrap', this);
    }
    
});