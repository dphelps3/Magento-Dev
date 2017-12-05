// JavaScript Document

jQuery(function() {
"use strict";
    function log_modal_event(event, modal) {
      if(typeof console != 'undefined' && console.log) console.log("[event] " + event.type)
    }
	
	jQuery('a[href="#vidModal"]').click(function(event) {
      event.preventDefault();
      jQuery(this).modal({
        fadeDuration: 200 });
    });
	
	
});


(function(jQuery) {

  var current = null;

  jQuery.modal = function(el, options) {
    jQuery.modal.close(); // Close any open modals.
    var remove, target;
    this.jQuerybody = jQuery('body');
    this.options = jQuery.extend({}, jQuery.modal.defaults, options);
    this.options.doFade = !isNaN(parseInt(this.options.fadeDuration, 10));
    if (el.is('a')) {
      target = el.attr('href');
      //Select element by id from href
      if (/^#/.test(target)) {
        this.jQueryelm = jQuery(target);
        if (this.jQueryelm.length !== 1) return null;
        this.open();
      //AJAX
      } else {
        this.jQueryelm = jQuery('<div>');
        this.jQuerybody.append(this.jQueryelm);
        remove = function(event, modal) { modal.elm.remove(); };
        this.showSpinner();
        el.trigger(jQuery.modal.AJAX_SEND);
        jQuery.get(target).done(function(html) {
          if (!current) return;
          el.trigger(jQuery.modal.AJAX_SUCCESS);
          current.jQueryelm.empty().append(html).on(jQuery.modal.CLOSE, remove);
          current.hideSpinner();
          current.open();
          el.trigger(jQuery.modal.AJAX_COMPLETE);
        }).fail(function() {
          el.trigger(jQuery.modal.AJAX_FAIL);
          current.hideSpinner();
          el.trigger(jQuery.modal.AJAX_COMPLETE);
        });
      }
    } else {
      this.jQueryelm = el;
      this.jQuerybody.append(this.jQueryelm);
      this.open();
    }
  };

  jQuery.modal.prototype = {
    constructor: jQuery.modal,

    open: function() {
      var m = this;
      if(this.options.doFade) {
        this.block();
        setTimeout(function() {
          m.show();
        }, this.options.fadeDuration * this.options.fadeDelay);
      } else {
        this.block();
        this.show();
      }
      if (this.options.escapeClose) {
        jQuery(document).on('keydown.modal', function(event) {
          if (event.which == 27) jQuery.modal.close();
        });
      }
      if (this.options.clickClose) this.blocker.click(jQuery.modal.close);
    },

    close: function() {
      this.unblock();
      this.hide();
      jQuery(document).off('keydown.modal');
	  jQuery('#youtube_player')[0].contentWindow.postMessage('{"event":"command","func":"' + 'stopVideo' + '","args":""}', '*');
    },

    block: function() {
      var initialOpacity = this.options.doFade ? 0 : this.options.opacity;
      this.jQueryelm.trigger(jQuery.modal.BEFORE_BLOCK, [this._ctx()]);
      this.blocker = jQuery('<div class="jquery-modal blocker"></div>').css({
        top: 0, right: 0, bottom: 0, left: 0,
        width: "100%", height: "100%",
        position: "fixed",
        zIndex: this.options.zIndex,
        background: this.options.overlay,
        opacity: initialOpacity
      });
      this.jQuerybody.append(this.blocker);
      if(this.options.doFade) {
        this.blocker.animate({opacity: this.options.opacity}, this.options.fadeDuration);
      }
      this.jQueryelm.trigger(jQuery.modal.BLOCK, [this._ctx()]);
    },

    unblock: function() {
      if(this.options.doFade) {
        this.blocker.fadeOut(this.options.fadeDuration, function() {
          jQuery(this).remove();
        });
      } else {
        this.blocker.remove();
      }
    },

    show: function() {
      this.jQueryelm.trigger(jQuery.modal.BEFORE_OPEN, [this._ctx()]);
      if (this.options.showClose) {
        this.closeButton = jQuery('<a href="#close-modal" rel="modal:close" class="close-modal ' + this.options.closeClass + '">' + this.options.closeText + '</a>');
        this.jQueryelm.append(this.closeButton);
      }
      this.jQueryelm.addClass(this.options.modalClass + ' current');
      this.center();
      if(this.options.doFade) {
        this.jQueryelm.fadeIn(this.options.fadeDuration);
      } else {
        this.jQueryelm.show();
      }
      this.jQueryelm.trigger(jQuery.modal.OPEN, [this._ctx()]);
    },

    hide: function() {
      this.jQueryelm.trigger(jQuery.modal.BEFORE_CLOSE, [this._ctx()]);
      if (this.closeButton) this.closeButton.remove();
      this.jQueryelm.removeClass('current');

      if(this.options.doFade) {
        this.jQueryelm.fadeOut(this.options.fadeDuration);
      } else {
        this.jQueryelm.hide();
      }
      this.jQueryelm.trigger(jQuery.modal.CLOSE, [this._ctx()]);
    },

    showSpinner: function() {
      if (!this.options.showSpinner) return;
      this.spinner = this.spinner || jQuery('<div class="' + this.options.modalClass + '-spinner"></div>')
        .append(this.options.spinnerHtml);
      this.jQuerybody.append(this.spinner);
      this.spinner.show();
    },

    hideSpinner: function() {
      if (this.spinner) this.spinner.remove();
    },

    center: function() {
      this.jQueryelm.css({
        position: 'fixed',
        top: "30%",
        left: "15%",
        //marginTop: - (this.jQueryelm.outerHeight() / 2),
        //marginLeft: - (this.jQueryelm.outerWidth() / 2),
        zIndex: this.options.zIndex + 1
      });
    },

    //Return context for custom events
    _ctx: function() {
      return { elm: this.jQueryelm, blocker: this.blocker, options: this.options };
    }
  };

  //resize is alias for center for now
  jQuery.modal.prototype.resize = jQuery.modal.prototype.center;

  jQuery.modal.close = function(event) {
    if (!current) return;
    if (event) event.preventDefault();
    current.close();
    var that = current.jQueryelm;
    current = null;
    return that;
  };

  jQuery.modal.resize = function() {
    if (!current) return;
    current.resize();
  };

  // Returns if there currently is an active modal
  jQuery.modal.isActive = function () {
    return current ? true : false;
  }

  jQuery.modal.defaults = {
    overlay: "#999",
    opacity: 0.65,
    zIndex: 20,
    escapeClose: true,
    clickClose: true,
    closeText: 'Close',
    closeClass: '',
    modalClass: "modal",
    spinnerHtml: null,
    showSpinner: true,
    showClose: true,
    fadeDuration: null,   // Number of milliseconds the fade animation takes.
    fadeDelay: 0.5        // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
  };

  // Event constants
  jQuery.modal.BEFORE_BLOCK = 'modal:before-block';
  jQuery.modal.BLOCK = 'modal:block';
  jQuery.modal.BEFORE_OPEN = 'modal:before-open';
  jQuery.modal.OPEN = 'modal:open';
  jQuery.modal.BEFORE_CLOSE = 'modal:before-close';
  jQuery.modal.CLOSE = 'modal:close';
  jQuery.modal.AJAX_SEND = 'modal:ajax:send';
  jQuery.modal.AJAX_SUCCESS = 'modal:ajax:success';
  jQuery.modal.AJAX_FAIL = 'modal:ajax:fail';
  jQuery.modal.AJAX_COMPLETE = 'modal:ajax:complete';

  jQuery.fn.modal = function(options){
    if (this.length === 1) {
      current = new jQuery.modal(this, options);
    }
    return this;
  };

  // Automatically bind links with rel="modal:close" to, well, close the modal.
  jQuery(document).on('click.modal', 'a[rel="modal:close"]', jQuery.modal.close);
  jQuery(document).on('click.modal', 'a[rel="modal:open"]', function(event) {
    event.preventDefault();
    jQuery(this).modal();
  });
})(jQuery);
