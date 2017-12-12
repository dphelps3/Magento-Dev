var AitDeliverydate = Class.create(Step,  
{
    initDeliverydate: function()
    {
        this.initEvents(this.container); 
    },
    onUpdateResponseAfter: function(response)
    {
        if (response.deliverydate.length != 0)
        {
            if (response.deliverydate.valid_date)
            {
                $('delivery_date').value = response.deliverydate.valid_date;
            }
        }   
    },
    afterInit: function()
    {
        this.initDeliverydate();
    }
    
});