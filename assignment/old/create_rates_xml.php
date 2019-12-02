<?php
# always include this line
@date_default_timezone_set("GMT"); 
#declare the currency array
$ccodes = array(
   'AUD','BRL','CAD','CHF',
   'CNY','DKK','EUR','GBP',
   'HKD','HUF','INR','JPY',
   'MXN','MYR','NOK','NZD',
   'PHP','RUB','SEK','SGD',
   'THB','TRY','USD','ZAR');
# pull the rates json file (USE YOUR OWN API KEY)
$json_rates = file_get_contents('http://data.fixer.io/api/latest?access_key=e3826543064ffb43cf65f36db1acf611')
			  or die("Error: Cannot load JSON file from fixer");
#decode the json to a php object
$rates = json_decode($json_rates);
# calculate the GBP ratio

// code help from c23-day
foreach($rates->rates as $live_rates=>$val){
	if (in_array($live_rates, $ccodes) ){
		$live_array[$live_rates] = 1;
	}else{
		$live_array[$live_rates] = 0;
	}
}

$gbp_rate = 1/ $rates->rates->GBP;
# pull our currencies file into a simplexml object
$xml=simplexml_load_file('currencies.xml') or die("Error: Cannot load currencies file");
# start and initialize the writer
$writer = new XMLWriter();
$writer->openURI('rates.xml');
//indenting xml
$writer->setIndent(true);
$writer->startDocument("1.0");
$writer->startElement("currencies");
$writer->writeAttribute('base', 'GBP');
# for every currency code in our array
# select its parent + subnodes and write
# them out after tidying up the countries list
foreach ($ccodes as $code) {
	if (isset($rates->rates->$code)) {
	
		$nodes = $xml->xpath("//ccode[.='$code']/parent::*");
		
		$writer->startElement("currency");
			$writer->startElement("code");
			$writer->writeAttribute('rate', $rates->rates->$code * $gbp_rate);
			$writer->writeAttribute('live', $live_array[$code]);
			$writer->text($code);
			$writer->endElement();
		
			$writer->startElement("cname");
			$writer->text($nodes[0]->cname);
			$writer->endElement();
		
			$writer->startElement("cntry");
						
			# tidy up countries node
			$cntry = trim(preg_replace('/[\t\n\r\s]+/', ' ', $nodes[0]->cntry));
			$wrong = array("Of", "And", "U.s.", "(The)", " , ");
			$right = array("of", "and", "U.S.", "", ", ");
			$cn = str_replace($wrong, $right, $cntry);
			$writer->text($cn);
			$writer->endElement();
		$writer->endElement();
	}
}
$writer->endDocument();
$writer->flush();
echo "All done ....!";
?>

