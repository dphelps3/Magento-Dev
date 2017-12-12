var AitPayment = Class.create(Step,  
{
    initPayment: function(paymentContainerId)
    {
        this.initEvents(paymentContainerId);
        $(paymentContainerId).select('input[type="radio"]').each(function(input){
            input.addClassName('validate-one-required-by-name');
        });                
    },

    afterInit: function()
    {
        if(this.ids)
            this.initPayment(this.ids.paymentMethodLoad);
        this.setReloadSteps(['review']);
    }
});