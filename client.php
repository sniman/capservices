<?php 
include_once ('lib/nusoap.php'); 
//Give it value at parameter 
$param = array( 'your_name' => 'Monotosh Roy'); 
//Create object that referer a web services 

$wsdl = "http://localhost:8888/moodle27/nusoap-095/server.php";

//$client = new nusoap_client($wsdl, true);
$client = new nusoap_client('http://localhost:8888/moodle27/nusoap-095/server.php?wsdl', 'wsdl');
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
} 


//Call a function at server and send parameters too 
$response = $client->call('get_message', $param, 'http://localhost:8888/moodle27/nusoap-095/server.php?wsdl','http://tempuri.org/GetXhtml', array('content-type' => 'UTF-8'), true,null,'rpc','literal');

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


