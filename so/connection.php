<?php
// 	'dsn' => 'sqlsrv:Server=164.52.2.140,1433;Database=sales_order',

	//$hbhost = '164.52.2.140';
	$hbhost = '164.52.2.140:1433';
	$hbdatabase = 'sales_order';
	$hbuser = 'cubiDrag00n';
	$hbpassword = 's0mj3tt1ngci4l@t!';
	//$hbdb = sqlsrv_connect($hbhost, $hbuser, $hbpassword, $hbdatabase);
	
	$serverName = "164.52.2.140\\sqlexpress, 1433"; //serverName\instanceName, portNumber (default is 1433)
	$connectionInfo = array( "Database"=>$hbdatabase, "UID"=>$hbuser, "PWD"=>$hbpassword);
	$hbdb = sqlsrv_connect( $serverName, $connectionInfo);
	//$sSQL= 'SET NAMES utf8'; 

	//mysqli_query($hbdb,$sSQL)
	
	if( $hbdb ) {
		 echo "Connection established.<br />";
	}else{
		 echo "Connection could not be established.<br />";
		 die( print_r( sqlsrv_errors(), true));
	}
	//echo "Connected successfully";
	//echo "<br>";

?>