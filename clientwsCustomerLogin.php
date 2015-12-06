<?php 
include_once ('lib/nusoap.php'); 
//Give it value at parameter 
$param = array( 'c_email' => 'snoriman@gmail.com', 'c_password' => 'Myp@ss1983'); 
//Create object that referer a web services 
echo '<h2>Constructor error</h2>
$wsdl = "http://localhost:8888/moodle27/nusoap-095/server.php";

//$client = new nusoap_client($wsdl, true);
$client = new nusoap_client('http://localhost:8888/moodle27/nusoap-095/server.php?wsdl', 'wsdl');
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
} 


//Call a function at server and send parameters too 
$response = $client->call('wsCustomerLogin', $param, 'http://localhost:8888/moodle27/nusoap-095/server.php?wsdl','http://tempuri.org/GetXhtml', array('content-type' => 'UTF-8'), true,null,'rpc','literal');

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


