	// This new js class extends the Product.Config class to allow
    // us to specify a optionsPrice. This allows us to maintain multiple
    // instances of this class on a single page.
    
	Product.GroupedConfig = Class.create(Product.Config, {
		initialize: function(config, productId){
	        this.config     = config;
	        this.taxConfig  = this.config.taxConfig;
	        this.settings   = $$('#product_addtocart_form_' + productId + ' .super-attribute-select');
	        this.state      = new Hash();
	        this.priceTemplate = new Template(this.config.template);
	        this.prices     = config.prices;
	
	        this.settings.each(function(element){
	            Event.observe(element, 'change', this.configure.bind(this))
	        }.bind(this));
	
	        // fill state
	        this.settings.each(function(element){
	            var attributeId = element.id.replace(/[a-z]*/, '');
	            if(attributeId && this.config.attributes[attributeId]) {
	                element.config = this.config.attributes[attributeId];
	                element.attributeId = attributeId;
	                this.state[attributeId] = false;
	            }
	        }.bind(this))
	
	        // Init settings dropdown
	        var childSettings = [];
	        for(var i=this.settings.length-1;i>=0;i--){
	            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
	            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
	            if(i==0){
	                this.fillSelect(this.settings[i])
	            }
	            else {
	                this.settings[i].disabled=true;
	            }
	            $(this.settings[i]).childSettings = childSettings.clone();
	            $(this.settings[i]).prevSetting   = prevSetting;
	            $(this.settings[i]).nextSetting   = nextSetting;
	            childSettings.push(this.settings[i]);
	        }
	
	        // try retireve options from url
	        var separatorIndex = window.location.href.indexOf('#');
	        if (separatorIndex!=-1) {
	            var paramsStr = window.location.href.substr(separatorIndex+1);
	            this.values = paramsStr.toQueryParams();
	            this.settings.each(function(element){
	                var attributeId = element.attributeId;
	                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
	                this.configureElement(element);
	            }.bind(this));
	        }
    	},
		setOptionsPrice: function(optionsPrice){
			this.optionsPrice = optionsPrice;
		},
		reloadPrice: function() {
			var price = 0;
	        for(var i=this.settings.length-1;i>=0;i--){
	            var selected = this.settings[i].options[this.settings[i].selectedIndex];
	            if(selected.config){
	                price += parseFloat(selected.config.price);
	            }
	        }
	
	        this.optionsPrice.changePrice('config', price);
	        this.optionsPrice.reload();
	
	        return price;	
	  	}
	});	