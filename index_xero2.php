<?php
	require_once('connection.php');

	echo "Start.";
	echo "<br>";

	// https://login.xero.com/identity/connect/authorize?response_type=code&client_id=YOURCLIENTID&redirect_uri=YOURREDIRECTURI&scope=openid profile email accounting.transactions&state=123
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=F6E991B153A4493DB4CC34D2FDD23278&redirect_uri=https://xero.cubimall.in.th/cbm/feedback.php&scope=accounting.transactions&state=123");
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=F6E991B153A4493DB4CC34D2FDD23278&redirect_uri=https://xero.cubimall.in.th/exchange_code.php&scope=accounting.transactions&state=123");
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=F6E991B153A4493DB4CC34D2FDD23278&redirect_uri=https://cubimall.in.th/cbmx/exchange_code.php&scope=accounting.transactions&state=123");
	
	// HB
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=F6E991B153A4493DB4CC34D2FDD23278&redirect_uri=https://cubimall.in.th/cbmx/exchange_code.php&scope=openid profile email accounting.transactions&state=123");
	header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=81DB872EF0574ADDB5C64B4B1CA18382&redirect_uri=http://localhost/soupgrade/exchange_code.php&scope=openid profile email accounting.transactions accounting.contacts accounting.settings offline_access&state=123");
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=F6E991B153A4493DB4CC34D2FDD23278&redirect_uri=https://cubimall.in.th/cbmx/exchange_code.php&scope=openid profile email files accounting.transactions accounting.contacts offline_access&state=123");

	// TH - SO
	//header("Location: https://login.xero.com/identity/connect/authorize?response_type=code&client_id=FHP6DQYHDA5QT4GFPBCWJDHJREGQP4&redirect_uri=https://cubimall.in.th/cbmx/exchange_code.php&scope=openid profile email accounting.transactions&state=123");
	
?>

