function save_sku_qty(t) {
	var e = t.replace(/\./g, "\\."),
	a = parseInt(jQuery("#" + e).val());
    //console.log("a="+a);
    //console.log('save_sku_qty sku_info='+Object.toJSON(sku_info));
    //console.log('save_sku_qty qtylist1='+Object.toJSON(qtylist));
    //console.log('save_sku_qty skus_to_load='+Object.toJSON(skus_to_load));
    //console.log('save_sku_qty total_price='+total_price);
	qtylist[t] = {
		sku : t,
		qty : a
	},
	update_cart()
}
function update_cart() {
	total_price = 0;
	var total_quantity=0;
	var t = '<form action="/addcart?type=v200add&o_url=https%3A%2F%2Fwww%2Ecshardware.com&createsessioncookie=1&noredirect=1&" method="post">';
	t += '<table class="cart_table table table-condensed" id="cart_table"><caption>Shopping list</caption><tbody><tr><th class="qty_th" scope="col">Qty</th><th class="sku_th" scope="col">SKU</th><th class="price_th" scope="col">Price</th></tr>';
	for (var e in qtylist)
        qtylist[e].qty > 0 && (t += get_tr_from_sku(qtylist[e].sku, qtylist[e].qty)) && (total_quantity +=qtylist[e].qty);    
	t += '<tr><td></td><td class="total_td text_bold">Total:</td><td class="total_price_td text_bold">$' + total_price.toFixed(2) + "</td></tr>",
	t += '<tr><td colspan="3"><button class="btn btn-sm btn-clear pull-left" onclick="reset_page()" type="reset"><span class="glyphicon glyphicon-refresh"></span>Clear List</button><button class="btn btn-sm btn-primary pull-right" type="submit"><span class="glyphicon glyphicon-shopping-cart"></span>Add to Cart</button></td></tr>',
	t += "</tbody></table>",
	t += '<br /><!--<div><input type="reset" onclick="reset_page()" value="Clear List" class="btn_addtocart chrome"><input type="submit" value="Add to Cart" class="btn_addtocart chrome"></div>-->',
	t += '</form><div id="cart_div_footer">&nbsp;</div>';
    //total_price > 0 && (jQuery("#cart_div").removeClass("hidden"), jQuery("#cart_div").addClass("bordered"), jQuery("#cart_div").html(t)),
	if((total_price > 0) && (total_quantity > 0)) {
        jQuery("#cart_div").removeClass("hidden"), jQuery("#cart_div").addClass("bordered"), jQuery("#cart_div").html(t);
    }else if(total_quantity > 0){
       //jQuery("#cart_div").removeClass("hidden"), jQuery("#cart_div").addClass("bordered"), jQuery("#cart_div").html(t);
    }else{
       jQuery("#cart_div").addClass("hidden"),jQuery("#cart_div").html(''); 
       sku_info = {}, qtylist = {}, skus_to_load = [], total_price = 0;
    }    
	jQuery("#going_to_cart").addClass('going_visible'),
	jQuery("#going_to_cart #cart_total_quantity_value").text(total_quantity),
	flush_pending_skus()
}
function reset_page() {
	qtylist.length = 0,
	update_cart(),
	location.reload(!0)
}
function flush_pending_skus() {
	for (var t, e = ""; skus_to_load.length > 0; )
		t = skus_to_load.pop(), e += e ? "|" + t : t;
	jQuery(document).ajaxStart(function(){
                            jQuery("div.progress-image").show();
                         }); 
	e && jQuery.get("/ssajax", {
		rb_id : "0AFBE47B797546FFB51B455F9E12553A",
		sku : e
	}, function (e) {
		jQuery("record", e).each(function () {
			var e = jQuery(this);
			t = e.find("sku").text(),
			sku_info[t] = {},
			sku_info[t].p_key = e.find("p_key").text(),
			sku_info[t].retail_price = parseFloat(e.find("retail_price").text()),
			sku_info[t].qty = ""
		}),
		jQuery(document).ajaxStop(function(){
                             jQuery('div.progress-image').hide();
                         });  
		update_cart()
	})
}
function get_tr_from_sku(t, e) {
	if (sku_info[t]) {
		e = parseInt(e);
		var a = sku_info[t].retail_price,
		s = a.toFixed(2),
		r = (a * e).toFixed(2),
		i = sku_info[t].p_key;
        if((a > 0) && (e > 0))total_price += a * e;
		return total_price,
		'<tr><td class="qty_td">' + e + '</td><td class="sku_td">' + t + "&nbsp;&nbsp;<small>@&nbsp;$" + s + '&nbsp;ea.</small></td><td class="price_td">$' + r + '<input type="hidden" name="keys" value="' + i + '" /><input type="hidden" name="qty[' + i + ']" value="' + e + '" /></td></tr>'
	}
	return skus_to_load.push(t),
	'<tr><td class="qty_td">' + e + '</td><td class="sku_td">' + t + "</td><td>&nbsp;</td></tr>"
}
function NumbersOnly(t, e) {
	var a,
	s;
	if (window.event)
		a = window.event.keyCode;
	else {
		if (!e)
			return !0;
		a = e.which
	}
	return s = String.fromCharCode(a),
	null == a || 0 == a || 8 == a || 9 == a || 27 == a ? !0 : 13 == a || 32 == a ? !1 : "." == s && t.value.indexOf(".") > -1 ? !1 : "0123456789".indexOf(s) > -1 ? !0 : !1
}
function getSkuFromData(t) {
	return t
}
var sku_info = {}, qtylist = {}, skus_to_load = [], total_price = 0;

