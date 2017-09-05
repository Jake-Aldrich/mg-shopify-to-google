<?php
// This includes gives us all the WordPress functionality
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
ini_set("default_charset", 'utf-8');

$fh = fopen( dirname( __FILE__ ) . '/products_export.csv', 'r+');

while(! feof($fh)) {
	$row = fgetcsv($fh);
	$currentItem = array();
	//check if this is the header row
	if ($row[0] == 'Handle') {
		$shopifyHeaders = $row;
	} elseif ($row[0]) {
		//add the headers as keys to the row array
		$row = array_combine($shopifyHeaders, $row);

		//create parent SKU
		$skuParts = explode('-', $row['Variant SKU']);
		$parentSKU = $skuParts[0] . "-" . $skuParts[1];

		//set product material
		switch ($skuParts[0]) {
			case '1004':
			case '8860':
			case '8868':
			case 'B2000':
			case 'C3560':
				$material = 'Cotton';
				break;
			
			case '4400':
			case '4411':
			case '3001':
			case '562B':
			case '562M':
			case '996M':
			case '996Y':
				$material = 'Cotton/Polyester';
				break;

			case 'B98902':
				$material = 'Linen';
				break;

			default:
				$material = 'Cotton/Polyester/Rayon';
				break;
		}
		//set color and size for single variant items
		if ($row['Option1 Name'] == 'Title') {
			switch($skuParts[0]) {
				case '8860':
				case '8868':
				case'B98902':
					$row['Option1 Value'] = 'Natural';
					break;
				case'C3560':
					$row['Option1 Value'] = 'White';
					break;
			}
			$row['Variant Image'] = $row['Image Src'];
		}
		if ($row['Option2 Value'] == '') {
			$row['Option2 Value'] = 'One Size';
		}

		//fill in fields left blank by Shopify
		if (!$row['Title']) {
			$row['Title'] = $previousRow['Title'];
			$row['Body (HTML)'] = $previousRow['Body (HTML)'];
			$row['Google Shopping / AdWords Grouping'] = $previousRow['Google Shopping / AdWords Grouping'];
			$row['Google Shopping / Google Product Category'] = $previousRow['Google Shopping / Google Product Category'];
			$row['Google Shopping / Gender'] = $previousRow['Google Shopping / Gender'];
			$row['Google Shopping / Age Group'] = $previousRow['Google Shopping / Age Group'];
		}

		//check if the product is on sale
		if (intval($row['Variant Price']) < intval($row['Variant Compare At Price'])) {
			$price = $row['Variant Compare At Price'] . ' USD';
			$salePrice = $row['Variant Price'] . ' USD';
		} else {
			$price = $row['Variant Price'] . ' USD';
			$salePrice = '';
		}
		//begin converting for Google
		$currentItem['id'] = $row['Variant SKU'];
		$currentItem['title'] = $row['Title'] . " " . $row['Option1 Value'] . " " . $row['Option2 Value'];
		$currentItem['description'] = preg_replace( "/\r|\n/", "", strip_tags($row['Body (HTML)']));
		$currentItem['link'] = "https://www.momentgear.com/products/" . $row['Handle'];
		$currentItem['condition'] = 'New';
		$currentItem['price'] = $price;
		$currentItem['sale price'] = $salePrice;
		$currentItem['mpn'] = $row['Variant SKU'];
		$currentItem['shipping weight'] = round(intval($row['Variant Grams'])/453.592, 2) . " lb";
		$currentItem['item group id'] = $parentSKU;
		$currentItem['brand'] = 'Moment Gear';
		$currentItem['product type'] = $row['Google Shopping / AdWords Grouping'];
		$currentItem['google product category'] = $row['Google Shopping / Google Product Category'];
		$currentItem['image link'] = $row['Variant Image'];
		$currentItem['identifier exists'] = 'False';
		$currentItem['availability'] = 'In Stock';
		$currentItem['gender'] = $row['Google Shopping / Gender'];
		$currentItem['age group'] = $row['Google Shopping / Age Group'];
		$currentItem['color'] = $row['Option1 Value'];
		$currentItem['size'] = $row['Option2 Value'];
		$currentItem['material'] = $material;

		//Add headers if this is the first time through the loop
		if(!isset($googleFeed)) {
			$googleFeed = array();
			$googleFeed[] = array_keys($currentItem);
		}
		$googleFeed[] = $currentItem;
		$previousRow = $row;
	}
}


$fileName = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/wpallimport/files/mg-shopify-to-google-feed.txt";

$fp = fopen($fileName, 'w');
foreach($googleFeed as $fields) {
	 //fputcsv($fp, $fields);
	$rowString = implode("\t", $fields);
	fwrite($fp, $rowString . "\r\n");
}
fflush ($fp);
fclose($fp);

$referer = $_SERVER['HTTP_REFERER'];
header("Location: $referer");

?>