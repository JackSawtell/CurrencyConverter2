<?php
function create_rates(){
    # pull the rates json file
    $json_rates = file_get_contents('http://data.fixer.io/api/latest?access_key=e3826543064ffb43cf65f36db1acf611')
                    or die("Error: Cannot load JSON file from fixer");
    # decode the json to a php object
    $rates = json_decode($json_rates);
    # $timestamp = $rates->timestamp;
    
    # code help from c23-day
    foreach($rates->rates as $live_rates=>$val){
        if (in_array($live_rates, DEFAULT_CODES) ){
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
    $writer->writeAttribute('timestamp', $rates->timestamp);
    # for every currency code in our array
    # select its parent + subnodes and write
    # them out after tidying up the countries list
    foreach (DEFAULT_CODES as $code) {
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
}

function create_currencies(){
    # pull the ISO currencies file into a simplexml object
    $xml = simplexml_load_file('http://www.currency-iso.org/dam/downloads/lists/list_one.xml') 
            or die("Error: Cannot create object");

    $writer = new XMLWriter();
    $writer->openURI('currencies.xml');
    $writer->startDocument("1.0");
    $writer->startElement("currencies");

    # get all the currency codes
    $codes = $xml->xpath("//CcyNtry/Ccy");
    $ccodes = [];

    # make array with unique currency codes
    foreach ($codes as $code) {
        if (!in_array($code, $ccodes)) {
            $ccodes[] = (string) $code;
        }
    }

    foreach ($ccodes as $ccode) { 

        $nodes = $xml->xpath("//Ccy[.='$ccode']/parent::*");
        
        $cname =  $nodes[0]->CcyNm;
        
        # begin writing out the nodes & values
        $writer->startElement("currency");
            $writer->startElement("ccode");
            $writer->text($ccode);
            $writer->endElement();
            $writer->startElement("cname");
            $writer->text($cname);
            $writer->endElement();
            $writer->startElement("cntry");
            
                $last = count($nodes) - 1;
                
                # group countries together using the same code
                # & lowercase first letter in name
                foreach ($nodes as $index=>$node) {
                    $writer->text(mb_convert_case($node->CtryNm, MB_CASE_TITLE, "UTF-8"));
                    if ($index!=$last) {$writer->text(', ');}
                }
            $writer->endElement();
        
        $writer->endElement();
    }

    $writer->endDocument();
    $writer->flush();
    echo "Done ....!";
}

function generate_error($eno, $format='xml') {
	$msg = ERROR_HASH[$eno];
	
	if ($format=='json') {
		$json = array('conv' => array("code" => "$eno", "msg" => "$msg"));
		header('Content-Type: application/json');
		return json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	}
	else {
		$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<conv><error>';
		$xml .= '<code>' . $eno . '</code>';
		$xml .= '<msg>' . $msg . '</msg>';
		$xml .= '</error></conv>';
	
		header('Content-type: text/xml');
		return $xml;
	}
}

function response_xml (&$reply) {
	
	$reply['from_loc'] = trim(preg_replace('/\s+/', ' ', $reply['from_loc'])); 
	$reply['to_loc'] = trim(preg_replace('/\s+/', ' ', $reply['to_loc'])); 
	
	$resp_xml = <<<__xml
     <conv>
       <at>{$reply['date_time']}</at>
       <rate>{$reply['rate']}</rate>
       <from>
          <code>{$reply['from_code']}</code>
          <curr>{$reply['from_curr']}</curr>
          <loc>{$reply['from_loc']}</loc>
          <amnt>{$reply['from_amnt']}</amnt>
       </from>
       <to>
          <code>{$reply['to_code']}</code>
          <curr>{$reply['to_curr']}</curr>
          <loc>{$reply['to_loc']}</loc>
          <amnt>{$reply['to_amnt']}</amnt>
       </to>
     </conv>
__xml;
return $reply_xml;
}
?>