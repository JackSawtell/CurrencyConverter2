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

# check PARAM values match the keys in $GET
if (count(array_intersect(PARAMS, array_keys($_GET))) < 4) {
    echo generate_error(1000, $_GET['format']); 
    exit();
}
# show error if there are to many params
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


# xpath the codes of the rates which are live
#$from = $_GET['from'];
#
#$from_currency = (string)$rates[0]->cname;
#$from_location = (string)$rates[0]->cntry;
#$to = $_GET['to'];
#$to_rates = $xml->xpath("//code[.='$to']/parent::*");
#$to_currency = (string)$rates[0]->cname;
#$to_location = (string)$rates[0]->cntry;
# get the timestamp
#$pull_timestamp =(string)$rates[0]->at;




# load the rates file as a simple xml object
$xml=simplexml_load_file('rates.xml');
# xpath the codes of the rates which are live
$rates = $xml->xpath("//rate[@live='1']/@code");

# create a php array of these codes
foreach ($rates as $key=>$val) {$codes[] =(string) $val;}

# printing the data
# get the to and from rates
$fr = $xml->xpath("//code[@rate='" . $_GET['from'] . "']/@rate")[0]['rate'];
$tr = $xml->xpath("//code[@rate='" . $_GET['to'] . "']/@rate")[0]['rate'];
# if to and from are the same - set rate to 1.00
if ($_GET['from']==$_GET['to']) {
	$rate = 1.00;
	$conv =  $_GET['amnt'];
}
else {
	# calculate relative conversion rate
	$rate = floatval($tr) / floatval($fr);
	
	# calculate the conversion
	$conv = $rate * $_GET['amnt'];
}

# build an array to send to the response function
$curr = simplexml_load_file('currencies.xml');

#get the timestamp (ts) from the rates file & format it
$reply['date_time'] = date('d M Y H:i', (string) $xml->xpath("/rates/@ts")[0]);

# get the rate
$rates = $xml->xpath("//code[.='$from']/parent::*");
$reply['rate'] = $rate;

$reply['from_code'] = $_GET['from'];
$reply['from_curr'] = (string) $curr->xpath("//currency/cname[../ccode='". $_GET['from'] . "']")[0];
$reply['from_loc'] = (string) $curr->xpath("//currency/cntry[../ccode='". $_GET['from'] . "']")[0];
$reply['from_amnt'] = $_GET['amnt'];

$reply['to_code'] = $_GET['to'];
$reply['to_curr'] = (string) $curr->xpath("//currency/cname[../ccode='". $_GET['to'] . "']")[0];
$reply['to_loc'] = (string) $curr->xpath("//currency/cntry[../ccode='". $_GET['to'] . "']")[0];
$reply['to_amnt'] = $conv;

# make the response xml
$response = response_xml($reply);

# print the xml header and content
if ($_GET['format']=='json') {
	$json = simplexml_load_string('<conv>'.$response.'</conv>');
	header('Content-Type: application/json');
	echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
else {
	header('Content-Type: text/xml');
	$dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->loadXML ('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $response);
    $dom->formatOutput = true;
	echo (string) $dom->saveXML();
}
exit;

echo 'donelll';
?>