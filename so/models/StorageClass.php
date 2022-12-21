<?php
namespace app\models;

class StorageClass
{
	function __construct() {
		// if( !isset($_SESSION) ){
        	// $this->init_session();
    	// }
   	}

   	public function init_session(){
    	//session_start();
	}

    public function getSession() {
    	//return $_SESSION['oauth2'];
    	$val =null;
		$hbdb = $this->connectdb();
		$Query = "select * from sales_order_token where id = 1";
		//echo $Query;
		$Result = sqlsrv_query($hbdb, $Query);
		$row = sqlsrv_fetch_array($Result);
	
		//echo $row[4];
		$refresh_token = $row[1];

	    // $_SESSION['oauth2'] = [
	        // 'token' => $token,
	        // 'expires' => $expires,
	        // 'tenant_id' => $tenantId,
	        // 'refresh_token' => $refreshToken,
	        // 'id_token' => $idToken
	    // ];

	    $val = [
	         'token' => $row[2],	// access token
	         'expires' => $row[3],
	         'tenant_id' => $row[4],
	         'refresh_token' => $row[1],
	         'id_token' => $row[6]
	     ];

		
		return $val;
    }

 	public function startSession($token, $secret, $expires = null)
	{
       	//session_start();
	}

	public function connectdb() {
		$hbhost = '164.52.2.140:1433';
		$hbdatabase = 'sales_order';
		$hbuser = 'cubiDrag00n';
		$hbpassword = 's0mj3tt1ngci4l@t!';
		//$hbdb = sqlsrv_connect($hbhost, $hbuser, $hbpassword, $hbdatabase);
		
		$serverName = "164.52.2.140\\sqlexpress, 1433"; //serverName\instanceName, portNumber (default is 1433)
		$connectionInfo = array( "Database"=>$hbdatabase, "UID"=>$hbuser, "PWD"=>$hbpassword);
		$hbdb = sqlsrv_connect( $serverName, $connectionInfo);
		return $hbdb;
	}
	
	public function setToken($token, $expires = null, $tenantId, $refreshToken, $idToken)
	{    
	    // $_SESSION['oauth2'] = [
	        // 'token' => $token,
	        // 'expires' => $expires,
	        // 'tenant_id' => $tenantId,
	        // 'refresh_token' => $refreshToken,
	        // 'id_token' => $idToken
	    // ];
		$hbdb = $this->connectdb();
		$Query = "update sales_order_token set refresh_token = '".$refreshToken."', expires = '".$expires."', access_token = '".$token."' where id = 1";
		//echo $Query;
		$Result = sqlsrv_query($hbdb, $Query);
		
	}

	public function getToken()
	{
	    //If it doesn't exist or is expired, return null
	    // if (empty($this->getSession())
	        // || ($_SESSION['oauth2']['expires'] !== null
	        // && $_SESSION['oauth2']['expires'] <= time())
	    // ) {
	        // return null;
	    // }
	    return $this->getSession();
	}

	public function getAccessToken()
	{
	    //return $_SESSION['oauth2']['token'];
		$sess = $this->getSession();
	    return $sess['token'];
	}

	public function getRefreshToken()
	{
	    //return $_SESSION['oauth2']['refresh_token'];
	    $sess = $this->getSession();
	    return $sess['refresh_token'];
	}

	public function getExpires()
	{
	    //return $_SESSION['oauth2']['expires'];
		$sess = $this->getSession();
	    return $sess['expires'];
	}

	public function getXeroTenantId()
	{
	    //return $_SESSION['oauth2']['tenant_id'];
		$sess = $this->getSession();
	    return $sess['tenant_id'];
	}

	public function getIdToken()
	{
	    //return $_SESSION['oauth2']['id_token'];
		$sess = $this->getSession();
	    return $sess['id_token'];
	}

	public function getHasExpired()
	{
		return false;
	}
}
?>