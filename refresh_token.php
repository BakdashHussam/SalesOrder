<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="refresh" content="280">
	<title>Sales Order With Xero OAuth 2.0</title>
</head>
<body>

<?php
	require_once('parameters.php');
	require_once('connection.php');

	// get token from the database
	$Query = "select * from sales_order_token where id = 1";
	//echo $Query;
 	$Result = sqlsrv_query($hbdb, $Query);
	$row = sqlsrv_fetch_array($Result);
	
	//echo $row[4];
	$refresh_token = $row[1];

	if (true)
	{
		$header = array(
			'authorization: ' . 'Basic '.base64_encode($client_id.':'.$client_secret),
			'Content-Type: multipart/form-data'
		);
		// set post fields
		$post = [
			'grant_type' => 'refresh_token',
			'refresh_token'   => $refresh_token
		];

		$ch = curl_init();
		//$ch = curl_init('https://identity.xero.com/connect/token');
		$url = "https://identity.xero.com/connect/token";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		//curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

		// do anything you want with your response
		//echo $response;	
		
		// Decode response
		$response_obj = json_decode($response, false);
		var_dump(json_decode($response, false));


		$id_token = $response_obj->{'id_token'};
		$access_token = $response_obj->{'access_token'};
		
		$expires_in = $response_obj->{'expires_in'};
		$token_type = $response_obj->{'token_type'};
		$scope = $response_obj->{'scope'};
		$refresh_token = $response_obj->{'refresh_token'};
		
		if ($refresh_token <> '')
		{
			echo "refresh_token: ".$refresh_token;
			echo "<br>";

			$Query = "update sales_order_token set refresh_token = '".$refresh_token."', expires = '".$expires_in."', access_token = '".$access_token."', token = '".$id_token."', tenant_id = '".$xero_tenant_id."' where id = 1";
			//echo $Query;
			$Result = sqlsrv_query($hbdb, $Query);
		}
		// update database
//		$Query = "update sales_order_token set refresh_token = '".$refresh_token."', expires = '".$expires_in."', access_token = '".$access_token."' where id = 1";
		//echo $Query;
//		$Result = sqlsrv_query($hbdb, $Query);
		
	}

?>
</body>
</html>
