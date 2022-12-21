<?php
use \Firebase\JWT\JWT;
require_once('parameters.php');
//require_once('parameters_th.php');
require_once('connection.php');

	// Send to a user to authorize the application -- index_xero.php
	// User redirect back with a code
//	echo $client_id;
//	echo "<br>";
//	echo $client_secret;
//	echo "<br>";


	if (isset($_GET["code"]))
		$code = $_GET["code"];
	if (isset($_GET["state"]))
		$state = $_GET["state"];

//	echo $code;
//	echo "<br>";
//	echo $state; 
//	echo "<br>";
	$st = 'Basic '.base64_encode($client_id.':'.$client_secret);
	//echo $st;
//	echo "<br>";

	// Exchange the code
	if (isset($_GET["code"]) && isset($_GET["state"]))
	{
		// set post fields
		$post = [
			'grant_type' => 'authorization_code',
			'code'   => $code,
			//'redirect_uri' => 'https://xero.cubimall.in.th/receive_token.php'
			//'redirect_uri' => 'https://cubimall.in.th/cbmx/receive_token.php'
			'redirect_uri' => 'http://localhost/soupgrade/exchange_code.php'
		];

		$ch = curl_init();
		//$ch = curl_init('https://identity.xero.com/connect/token');
		$url = "https://identity.xero.com/connect/token";
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: ' . 'Basic '.base64_encode($client_id.':'.$client_secret), 'Content-Type: multipart/form-data'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//curl_setopt($ch, Content-Type, 'application/x-www-form-urlencoded');
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch,CURLOPT_FAILONERROR,TRUE);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		//curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

		echo $response;	
		echo "<br>";

		
	}
	// $response ready - Receive the token
	if ($response <> '')
	{
		//$response_obj = JSON.parse($response);
		$response_obj = json_decode($response, false);
		//var_dump(json_decode($response, false));


		$id_token = $response_obj->{'id_token'};
		$access_token = $response_obj->{'access_token'};
		
		$expires_in = $response_obj->{'expires_in'};
		$token_type = $response_obj->{'token_type'};
		$scope = $response_obj->{'scope'};
		$refresh_token = $response_obj->{'refresh_token'};
		// echo $id_token;
		// echo "<br>";
		// echo $access_token;
		// echo "<br>";
		// echo $expires_in;
		// echo "<br>";
		// echo $token_type;
		// echo "<br>";
		// echo $scope;
		// echo "<br>";
		// echo "Fin!";
		
	
		echo "<br>";
		if ($access_token <> '')
		{
//			echo "access_token: ".$access_token;
//			echo "<br>";
			
			//$access_token_obj = json_decode($access_token, false);
			//var_dump(json_decode($access_token_obj, false));
			//echo "<br>";
			
		
		
		
		
		}
		
		if ($id_token <> '')
		{
//			echo "id_token: ".$id_token;
//			echo "<br>";
		}
		
		if ($expires_in)
		{
//			echo "expires_in: ".$expires_in;
//			echo "<br>";
		}

		if ($token_type)
		{
//			echo "token_type: ".$token_type;
//			echo "<br>";
		}
		
		if ($refresh_token)
		{
			echo "refresh_token: ".$refresh_token;
			echo "<br>";
		}

	// Store in database
	//$Query = "update sales_order_token set refresh_token = '".$refresh_token."' where id = 1";
	$Query = "update sales_order_token set refresh_token = '".$refresh_token."', expires = '".$expires_in."', access_token = '".$access_token."', token = '".$id_token."', tenant_id = '".$xero_tenant_id."' where id = 1";
	//echo $Query;
	$Result = sqlsrv_query($hbdb, $Query);
	
	
	// Check the tenants authorized to access
		// set Header field
		$header = array(
			'Authorization: Bearer '. $access_token,
			//'Accept: application/json',
			'Content-Type: multipart/form-data'
		);
		// set post fields
		$ch = curl_init();
		//$ch = curl_init('https://identity.xero.com/connect/token');
		//$url = "https://api.xero.com/connections";
		$url = "https://api.xero.com/api.xro/2.0/organization";
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Bearer ' . $access_token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//curl_setopt($ch, Content-Type, 'application/x-www-form-urlencoded');
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch,CURLOPT_FAILONERROR,TRUE);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		//curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

// Here to check organizations and get $xero_tenant_id

		 echo "<br>";
		 echo "The response for organization:";
		 echo "<br>";
		 echo $response;	
		 echo "<br>";

//
	// Check the tenants authorized to access
		// set Header field
		$header = array(
			'Authorization: Bearer '. $access_token,
			//'Accept: application/json',
			'Content-Type: application/json'
			//'Content-Type: multipart/form-data'
		);
		// set post fields
		$ch = curl_init();
		//$ch = curl_init('https://identity.xero.com/connect/token');
		$url = "https://api.xero.com/connections";
		//$url = "https://api.xero.com/api.xro/2.0/organization";
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Bearer ' . $access_token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//curl_setopt($ch, Content-Type, 'application/x-www-form-urlencoded');
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch,CURLOPT_FAILONERROR,TRUE);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		//curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

// Here to check connections and get $xero_tenant_id

		 echo "<br>";
		 echo "The response for Connections:";
		 echo "<br>";
		 echo $response;	
		 echo "<br>";

//
	
		// Check contacts
		
		$header = array(
			'Authorization: Bearer '. $access_token,
			'Xero-tenant-id: '. $xero_tenant_id,
			'Accept: application/json',
			'Content-Type: multipart/form-data'
		);

		$ch = curl_init();
		$url = "https://api.xero.com/api.xro/2.0/Contacts";
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: ' . 'Bearer ' . $access_token, 'xero-tenant-id: '.'680b41c1-351e-4d43-8c3f-a314b2bf1da6', 'accept: application/json', 'Content-Type: multipart/form-data'));
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . 'Bearer ' . $access_token, 'Xero-Tenant-Id: '.'680b41c1-351e-4d43-8c3f-a314b2bf1da6', 'Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'));
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . 'Bearer ' . $access_token, 'Accept: application/json', 'Xero-tenant-id: 680b41c1-351e-4d43-8c3f-a314b2bf1da6'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

//		 echo "The response for contacts:";
//		 echo "<br>";
//		 echo $response;	
//		 echo "<br>";

	//	Display invoices
		$header = array(
			'Authorization: Bearer '. $access_token,
			'Xero-tenant-id: '. $xero_tenant_id,
			'Accept: application/json',
			'Content-Type: multipart/form-data'
		);
		
		$ch = curl_init();
		$url = "https://api.xero.com/api.xro/2.0/Invoices";
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: ' . 'Bearer ' . $access_token, 'accept: application/json', 'Xero-Tenant-Id: ' . $xero_tenant_id));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//curl_setopt($ch, Content-Type, 'application/x-www-form-urlencoded');
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ch,CURLOPT_FAILONERROR,TRUE);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		//curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

		// echo "The response for get invoices:";
		// echo "<br>";
		// echo $response;	
		// echo "<br>";
		
	}
	
	
	


?>