<?php
	require_once ('lib/nusoap.php'); 
	
	$conn;
	$tbl_prefix = 'tem_';
	$URL = "http://localhost:8888/capservices/";
	$namespace = $URL . 'server.php?wsdl';
	$server = new soap_server;
	$server->configureWSDL('CUPPODOCIA', $URL);
	$server->register('wsCustomerLogin', array('email' => 'xsd:string', 'password' => 'xsd:string'),  array('details' => 'xsd:string'));  
	$server->register('wsGetCategories', array(),  array('xml' => 'xsd:string'));   
	$server->register('wsGetProductByCategory', array('category_name' => 'xsd:string'),  array('xml' => 'xsd:string'));  
	$server->register('wsGetPickupDailySchedule', array(),  array('xml' => 'xsd:string'));  
	$server->register('wsPlaceOrder', array('user_email' => 'xsd:string', 'user_mobile' => 'xsd:string', 'pickup_date' => 'xsd:string', 'pickup_loc' => 'xsd:string', 'pickup_id' => 'xsd:string', 'cart_total' => 'xsd:string', 'cart_content' => 'xsd:string'),  array('result' => 'xsd:string'));
	$server->register('wsGetMoodleCourse', array('student_id' => 'xsd:string'),  array('xml' => 'xsd:string')); 
	$server->register('wsGetMoodleSectionByCourse', array('course_id' => 'xsd:string'),  array('xml' => 'xsd:string'));  
	
	function wsPlaceOrder($user_email, $user_mobile, $pickup_date, $pickup_loc, $pickup_id, $cart_total, $cart_content) {
		$randnumber = rand(1000000, 9999999);
		$cart_array = explode('@',$cart_content);
		$result = '';
		$sqlQuery = '';
		intConnDatabase();
		for($x=0; $x<sizeof($cart_array); $x++) {
			$cart_subarray =  explode('|', $cart_array[$x]);
			$sqlQuery = "INSERT INTO ".$GLOBALS['tbl_prefix']."order_quick(email, pickup_date, pickup_details, pickup_id, prod_code, prod_name, prod_quantity, prod_unit_price, prod_sub_total, prod_total, ref_number) VALUES ".
			"('$user_email', '$pickup_date', '$pickup_loc', '$pickup_id', '".$cart_subarray[0]."', '".$cart_subarray[1]."', '".$cart_subarray[2]."', '".$cart_subarray[3]."', '".$cart_subarray[4]."', '".$cart_total."', '$randnumber')";
			mysqli_query($GLOBALS['conn'], $sqlQuery);
		}
		$result = $randnumber;
		intCloseDatabase();
		intgwSendSms('API4R6VWQ54TA', 'API4R6VWQ54TAR3YHO', '60123000000', '6'.$user_mobile, 'Your order number is '.$result.'. Please state it when you claim your package on '.$pickup_date.', '.$pickup_loc.'. Total amount to be paid is RM '.$cart_total.'0') ;
		return $result;
	}
		
	
	function wsGetPickupDailySchedule() {
		$StringXML = "";
		intConnDatabase();
		$sqlQuery= "SELECT b.day, b.time, a.name, a.location, a.id, b.id AS id_pickup_dt FROM ".$GLOBALS['tbl_prefix']."pickup_day_time b LEFT JOIN ".
			      $GLOBALS['tbl_prefix']."pickup_point a ON b.id_pickup_point=a.id";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '<item><day>'.$result['day'].'</day><time>'.$result['time'].'</time><name>'.$result['name'].
			'</name><location>'.$result['location'].'</location><id>'.$result['id'].'</id><id_pickup_dt>'.$result['id_pickup_dt'].'</id_pickup_dt>'.
			'</item>'; 
		}
		$StringXML = '<root>'.$StringXML.'</root>';
		intCloseDatabase();
		return $StringXML;
	}
	
	function wsGetProductByCategory($category_name) {
		$StringXML = "";
		intConnDatabase();
		$sqlQuery= "SELECT a.id, a.manufacturer_id, a.categories, a.code, a.quantity, a.weight, a.weight_class, a.purchase_price, a.image, b.name, ".
			      "b.description, c.name AS man_name, c.image AS man_image FROM ".$GLOBALS['tbl_prefix']."products a LEFT JOIN ".
			      $GLOBALS['tbl_prefix']."products_info b ON a.id=b.product_id LEFT JOIN ".$GLOBALS['tbl_prefix']."manufacturers c ON ".
			      "a.manufacturer_id=c.id WHERE a.categories = (SELECT category_id FROM ".$GLOBALS['tbl_prefix']."categories_info WHERE ".
			      "name='$category_name') ORDER BY a.id, a.categories, a.categories ASC";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '<item><id>'.$result['id'].'</id><manufacturer_id>'.$result['manufacturer_id'].'</manufacturer_id><categories>'.$result['categories'].
			'</categories><code>'.$result['code'].'</code><quantity>'.$result['quantity'].'</quantity><weight>'.$result['weight'].'</weight>'.
			'<weight_class>'.$result['weight_class'].'</weight_class><purchase_price>'.$result['purchase_price'].'</purchase_price>'.
			'<image>'.$result['image'].'</image><prodname>'.$result['name'].'</prodname><description>Test description</description>'.
			'<man_name>'.$result['man_name'].'</man_name><man_image>'.$result['man_image'].'</man_image></item>'; 
		}
		$StringXML = '<root>'.$StringXML.'</root>';
		intCloseDatabase();
		return $StringXML;
	}
	
	function wsGetCategories() {
		$StringXML = "";
		intConnDatabase();
		$sqlQuery = "SELECT a.id, a.parent_id, b.name, a.priority, c.name AS parent_name FROM ".$GLOBALS['tbl_prefix']."categories a LEFT JOIN ".
			       $GLOBALS['tbl_prefix']."categories_info b ON b.category_id=a.id ".
			       " LEFT JOIN ".$GLOBALS['tbl_prefix']."categories_info c ON c.id=a.parent_id ".
			       "ORDER BY a.parent_id, a.priority ASC";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '<item><id>'.$result['id'].'</id><parent>'.$result['parent_id'].'</parent><name>'.$result['name'].
			'</name><parentname>'.$result['parent_name'].'</parentname><priority>'.$result['priority'].'</priority></item>'; 
		}
		$StringXML = '<root>'.$StringXML.'</root>';
		intCloseDatabase();
		return $StringXML;
	}
	/*
	 get course by student id
	 */	
	function wsGetMoodleCourse($student_id) {
        $StringXML = "";
		intMoodleConnDatabase();
		$sqlQuery= "select u.id,s.firstname,c.fullname,e.courseid from mdl_enrol e,mdl_user_enrolments u,mdl_user s,mdl_course c where  e.id=u.enrolid and s.id=u.userid and c.id=e.courseid and u.userid='$student_id' order by c.fullname;";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		/*
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '<item><id>'.$result['courseid'].'</id><subject>'.$result['fullname'].'</subject></item>'; 
		}
		*/
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '#'.$result['courseid'].':'.$result['fullname'].''; 
		}
		$StringXML = ''.$StringXML.'';
		intCloseDatabase();
		return $StringXML;
	}
	
	/*
	get section/course subject by courseid
	*/
	function wsGetMoodleSectionByCourse($course_id) {
 		$StringXML = "";
		intMoodleConnDatabase();
		$sqlQuery= "select * from mdl_course_sections where course= '$course_id' and name is not null";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		/*
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '<item><subject>'.$result['name'].'</subject></item>'; 
		}
	*/
		while($result = mysqli_fetch_array($query)) {
			$StringXML.= '#'.$result['name'].''; 
		}
			
		$StringXML = ''.$StringXML.'';
		intCloseDatabase();
		return $StringXML;
	}

	function wsCustomerLogin($email, $password) {
		$user = null;
		$validation = false;
		$details = '';
		$d_fullName = '';
		$d_email = '';
		$d_mobile = '';
		intConnDatabase();
		$sqlQuery = "SELECT * FROM ".$GLOBALS['tbl_prefix']."customers WHERE email='".$email."' limit 1";
		$query = mysqli_query($GLOBALS['conn'], $sqlQuery);
		while($result = mysqli_fetch_array($query)) {
			$user = $result;
		}
		
		if($user!=null) {
			$d_fullName = $user['firstname'].' '.$user['lastname'];
			$d_email = $user['email'];
			$d_mobile = $user['phone'];
			if(intPassChecksum($user['email'], $password)==$user['password']) {
				$validation = true;
			} 
		}
		intCloseDatabase();
		if($validation==true) {
			$details = $d_fullName.'|'.$d_email.'|'.$d_mobile;
		}
		return $details;
	}

	function intConnDatabase() {
		$GLOBALS['conn'] = mysqli_connect('localhost', 'root', 'root', '1mpian_thatemall', '8889');
		if (mysqli_connect_errno()) {
			$GLOBALS['conn'] = null;
		} 
	}
	
	/*
	 create db to moodle 
	 */
	function intMoodleConnDatabase() {
		$GLOBALS['conn'] = mysqli_connect('localhost', 'root', 'root', 'moodle27', '8889');
		if (mysqli_connect_errno()) {
			$GLOBALS['conn'] = null;
		} 
	}

	function intCloseDatabase() {
		mysqli_close();
	}
	
	function intPassChecksum($login, $password) {
	  	  if (strlen($password) < 2) {
	  	  	  return hash('sha256', strtolower($login) . $password . '0XRj2nDIetIesMSaUfWjrwqD4h0IoCScNHSfy4UTf6hTuwDjhCWxsoURQ8J8tFSpB3H5Lz3x8OLf3ikxpPJOhk5t0e7Pp8SUUz1w6EtXJLqIxCKBgQM70addF2OMYrbB');
	  	  } else {
	  	  	  $password = @str_split($password, ceil(strlen($password)/2));
	  	  	  return hash('sha256', strtolower($login) . $password[0] . '0XRj2nDIetIesMSaUfWjrwqD4h0IoCScNHSfy4UTf6hTuwDjhCWxsoURQ8J8tFSpB3H5Lz3x8OLf3ikxpPJOhk5t0e7Pp8SUUz1w6EtXJLqIxCKBgQM70addF2OMYrbB' . $password[1]);
	  	  }
	  }
	  
	function intgwSendSms($user,$pass,$sms_from,$sms_to,$sms_msg)  
            {           
                        $query_string = "api.aspx?apiusername=".$user."&apipassword=".$pass;
                        $query_string .= "&senderid=".rawurlencode($sms_from)."&mobileno=".rawurlencode($sms_to);
                        $query_string .= "&message=".rawurlencode(stripslashes($sms_msg)) . "&languagetype=1";        
                        $url = "http://gateway.onewaysms.com.my:10001/".$query_string;       
                        $fd = @implode ('', file ($url));      
                        if ($fd)  
                        {                       
				    if ($fd > 0) {
					Print("MT ID : " . $fd);
					$ok = "success";
				    }        
				    else {
					print("Please refer to API on Error : " . $fd);
					$ok = "fail";
				    }
                        }           
                        else      
                        {                                       
                                    $ok = "fail";       
                        }           
                        return $ok;  
            } 



	$server->service($HTTP_RAW_POST_DATA); 
	exit(); 
?>  
