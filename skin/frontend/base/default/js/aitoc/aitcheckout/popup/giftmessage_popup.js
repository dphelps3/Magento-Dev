var AitGiftMessagePopup = Class.create(AitPopup,
{
    lightbox:null,   
    container:null,   
    liteboxId:'popup-litebox-giftmessage',
    controlCheckboxId:'allow_gift_options',
        
    initialize: function(container, controlCheckboxId, textForButtonClose, textForButtonCloseAndSave)
    {        
        this.lightbox = this.generateLiteboxDiv();
        this.container = $(container);
        this.controlCheckboxId = controlCheckboxId;
        Event.observe(this.lightbox, 'click', this.hide.bind(this, 0));
        Event.observe(document, 'keyup', this.checkKey.bind(this)); 
        if(!($('ait-gift-message-close-not-save')))
        {
            this.addAitTextElement('ait-gift-message-close-not-save', 'Close');            
            Event.observe($('ait-gift-message-close-not-save'), 'click', this.hide.bind(this,0));               
        }
        if(!($('ait-gift-message-close-save')))
        {
            this.addAitTextElement('ait-gift-message-close-save', 'Close And Save');              
            Event.observe($('ait-gift-message-close-save'), 'click', this.hide.bind(this,1));            
        }
    }, 

    generateLiteboxDiv: function()
    {
        var liteboxDiv=document.createElement("div");
        liteboxDiv.setAttribute('id', this.liteboxId);
        if (document.body.firstChild){
            document.body.insertBefore(liteboxDiv, document.body.firstChild);
        } else {
             document.body.appendChild(liteboxDiv);
        }
         
        return liteboxDiv;
    },

    hide: function(saveOrNot) {
        if(shippingMethod.initSaveGiftOptionsEvents('ait-gift-message-close-save') === false && saveOrNot == 1)
        {
            alert('Please fill all the required fields');
            return false;
        }
        
        if(this.container.hasClassName('gift-message-popup-show'))
        {
            this.container.removeClassName('gift-message-popup-show');
        }
        if(saveOrNot == 0)
        {    
            $(this.controlCheckboxId).checked = false;
            this.eventFire($(this.controlCheckboxId), 'click');
        }
        this.toggleLitebox(false);
    },    

    checkKey: function(evt) {
        var code;
        if (evt.keyCode) code = evt.keyCode;
        else if (evt.which) code = evt.which;
        if (code == Event.KEY_ESC) {
            this.hide.bind(this,0);
        }
    },

    addAitTextElement: function(id, text){
        var mydiv=document.createElement("div");
        var divtext=document.createTextNode(text);
        mydiv.setAttribute('id', id);
        mydiv.setStyle({cursor:'pointer'});
        mydiv.appendChild(divtext);
        $(this.container).appendChild(mydiv); 
    }, 

    eventFire: function(el, etype){
        if (el.fireEvent) {
           (el.fireEvent('on' + etype));
        } else {
            var evObj = document.createEvent('Events');
            evObj.initEvent(etype, true, false);
            el.dispatchEvent(evObj);
        }
    }, 

    show: function() {        
        this.toggleLitebox(true);
        if(!this.container.hasClassName('gift-message-popup-show'))
        {
            this.container.addClassName('gift-message-popup-show');
        }
    },
});