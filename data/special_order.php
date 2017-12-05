<?php
$mysqli = new mysqli("127.0.0.1", "cshardwa_prdbusr", "2oLg&XFR=DPG", "cshardwa_production");
$res = $mysqli->query("SELECT sku, attribute_id, value FROM csmagcatalog_product_entity AS a LEFT JOIN csmagcatalog_product_entity_varchar AS b ON a.entity_id = b.entity_id WHERE NOT value='' AND attribute_id = 82 OR attribute_id = 84");

if( file_exists('soproduct.csv') ){
	unlink('soproduct.csv');
}

$fh = fopen('soproduct.csv', 'a');
fwrite($fh, "sku,attribute_id,value\n");

while ($row = $res->fetch_assoc()) {
		$row['value'] = str_replace('"', "''", $row['value']);
		fwrite($fh, $row['sku'] . ',' . $row['attribute_id'] . ',"' . $row['value'] . '"' . "\n");
}

fclose($fh);

?>

