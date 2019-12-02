<?php
require_once('functions.php');
require_once('config.php');

if (!isset($_GET['format']) || empty($_GET['format'])) {
	$_GET['format'] = 'xml';
}

if (!file_exists('currencies.xml')) {
	create_currencies();
	exit;
}

if (!file_exists('rates.xml')) {
	create_rates();
	exit;
}

# ensure PARAM values match the keys in $GET
if (count(array_intersect(PARAMS, array_keys($_GET))) < 4) {
    echo generate_error(1000, $_GET['format']); 
    exit();
}
# ensure no extra params
if (count($_GET) > 4) {
	echo generate_error(1100, $_GET['format']); 
	exit();
}
# $to and $from are not recognized currencies
if (!in_array($_GET['to'], DEFAULT_CODES) || !in_array($_GET['from'],DEFAULT_CODES)) {
    echo generate_error(1200, $_GET['format']);
	exit;
}
# $amnt is not a two digit decimal value (can be integer)
if (!preg_match('/^\d+(\.\d{1,2})?$/', $_GET['amnt'])) {
	echo generate_error(1300, $_GET['format']);
	exit;
}

# check for allowed format values
if (!in_array( $_GET['format'], FORMAT_CHECK)) {
	echo generate_error(1400);
	exit;
}

# validate parameter values
# load the rates file as a simple xml object
$xml=simplexml_load_file('rates.xml');

# xpath the codes of the rates which are live
$from = $_GET['from'];
$rates = $xml->xpath("//code[.='$from']/parent::*");
$from_currency = (string)$rates[0]->cname;
$from_location = (string)$rates[0]->cntry;
$to = $_GET['to'];
$to_rates = $xml->xpath("//code[.='$to']/parent::*");
$to_currency = (string)$rates[0]->cname;
$to_location = (string)$rates[0]->cntry;


# create a php array of these codes
//foreach ($rates as $key=>$val) {$codes[] =(string) $val;}
echo 'donelll';
?>