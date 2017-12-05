<?php
$mysqli = new mysqli("127.0.0.1", "sync_tool", "zK5x43g0225p2H99tQYB", "cshardwa_production");
$res = $mysqli->query("SELECT entity_id, name, sku, price, small_image FROM csmagcatalog_product_flat_1 WHERE sku LIKE '8.%' OR sku LIKE 'CAB.%' OR sku LIKE '50.%' OR sku LIKE '52.%'");
if( file_exists('product.csv') ){
	unlink('product.csv');
}

$out = array();

while ($row = $res->fetch_assoc()) {
	
		$query = "SELECT a.value FROM csmageav_attribute_option_value as a LEFT JOIN csmagcatalog_product_entity_int as b ON a.option_id=b.value WHERE entity_id=" . $row['entity_id'] . " AND attribute_id=415";
		$data = $mysqli->query($query);
		
		//if($data) {
			$info = $data->fetch_assoc();
			$row['door'] = $info['value'];
		//}
		
		$query = "SELECT a.value FROM csmageav_attribute_option_value as a LEFT JOIN csmagcatalog_product_entity_int as b ON a.option_id=b.value WHERE entity_id=" . $row['entity_id'] . " AND attribute_id=416";
		$data = $mysqli->query($query);
		$info = $data->fetch_assoc();
		$row['drawer'] = $info['value'];
		
		$query = "SELECT * FROM csmagcatalog_product_entity_varchar WHERE entity_id=" . $row['entity_id'] . " AND attribute_id=414";
		$data = $mysqli->query($query);
				
		//if($data) {			
			$info = $data->fetch_assoc();
			$row['cabinet_finish'] = $info['value'];
		//}
		
		$out[] = $row;	
}

$out = sortByProp($out, 'sku');

$fh = fopen('product.csv', 'a');
fwrite($fh, "sku,name,price,image,finish,door,drawer\n");
foreach($out as $row) {
	if(strpos($row['sku'], 'CAB.') === 0 || strpos($row['sku'], '50.') === 0 || strpos($row['sku'], '52.') === 0) { // Cabinet Series product
		fwrite($fh, $row['sku'] . ',' . $row['name'] . ',' . $row['price'] . ',' . $row['small_image'] . ',' . $row['cabinet_finish'] . ',' . $row['door'] . ',' . $row['drawer'] . "\n");
	} else {
		fwrite($fh, $row['sku'] . ',,' . $row['price'] . ',' . $row['small_image'] . ",,,\n"); // Regular cabinet product
	}
}

fclose($fh);

function sortByProp($array, $propName, $reverse = false)
{
    $sorted = array();
    foreach ($array as $item)
    {
        $sorted[$item->$propName][] = $item;
    }
    if ($reverse) krsort($sorted); else ksort($sorted);
    $result = array();
    foreach ($sorted as $subArray) foreach ($subArray as $item)
    {
        $result[] = $item;
    }
    return $result;
}
?>

