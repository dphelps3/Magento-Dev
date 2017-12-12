var AitCustomerbalance = Class.create(Step,  
{
    initCustomerbalance: function(customerbalanceContainerId)
    {
        this.initEvents(customerbalanceContainerId);                        
    },

    afterInit: function()
    {
        this.initCustomerbalance(this.container);
        this.setReloadSteps(['review']);
    }
});