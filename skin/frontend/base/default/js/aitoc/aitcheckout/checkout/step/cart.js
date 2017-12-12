var AitCart = Class.create(Step,  
{
    initCart: function(productOptionsCssQuery, qtyCssQuery, increaseCssQuery, decreaseCssQuery, removeCssQuery, wishlistBtnQuery)
    {
        $$(productOptionsCssQuery).each(function(element)
        {
            element.select('input', 'select', 'textarea').each(
                function(input)
                {
                    if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') {
                        input.observe('click', this.updatePost.bind(this));
                    } else {
                       
                        input.observe('change', this.updatePost.bind(this));    
                    }
                }.bind(this)
            );                 
        }.bind(this)); 
        
        $$(increaseCssQuery, decreaseCssQuery).each(function(element){
            element.observe('click', this.updateOptions.bind(this));    
        }.bind(this)); 
        
        $$(qtyCssQuery).each(function(element){
            element.observe('change', this.updatePost.bind(this));        
        }.bind(this)); 
         
        $$(removeCssQuery).each(function(element){
            element.observe('click', this.deletePost.bind(this));    
        }.bind(this)); 
        
        $$(remove2CssQuery).each(function(element){
            element.observe('click', this.deleteFromReviewPost.bind(this, 'giftcard_id'));
        }.bind(this));

        $$(removeCredit).each(function(element){
            element.observe('click', this.deleteFromReviewPost.bind(this, 'storecredit'));
        }.bind(this));

        $$(removeRewards).each(function(element){
            element.observe('click', this.deleteFromReviewPost.bind(this, 'reward'));
        }.bind(this));
        
        $$(wishlistBtnQuery).each(function(element){
            element.observe('click', function(event){
                var itemId = Event.element(event).value;
                $('cart-' + itemId + '-wishlist').value = 1;
                this.updatePost(event);    
            }.bind(this));    
        }.bind(this));
        
        
    }, 
    
    
    updateOptions: function(event)
    {
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;
        
        //in future need to add param @options@ to step initialize params 
        var sign = Event.element(event).hasClassName('btn-increase') ? '1' : (
                        Event.element(event).hasClassName('btn-decrease') ? '-1' : '0' );     
        this.loadWaiting();    
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        ); 
        var request = new Ajax.Request(
            this.urls.updateOptionsUrl + 'id/' + itemRel + '/sign/'+ sign,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   

        this.updateTopCart();
    },

    updatePost: function(event)
    {
        var  form = this.getCheckout().getForm();
        /**
         * multiattribute product with shopping cart editor
         */
        for (var i=0;i<$(form).elements.length;i++)
          {
              if($(form).elements[i].id.indexOf('attribute')==0 && $(form).elements[i].value == "")
              {return ;}
          }
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;  
        this.loadWaiting();     
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        );  
        var request = new Ajax.Request(
            this.urls.updatePostUrl,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   

        this.updateTopCart();
    },
    
    deletePost: function(event)
    {
        var params = Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;  
        this.loadWaiting(); 
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        );  
        var request = new Ajax.Request(
            this.urls.deleteUrl + 'id/' + itemRel,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   

        this.updateTopCart();
    },

    updateTopCart: function() {
        new Ajax.Request(this.urls.updateTopCartUrl, {
            method: 'post',
            onSuccess: function(transport) {
                if (transport && transport.responseText){
                    try {
                        response = eval('(' + transport.responseText + ')');
                    }
                    catch (e) {
                        response = {};
                    }
                }

                if (typeof(response.error) != 'undefined') {
                    return false;
                }

                if (response.content) {
                    var cartElement = $$('div.top-cart').first();
                    if (cartElement) {
                        cartElement.update(response.content);
                    }
                }
            }
        });
    },
    afterInit: function()
    {
        if (this.doNotInitAfter == false)
        {
            this.initCart('.product-options', '.aititemqty', '.btn-increase', '.btn-decrease', '.btn-remove2', '.btn-move-to-wishlist');

            this.setReloadSteps(['messages']);
            if (aitCheckout.getStep('shipping_method'))
            {
                this.addReloadSteps(['shipping_method']);
            } else {
                this.addReloadSteps(['payment', 'review']);
            }

            if (aitCheckout.getStep('giftmessage'))
            {
                this.addReloadSteps(['giftmessage']);
            }

            if (aitCheckout.getStep('aitgiftwrap'))
            {
                this.addReloadSteps(['aitgiftwrap']);
            }
        }
    }

});