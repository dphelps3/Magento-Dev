var AitCustomreview = Class.create(Step,
    {
        afterSet: function()
        {
            if(aitCheckout.isStatusChanged()) {
                aitCheckout.getStep('customreview').onUpdateResponseAfter({customreview:{length:0}})
            }
        }
    });