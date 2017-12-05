/* --- Mega Menu ---
 /* --- v 3.2  December 01st 2015
 By Billy Trinh with help of Louis Pham
 http://magestore.com  */


if (typeof jQuery != 'undefined') {
var mega = jQuery.noConflict();
var isMobile = navigator.userAgent.match(/iPhone|iPad|iPod/i) || navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Opera Mini/i) || navigator.userAgent.match(/IEMobile/i);
const eff_hover = 1;
const eff_animation = 2;
const eff_toggle = 3;
const Slide = 0;
const Blind = 1;

/********** Mega Menu **********/
var MEGAMENU = function(menu, arr_effect,change,arr,responsive){
    this.menu = menu;
    this.effect = parseInt(arr_effect[0]);
    this.mobile_effect = parseInt(arr_effect[1]);
    this.change =  change;
    this.arr = arr;
    this.responsive = parseInt(responsive);
    this.init();

};
MEGAMENU.prototype = {
    init: function () {
        this.option();
        this.setWidth(this.width_default);
        this.setPosition(this.width_default);
        this.categoriesLevel();
        this.categoriesDynamic();
        this.categoriesMobile();
        this.applyEvent();
        this.mobile();
    },
    option: function(){
        this.ms_level0 = this.menu.children('.ms-level0');
        this.label = this.ms_level0.children('.ms-label');
        this.submenu = this.ms_level0.children('.ms-submenu');
        this.sub_left = this.ms_level0.children('.sub_left');
        this.sub_right = this.ms_level0.children('.sub_right');
        this.categoryParent = this.ms_level0.find('.ms-submenu .ms-category-level .parent');
        this.dynamicParent = this.ms_level0.find('.ms-submenu .ms-category-dynamic .col-level .parent');
        this.anchor = this.menu.find('.anchor_mbmenu .anchor_mbmenu_text');
        this.mblabel = this.ms_level0.children('.mb-label');
        this.mbreturn = this.ms_level0.find('.mb-return');
        this.mblevel = this.ms_level0.find('.mb-submenu .mb-level-click');
        this.menu_id = this.menu.attr('id');
        this.width_default = this.menu.outerWidth();
    },
    applyEvent: function(){
        if(isMobile != null){
            this.toggle();
        }else {
            switch (this.effect) {
                case eff_animation:
                    this.slide();
                    break;
                case eff_toggle:
                    this.toggle();
                    break;
                default:
                    this.fade();
                    break;
            }
        }
    },
    updateScreen: function(){
        this.width_default = this.menu.outerWidth();
        this.setWidth(this.width_default);
        this.setPosition(this.width_default);
    },
    mobile: function(){
        var this_menu = this.menu;
        this.anchor.bind('click',function(){
            $_this = mega(this);
            if($_this.hasClass('flag')){
                this_menu.removeClass('active');
                $_this.removeClass('flag');
            }else{
                this_menu.addClass('active');
                $_this.addClass('flag');
            }
            return false;
        });

        switch (this.mobile_effect) {
            case Slide:
                this.mobileSlide();
                break;
            default:
                this.mobileBlind();
                break;
        }
    },
    fade: function(){
        this.ms_level0.bind('mouseenter', function () {
            var $_this = mega(this);
            $_this.addClass('active');
            $_this.children('.ms-submenu').stop().fadeIn(150);
        });
        this.ms_level0.bind('mouseleave', function () {
            var $_this = mega(this);
            $_this.removeClass('active');
            $_this.children('.ms-submenu').hide();
        });
    },
    slide: function(){
        this.ms_level0.bind('mouseenter', function () {
            var $_this = mega(this);
            $_this.addClass('active');
            $_this.children('.ms-submenu').stop().slideDown(150);
        });
        this.ms_level0.bind('mouseleave', function () {
            var $_this = mega(this);
            $_this.removeClass('active');
            $_this.children('.ms-submenu').hide();
        });
    },
    toggle:function(){
        var id = this.menu_id;
        var change = this.change;
        var responsive = this.responsive;
        this.label.bind('click', function () {
            var $_this = mega(this);
            if(!$_this.hasClass('anchor_text')){
                if ($_this.hasClass('flag')) {
                    $_this.removeClass('flag');
                    $_this.parent().removeClass('active');
                    $_this.parent().children('.ms-submenu').hide();
                } else {
                    mega('#'+id+' .ms-level0').removeClass('active');
                    mega('#'+id+' .ms-label').removeClass('flag');
                    mega('#'+id+' .ms-submenu').hide();
                    $_this.addClass('flag');
                    $_this.parent().addClass('active');
                    $_this.parent().children('.ms-submenu').slideDown(150);
                }
                if(mega(window).width() > change || !responsive){
                    return false;
                }else{
                    return true;
                }
            }
        });
    },
    mobileSlide: function(){
        var id = this.menu_id;
        mclick = this.mblabel;
        mclick.parent().children('.mb-submenu').removeClass('blind');
        mclick.parent().children('.mb-submenu').addClass('slide');
        this.mblabel.bind('click', function () {
            var $_this = mega(this);
            mega('#'+id+' .ms-level0').removeClass('mbactive');
            $_this.parent().addClass('mbactive');
            $_this.parent().children('.mb-submenu').animate({
                left: 0
            }, 300);
        });
        this.mbreturn.bind('click', function () {
            var $_this = mega(this);
            mclick.parent().children('.mb-submenu').animate({
                left: 100 + '%'
            }, 300, function () {
                mclick.parent().removeClass('mbactive');
            });
        });
    },
    mobileBlind: function () {
        var id = this.menu_id;
        this.mblabel.bind('click', function () {
            var $_this = mega(this);
            if ($_this.hasClass('glyphicon-minus')) {
                $_this.removeClass('glyphicon-minus');
                $_this.parent().removeClass('mbactive');
                $_this.parent().children('.mb-submenu').slideUp(200);
            } else {
                mega('#'+id+' .ms-level0').removeClass('mbactive');
                mega('#'+id+' .mb-label').removeClass('glyphicon-minus');
                mega('#'+id+' .mb-submenu').slideUp(200);
                $_this.addClass('glyphicon-minus');
                $_this.parent().addClass('mbactive');
                $_this.parent().children('.mb-submenu').slideDown(200);
            }
        });
    },
    setWidth: function(width_default){
        for (var i = 0; i < this.submenu.length; i++) {
            var width_value = parseInt(this.arr[i]) * width_default / 100 + 'px';
            var sub = this.submenu[i];
            mega(sub).css({
                width: width_value,
                top: mega(sub).parent().outerHeight() + mega(sub).parent().position().top + 'px'
            });
        }
    },
    setPosition: function(width_default){
        this.sub_left.each(function () {
            $_this = mega(this);
            if ($_this.hasClass('position_auto')) {
                var left_value = $_this.parent().position().left;
                if (($_this.outerWidth() + left_value) > width_default) {
                    left_value = width_default - $_this.outerWidth();
                }
                if (left_value < 0)
                    left_value = 0;
                $_this.css({
                    left: left_value + 'px'
                });
            } else {
                $_this.css({
                    left: 0
                });
            }
        });
        this.sub_right.each(function () {
            $_this = mega(this);
            if ($_this.hasClass('position_auto')) {
                var right_value = width_default - $_this.parent().position().left - $_this.parent().outerWidth();
                if (($_this.outerWidth() + right_value) > width_default) {
                    right_value = width_default - $_this.outerWidth();
                }
                if (right_value < 0)
                    right_value = 0;
                $_this.css({
                    right: right_value + 'px'
                });
            } else {
                $_this.css({
                    right: 0
                });
            }
        });
    },
    categoriesLevel: function(){
        if (this.categoryParent.length) {
            this.categoryParent.bind('mouseenter', function () {
                var $_this = mega(this);
                $_this.addClass('active');
            });
            this.categoryParent.bind('mouseleave', function () {
                var $_this = mega(this);
                $_this.removeClass('active');
            });

        }
    },
    categoriesDynamic: function(){
        if (this.dynamicParent.length){
            this.dynamicParent.bind('mouseenter', function () {
                var $_this = mega(this);
                var info = $_this.find('i.information');
                var parent = $_this.parentsUntil('.ms-submenu');
                var active_id = info.attr('title');
                parent.find('.col-level .parent').removeClass('active');
                $_this.addClass('active');
                parent.find('.ms-category-dynamic .col-dynamic').removeClass('active');
                parent.find('#'+active_id).addClass('active');

            });
        }
    },
    categoriesMobile: function(){
        if (this.mblevel.length){
            this.mblevel.bind('click', function () {
                var $_this = mega(this);
                if ($_this.hasClass('glyphicon-minus')) {
                    $_this.removeClass('glyphicon-minus');
                    $_this.parent().parent().removeClass('active');
                } else {
                    $_this.addClass('glyphicon-minus');
                    $_this.parent().parent().addClass('active');
                }

            });
        }
    },
}
/********** Left Mega Menu **********/
var LEFTMENU = function(menu,main_div, arr_effect,change,arr,responsive){
    this.menu = menu;
    this.main_div = main_div;
    this.effect = parseInt(arr_effect[0]);
    this.mobile_effect = parseInt(arr_effect[1]);
    this.change =  change;
    this.arr = arr;
    this.responsive = parseInt(responsive);
    this.init();

};
LEFTMENU.prototype = {
    init: function () {
        this.option();
        this.setWidth(this.width_default,this.main_width);
        this.categoriesLevel();
        this.categoriesDynamic();
        this.categoriesMobile();
        this.applyEvent();
        this.mobile();
    },
    option: function(){
        this.ms_level0 = this.menu.children('.msl-level0');
        this.label = this.ms_level0.children('.msl-label');
        this.submenu = this.ms_level0.children('.msl-submenu');
        this.sub_left = this.ms_level0.children('.sub_left');
        this.sub_right = this.ms_level0.children('.sub_right');
        this.categoryParent = this.ms_level0.find('.msl-submenu .ms-category-level .parent');
        this.dynamicParent = this.ms_level0.find('.msl-submenu .ms-category-dynamic .col-level .parent');
        this.anchor = this.menu.find('.anchor_mbmenu .anchor_mbmenu_text');
        this.mblabel = this.ms_level0.children('.mb-label');
        this.mbreturn = this.ms_level0.find('.mb-return');
        this.mblevel = this.ms_level0.find('.lmb-submenu .mb-level-click');
        this.width_default = this.menu.outerWidth();
        this.main_width = this.main_div.outerWidth();
        this.menu_id = this.menu.attr('id');
    },
    updateScreen: function(){
        this.width_default = this.menu.outerWidth();
        this.main_width = this.main_div.outerWidth();
        this.setWidth(this.width_default,this.main_width);
    },
    applyEvent: function(){
        if(isMobile != null){
            this.toggle();
        }else {
            switch (this.effect) {
                case eff_animation:
                    this.slide();
                    break;
                case eff_toggle:
                    this.toggle();
                    break;
                default:
                    this.fade();
                    break;
            }
        }
    },
    mobile: function(){
        var left_menu = this.menu;
        this.anchor.bind('click',function(){
            $_this = mega(this);
            if($_this.hasClass('flag')){
                left_menu.removeClass('active');
                $_this.removeClass('flag');
            }else{
                left_menu.addClass('active');
                $_this.addClass('flag');
            }
            return false;
        });

        switch (this.mobile_effect) {
            case Slide:
                this.mobileSlide();
                break;
            default:
                this.mobileBlind();
                break;
        }
    },
    fade: function(){
        this.ms_level0.bind('mouseenter', function () {
            var $_this = mega(this);
            $_this.addClass('active');
            $_this.children('.msl-submenu').stop().fadeIn(150);
        });
        this.ms_level0.bind('mouseleave', function () {
            var $_this = mega(this);
            $_this.removeClass('active');
            $_this.children('.msl-submenu').hide();
        });
    },
    slide: function(){
        this.ms_level0.bind('mouseenter', function () {
            var $_this = mega(this);
            $_this.addClass('active');
            $_this.children('.msl-submenu').stop().slideDown(150);
        });
        this.ms_level0.bind('mouseleave', function () {
            var $_this = mega(this);
            $_this.removeClass('active');
            $_this.children('.msl-submenu').hide();
        });
    },
    toggle:function(){
        var left_id = this.menu_id;
        var left_change = this.change;
        var left_responsive = this.responsive;
        this.label.bind('click', function () {
            var $_this = mega(this);
            if(!$_this.hasClass('anchor_text')){
                if ($_this.hasClass('flag')) {
                    $_this.removeClass('flag');
                    $_this.parent().removeClass('active');
                    $_this.parent().children('.msl-submenu').hide();
                } else {
                    mega('#'+left_id+' .msl-level0').removeClass('active');
                    mega('#'+left_id+' .msl-label').removeClass('flag');
                    mega('#'+left_id+' .msl-submenu').hide();
                    $_this.addClass('flag');
                    $_this.parent().addClass('active');
                    $_this.parent().children('.msl-submenu').slideDown(150);
                }
                if(mega(window).width() > left_change || !left_responsive){
                    return false;
                }else{
                    return true;
                }
            }
        });
    },
    mobileSlide: function(){
        var id = this.menu_id;
        left_click = this.mblabel;
        left_click.parent().children('.lmb-submenu').removeClass('blind');
        this.mblabel.bind('click', function () {
            var $_this = mega(this);
            mega('#'+id+' .msl-level0').removeClass('mbactive');
            $_this.parent().addClass('mbactive');
            $_this.parent().children('.lmb-submenu').animate({
                left: 0
            }, 300);
        });
        this.mbreturn.bind('click', function () {
            var $_this = mega(this);
            left_click.parent().children('.lmb-submenu').animate({
                left: 100 + '%'
            }, 300, function () {
                left_click.parent().removeClass('mbactive');
            });
        });
    },
    mobileBlind: function () {
        var left_id = this.menu_id;
        this.mblabel.bind('click', function () {
            var $_this = mega(this);
            if ($_this.hasClass('glyphicon-minus')) {
                $_this.removeClass('glyphicon-minus');
                $_this.parent().removeClass('mbactive');
                $_this.parent().children('.lmb-submenu').slideUp(200);
            } else {
                mega('#'+left_id+' .ms-level0').removeClass('mbactive');
                mega('#'+left_id+' .mb-label').removeClass('glyphicon-minus');
                mega('#'+left_id+' .lmb-submenu').slideUp(200);
                $_this.addClass('glyphicon-minus');
                $_this.parent().addClass('mbactive');
                $_this.parent().children('.lmb-submenu').slideDown(200);
            }
        });
    },
    setWidth: function(width_default,main_width){
        var left_width = main_width -  width_default;
        for (var i = 0; i < this.submenu.length; i++) {
            var width_value = parseInt(this.arr[i]) * left_width / 100 + 'px';
            var sub = this.submenu[i];
            mega(sub).css({
                width: width_value,
                left: width_default-1 + 'px'
            });
        }
    },
    categoriesLevel: function(){
        if (this.categoryParent.length) {
            this.categoryParent.bind('mouseenter', function () {
                var $_this = mega(this);
                $_this.addClass('active');
            });
            this.categoryParent.bind('mouseleave', function () {
                var $_this = mega(this);
                $_this.removeClass('active');
            });

        }
    },
    categoriesDynamic: function(){
        if (this.dynamicParent.length){
            this.dynamicParent.bind('mouseenter', function () {
                var $_this = mega(this);
                var parent = $_this.parentsUntil('.msl-submenu');
                var active_id = $_this.attr('href');
                parent.find('.col-level .parent').removeClass('active');
                $_this.addClass('active');
                parent.find('.ms-category-dynamic .col-dynamic').removeClass('active');
                parent.find('#'+active_id).addClass('active');

            });
        }
    },
    categoriesMobile: function(){
        if (this.mblevel.length){
            this.mblevel.bind('click', function () {
                var $_this = mega(this);
                if ($_this.hasClass('glyphicon-minus')) {
                    $_this.removeClass('glyphicon-minus');
                    $_this.parent().parent().removeClass('active');
                } else {
                    $_this.addClass('glyphicon-minus');
                    $_this.parent().parent().addClass('active');
                }

            });
        }
    },
}
}
