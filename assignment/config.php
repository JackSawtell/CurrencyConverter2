<?php
@date_default_timezone_set("GMT"); 

# params array
define('PARAMS', array('to', 'from', 'amnt', 'format'));

define('FORMAT_CHECK',array('xml', 'json'));

# error_hash to hold error numbers and messages
define ('ERROR_HASH', array(
	1000 => 'Required parameter is missing',
	1100 => 'Parameter not recognized',
	1200 => 'Currency type not recognized',
	1300 => 'Currency amount must be a decimal number',
	1400 => 'Format must be xml or json',
	1500 => 'Error in Service',
	2000 => 'Action not recognized or is missing',
	2100 => 'Currency code in wrong format or is missing',
	2200 => 'Currency code not found for update',
	2300 => 'No rate listed for currency',
	2400 => 'Cannot update base currency',
    2500 => 'Error in service'));
    
define('DEFAULT_CODES', array(
    'AUD','BRL','CAD','CHF',
    'CNY','DKK','EUR','GBP',
    'HKD','HUF','INR','JPY',
    'MXN','MYR','NOK','NZD',
    'PHP','RUB','SEK','SGD',
    'THB','TRY','USD','ZAR'));
?>