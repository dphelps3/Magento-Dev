var AitCustomerPopup = Class.create(AitPopup,
{
    container:null,
    lightbox:null,
    shown: false,
    inputs: {},    
    
    tab:function() {
        if(!this.shown) return false;
        var haveFocus = false;
        for(var i=0;i<this.inputs.length;i++) {
            if(this.inputs[i].focused) {
                haveFocus = true;
                break;
            }
        }
        if(haveFocus == false)
            this.inputs[0].select();
    }    
});