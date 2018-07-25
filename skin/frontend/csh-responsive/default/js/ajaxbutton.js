if (typeof Dphelps == 'undefined') {
    var Dphelps = Class.create();
}

Dphelps.AjaxButton = Class.create();
Dphelps.AjaxButton.prototype = {
    initialize: function(url) {
        this.url = url;
    },

    send: function(price) {
        var request = new Ajax.Updater('ajax-updater' this.url, {
            parameters: { price: price },
            onLoading: function() {
                this.container = $('ajax-updater');
                this.container.addClassName('disabled');
                this.container.setStyle({ opacity: .5 });
                Element.show('please-wait');
            },
            onComplete: function() {
                this.container.removeClassName('disabled');
                this.container.setStyle({ opacity: 1 });
            }
        });
    }
}