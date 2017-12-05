<?php
//send updates to Magento

mysql_connect("localhost:3306","cshardwa","T2l8T1170");
mysql_select_db("cshardwa_production");

$id = mysql_real_escape_string($_POST["order_id"]);
$o_id = mysql_real_escape_string($_POST["o_id"]);
echo "<b>Edited Order # $id</b><br>";

//populate address information
$b_address = mysql_real_escape_string($_POST["b_address"]);
$b_city= mysql_real_escape_string($_POST["b_city"]);
$b_zip = mysql_real_escape_string($_POST["b_zip"]);
$b_state = mysql_real_escape_string($_POST["b_state"]);
$b_country = mysql_real_escape_string($_POST["b_country"]);
$b_company = mysql_real_escape_string($_POST["b_company"]);
$b_phone = mysql_real_escape_string($_POST["b_phone"]);
$s_address = mysql_real_escape_string($_POST["s_address"]);
$s_city= mysql_real_escape_string($_POST["s_city"]);
$s_zip = mysql_real_escape_string($_POST["s_zip"]);
$s_state = mysql_real_escape_string($_POST["s_state"]);
$s_country = mysql_real_escape_string($_POST["s_country"]);
$s_company = mysql_real_escape_string($_POST["s_company"]);
$s_phone = mysql_real_escape_string($_POST["s_phone"]);

$result = mysql_query("SELECT billing_address_id,shipping_address_id FROM csmagsales_flat_order WHERE increment_id='" . $id . "'");
$row = mysql_fetch_array($result);
$billing_key = $row['billing_address_id'];
$shipping_key = $row['shipping_address_id'];

$sql = "UPDATE csmagsales_flat_order_address SET region='" . $b_state . "',company='" . $b_company . "',telephone='" . $b_phone . "',postcode='" . $b_zip . "',street='" . $b_address . "',city='" . $b_city . "',country_id='" . $b_country . "' WHERE entity_id=" . $billing_key;
mysql_query($sql);

$sql = "UPDATE csmagsales_flat_order_address SET region='" . $s_state . "',company='" . $s_company . "',telephone='" . $s_phone . "',postcode='" . $s_zip . "',street='" . $s_address . "',city='" . $s_city . "',country_id='" . $s_country . "' WHERE entity_id=" . $shipping_key;
mysql_query($sql);

$sql = "UPDATE csmagsales_flat_order SET status='pending',state='new' WHERE increment_id='" . $id . "'";
mysql_query($sql);

$sql = "UPDATE csmagsales_flat_order_grid SET status='pending' WHERE increment_id='" . $id . "'";
mysql_query($sql);


//sku adjustment
$sku0 = mysql_real_escape_string($_POST["sku0"]);
$n_sku0 = mysql_real_escape_string($_POST["n_sku0"]);
if(sku0<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku0 . "' WHERE sku='" . $sku0 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku1 = mysql_real_escape_string($_POST["sku1"]);
$n_sku1 = mysql_real_escape_string($_POST["n_sku1"]);
if(sku1<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku1 . "' WHERE sku='" . $sku1 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku2 = mysql_real_escape_string($_POST["sku2"]);
$n_sku2 = mysql_real_escape_string($_POST["n_sku2"]);
if(sku2<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku2 . "' WHERE sku='" . $sku2 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku3 = mysql_real_escape_string($_POST["sku3"]);
$n_sku3 = mysql_real_escape_string($_POST["n_sku3"]);
if(sku3<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku3 . "' WHERE sku='" . $sku3 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku4 = mysql_real_escape_string($_POST["sku4"]);
$n_sku4 = mysql_real_escape_string($_POST["n_sku4"]);
if(sku4<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku4 . "' WHERE sku='" . $sku4 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku5 = mysql_real_escape_string($_POST["sku5"]);
$n_sku5 = mysql_real_escape_string($_POST["n_sku5"]);
if(sku5<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku5 . "' WHERE sku='" . $sku5 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku6 = mysql_real_escape_string($_POST["sku6"]);
$n_sku6 = mysql_real_escape_string($_POST["n_sku6"]);
if(sku6<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku6 . "' WHERE sku='" . $sku6 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku7 = mysql_real_escape_string($_POST["sku7"]);
$n_sku7 = mysql_real_escape_string($_POST["n_sku7"]);
if(sku7<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku7 . "' WHERE sku='" . $sku7 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku8 = mysql_real_escape_string($_POST["sku8"]);
$n_sku8 = mysql_real_escape_string($_POST["n_sku8"]);
if(sku8<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku8 . "' WHERE sku='" . $sku8 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku9 = mysql_real_escape_string($_POST["sku9"]);
$n_sku9 = mysql_real_escape_string($_POST["n_sku9"]);
if(sku9<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku9 . "' WHERE sku='" . $sku9 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku10 = mysql_real_escape_string($_POST["sku10"]);
$n_sku10 = mysql_real_escape_string($_POST["n_sku10"]);
if(sku10<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku10 . "' WHERE sku='" . $sku10 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku11 = mysql_real_escape_string($_POST["sku11"]);
$n_sku11 = mysql_real_escape_string($_POST["n_sku11"]);
if(sku11<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku11 . "' WHERE sku='" . $sku11 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku12 = mysql_real_escape_string($_POST["sku12"]);
$n_sku12 = mysql_real_escape_string($_POST["n_sku12"]);
if(sku12<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku12 . "' WHERE sku='" . $sku12 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku13 = mysql_real_escape_string($_POST["sku13"]);
$n_sku13 = mysql_real_escape_string($_POST["n_sku13"]);
if(sku13<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku13 . "' WHERE sku='" . $sku13 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku14 = mysql_real_escape_string($_POST["sku14"]);
$n_sku14 = mysql_real_escape_string($_POST["n_sku14"]);
if(sku14<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku14 . "' WHERE sku='" . $sku14 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku15 = mysql_real_escape_string($_POST["sku15"]);
$n_sku15 = mysql_real_escape_string($_POST["n_sku15"]);
if(sku15<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku15 . "' WHERE sku='" . $sku15 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku16 = mysql_real_escape_string($_POST["sku16"]);
$n_sku16 = mysql_real_escape_string($_POST["n_sku16"]);
if(sku16<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku16 . "' WHERE sku='" . $sku16 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku17 = mysql_real_escape_string($_POST["sku17"]);
$n_sku17 = mysql_real_escape_string($_POST["n_sku17"]);
if(sku17<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku17 . "' WHERE sku='" . $sku17 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku18 = mysql_real_escape_string($_POST["sku18"]);
$n_sku18 = mysql_real_escape_string($_POST["n_sku18"]);
if(sku18<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku18 . "' WHERE sku='" . $sku18 . " AND order_id=" . $o_id;
		mysql_query($sql);
}
$sku19 = mysql_real_escape_string($_POST["sku19"]);
$n_sku19 = mysql_real_escape_string($_POST["n_sku19"]);
if(sku19<>""){
		$sql = "UPDATE csmagsales_flat_order_item SET sku='" . $n_sku19 . "' WHERE sku='" . $sku19 . " AND order_id=" . $o_id;
		mysql_query($sql);
}

//delete temporary order file
// unlink($id . ".htm");

//post message

echo "<b>This order has been saved.</b>";

?>
