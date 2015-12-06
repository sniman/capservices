<?php 
include_once ('lib/nusoap.php'); 
//Give it value at parameter 
$param = array( 'user_email' => 'anwar.abrahman@yahoo.com', 'pickup_date' => '22/02/2014', 
	'pickup_loc' => 'Pusat Maklumat Harta Tanah Negara, Persiaran Perdana, Kementerian Kewangan', 'pickup_id' => '1', 'cart_total' => '70.00', 
	'cart_content' => 'MY1000-10|Detergen|1|20.00|20.00@MY1003-10|Sabun|2|25.00|50.00'); 
//Create object that referer a web services 

$wsdl = "http://192.168.43.249:8888/thatemall/nusoap-095/server.php";

//$client = new nusoap_client($wsdl, true);
$client = new nusoap_client('http://192.168.43.249:8888/thatemall/nusoap-095/server.php?wsdl', 'wsdl');
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
} 

//Call a function at server and send parameters too 
$response = $client->call('wsPlaceOrder', $param, 'http://192.168.43.249:8888/thatemall/nusoap-095/server.php?wsdl','http://tempuri.org/GetXhtml', array('content-type' => 'UTF-8'), true,null,'rpc','literal');

//Process result 
if($client->fault) 
{ 
echo "FAULT: <p>Code: (".$client->faultcode.")</p>"; 
echo "String: ".$client->faultstring; 
} 
else 
{ 
	echo $response;
} 
?> 


