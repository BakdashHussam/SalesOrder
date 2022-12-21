<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

use XeroPHP\Application\PrivateApplication;

use XeroAPI\XeroPHP;

//use XeroAPI\XeroPHP\Configuration;

use XeroAPI\XeroPHP\AccountingObjectSerializer;

use XeroAPI\XeroPHP\Api\AccountingApi;

/**
 * ContactForm is the model behind the contact form.
 */
class Pin extends Model
{
	private $config;

	private $row_count = 20;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [   ];
    }

	private function setConfig()
	{
		//echo file_get_contents('file:///C:/inetpub/wwwroot/basic/cert/xero.pem');exit;
		/*
		// test site
		$this->config = [
			'oauth' => [
				'callback'         => 'http://localhost/',
				'consumer_key'     => '6IRWJSE3RYZX1YENPILNEQYPEPWGED',
				'consumer_secret'  => 'DOSQVC7DUKAJP4J17XKXIFMXONJVV',
				'signature_location'    => \XeroPHP\Remote\OAuth\Client::SIGN_LOCATION_QUERY,
				'rsa_private_key'  => file_get_contents("file:///C:/inetpub/wwwroot/so/cert/xero.pem"),
			],
			'curl' => [
				CURLOPT_USERAGENT   => 'cubinetdemo3',
			]
		];
		*/
		
		// live site
		$this->config = [
		  'oauth' => [
		      'callback'         => 'http://localhost/',
		      'consumer_key'     => 'FHP6DQYHDA5QT4GFPBCWJDHJREGQP4',
		      'consumer_secret'  => '2YAJX7HOWZOQCTYTKMWCW4FF4VJIS7',
		      'signature_location'    => \XeroPHP\Remote\OAuth\Client::SIGN_LOCATION_QUERY,
		      'rsa_private_key'  => file_get_contents("file:///C:/inetpub/wwwroot/soupgrade/cert/xero.pem"),
		  ],
		  'curl' => [
		      CURLOPT_USERAGENT   => 'CISB',
		  ]
		];
		
	}

	public function newGetXeroTenantId()
	{
		$storage = new StorageClass();
		$xeroTenantId = (string)$storage->getSession()['tenant_id'];
		return $xeroTenantId;
	}
	
	public function newGetaccess_token()
	{
		$storage = new StorageClass();
		$access_token = (string)$storage->getSession()['token'];
		return $access_token;
	}
	
	public function newXeroSetup()
	{
		$storage = new StorageClass();
		$xeroTenantId = (string)$storage->getSession()['tenant_id'];
		$access_token = (string)$storage->getSession()['token'];

		//$configr = new \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );		  
		$configr = new \XeroAPI\XeroPHP\Configuration();
		$configr->getDefaultConfiguration()->setAccessToken($access_token);		  
		$accountingApi = new \XeroAPI\XeroPHP\Api\AccountingApi(
			new \GuzzleHttp\Client(),
			$configr->getDefaultConfiguration()
		);
		return $accountingApi;
	}
	
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [  ];
    }

	public static function getDb()
	{
		return Yii::$app->get('db');
	}
	/* generateRandomID */
	private function generateRandomID()
	{
		// Copyright: http://snippets.dzone.com/posts/show/3123
		$len = 18;
		$base='ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz0123456789';
		$max=strlen($base)-1;
		$activatecode='';
		//mt_srand((double)microtime()*1000000);
		while (strlen($activatecode)<$len){
		$base = str_shuffle($base);
		$activatecode.=$base{mt_rand(0,$max)};
		}
		return strtoupper($activatecode);
	}
	/* generate codes */
	private function generatePin($id)
	{
		$sql = Yii::$app->db->createCommand("select sale_order_id, detail_id, product_code, qty
											from sale_order_detail
											where sale_order_id='".$id."'");
		$query = $sql->queryAll();
		$failed_cardserial = "";
		foreach($query as $key=>$value)
		{
			$sale_order_id	= $value["sale_order_id"];
			$detail_id	= $value["detail_id"];
			$product		= substr($value["product_code"],0,3);
			$batchID		= $value["product_code"];
			$qty			= $value["qty"];
			$prefix			= $product.date("ymd");
			
			$success  = false;
			$sql2 = Yii::$app->db->createCommand("select top 1 cardserial
											from generated_code 
											where cardserial like '".$prefix."%' order by cardserial desc");
			$query2 = $sql2->queryAll();
			$starting_sequence=0;
			
			if(isset($query2[0])){$starting_sequence = intval(str_replace($prefix,"",$query2[0]["cardserial"]));}

			for($i=0;$i<$qty;$i++)
			{
				$starting_sequence = $starting_sequence+1;
				$cardserial = $prefix.str_pad(($starting_sequence), 6, 0, STR_PAD_LEFT);
				$cardcode = $this->generateRandomID();
				//first attempt
				$success = $this->newPin($cardserial,$cardcode,$sale_order_id,$detail_id);
				if(!$success)
				{
					//second attempt
					$cardcode = str_shuffle($cardcode);
					$success = $this->newPin($cardserial,$cardcode,$sale_order_id,$detail_id);
					if(!$success) 
					{
						//last attempt
						$cardcode = str_shuffle($cardcode);
						$success = $this->newPin($cardserial,$cardcode,$sale_order_id,$detail_id);
						if(!$success) //last attempt, save it as error
						{
							$failed_cardserial .= $cardserial."|".$cardcode.",";
						}
					}
				}
			}
		}
		
		return ($failed_cardserial==="")?true:$failed_cardserial;
	}
	
	public function getCustomer()
	{
//		$this->setConfig();	// by hussam
		$customer_list = array();
		$temp = array();
		
		//$xero = new PrivateApplication($this->config);
		/*
		$lookup = $xero->load('Accounting\\Contact')->orderBy('Name', 'asc')->execute();
		foreach ($lookup as $item) {
			$single = $xero->loadByGUID('Accounting\\Contact', $item["ContactID"]);
			//echo "<pre>"; var_dump($single);
			$temp = array();
			if ($single["ContactGroups"][0]["Name"] == 'Trade Customer') 
			{
				$temp["customer_id"] = $item["ContactID"];
				$temp["customer_name"] = $single["Name"];
				$temp["customer_code"] = $single["AccountNumber"];
				$temp["currency"] = $single["DefaultCurrency"];
				$temp["discount"] = $single["Discount"];
				$temp["term"] = $single["PaymentTerms"]["Sales"]["Day"];
				$customer_list[] = $temp;
			}
        }
		*/
		//$lookup = $xero->load('Accounting\\Contact')->orderBy('Name', 'asc')->execute();
//		$lookup = $xero->load('Accounting\\ContactGroup')->where('Name','Trade Customer')->orderBy('Name', 'asc')->execute()[0];
// HB update for Xero OAuth2.0
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();
		// Prepare conditions or data:
		$whereCond = 'Name=="Trade Customer"';
		$orderCond = 'Name ASC';
		// API Request
		$lookup = $accountingApi->getContactGroups($xeroTenantId, $whereCond, $orderCond)[0];
		$arrlookup = json_decode($lookup, true);
		//var_dump($arrlookup);
		//echo $arrlookup["ContactGroupID"];
		//exit;

//$single = $xero->loadByGUID('Accounting\\ContactGroup', $lookup["ContactGroupID"]);
// HB update for Xero OAuth2.0
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();
		// Prepare conditions or data:
		$whereCond = 'ContactGroupID=="' . $arrlookup["ContactGroupID"] . '"';
//echo $whereCond;
//exit();
		$orderCond = 'Name ASC';
		// API Request
		$single = $accountingApi->getContactGroup($xeroTenantId, $arrlookup["ContactGroupID"]);
		//$item = $xero->loadByGUID('Accounting\\Contact', $single["Contacts"][0]["ContactID"]);
		//echo "<pre>"; var_dump($item);exit;
//		echo $single;	// ok
		$arrsingle = json_decode($single, true);
		$arrContacts = $arrsingle["ContactGroups"][0]["Contacts"];
		//var_dump($arrContacts);
		//exit();
		
		//var_dump($arrsingle["ContactGroups"][0]["Contacts"]);
		
		//exit;
		foreach ($arrContacts as $contact) {
		//foreach ($arrlookup["Contacts"] as $contact) {
//			$item = $xero->loadByGUID('Accounting\\Contact', $contact["ContactID"]);
// HB update for Xero OAuth2.0
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();
		// Prepare conditions or data:
		$whereCond = 'ContactID=="'. $contact["ContactID"].'"';
		$orderCond = 'Name ASC';
		// API Request
//		$item = $accountingApi->getContact($xeroTenantId, $whereCond, $orderCond);
		$item = $accountingApi->getContact($xeroTenantId, $contact["ContactID"]);
		$arritem = json_decode($item, true);
		//var_dump($arritem);
		$itemcontact = $arritem["Contacts"][0];
		//var_dump($itemcontact);
		//echo "<br>";
		//exit;	
		//echo $itemcontact["Name"];
		if (isset($itemcontact["AccountNumber"])) {
			$temp = array();
			$temp["customer_id"] = $itemcontact["ContactID"];
			$temp["customer_name"] = $itemcontact["Name"];
			$temp["customer_code"] = $itemcontact["AccountNumber"];
			$temp["currency"] = "THB";	//$itemcontact["DefaultCurrency"];
			$temp["discount"] = $itemcontact["Discount"];
			$temp["term"] = 30;	//$itemcontact["PaymentTerms"]["Sales"]["Day"];
			$customer_list[] = $temp;
		} else {
			var_dump($itemcontact);
			exit;
		}
        }
		//var_dump($customer_list); 
		//exit;
		return ($customer_list);
	}
	
	/* get product list */
	public function getProduct($type="")
	{
//		$this->setConfig();	// by hussam
		$itemlist = array();
//		$xero = new PrivateApplication($this->config);
		$mode = ($type=="")?'Name.StartsWith("Cubi")':'Name.StartsWith("DTU")';
		switch(strtoupper($type))
		{
			case "DTU" : $mode = 'Name.StartsWith("DTU")';
				break;
			case "CUBI" : $mode = 'Name.StartsWith("Cubi")';
				break;
			default : $mode = '1=1';
				break;
		}
//		$lookup = $xero->load('Accounting\\Item')->where($mode)->orderBy('Name', 'asc')->execute();

// HB update for Xero OAuth2.0
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();
		// Prepare conditions or data:
		$whereCond = $mode;
		$orderCond = 'Name ASC';
$if_modified_since = '2000-01-01T12:00:00.002-08:00'; // \DateTime | Only records created or modified since this timestamp will be returned
		// API Request
		$lookup = $accountingApi->getItems($xeroTenantId, $if_modified_since, $whereCond, $orderCond, 2);
		$arrlookup = json_decode($lookup, true);
//echo $lookup;		
//var_dump($arrlookup);
$arrItems = $arrlookup["Items"];
//exit();
		
//		foreach ($lookup as $item) {
		foreach ($arrItems as $item) {
			$itemlist[$item["Code"]] = array(
											"product_value"=>$item["SalesDetails"]["UnitPrice"],
											"product_tax"=>$item["SalesDetails"]["TaxType"],
											"product_code"=>$item["Code"],
											"product_desc"=>$item["Name"]
											);
        }
		unset($itemlist["DISC"]);
		ksort($itemlist);
		return ($itemlist);
	}
	
	/* get create sales order */
	public function addNewSalesOrder($col,$dtu=0)
	{
		$last_id=0;
		$flag = false;
		$orderdate	=	isset($col["orderdate"])?$col["orderdate"]:"";
		$dtu_month	=	isset($col["dtu_month"])?$col["dtu_month"]:"";
		$customer	=	isset($col["customer"])?$col["customer"]:"";
		$customer_id	=	isset($col["customer_id"])?$col["customer_id"]:"";
		$customer_name	=	isset($col["customer_name"])?$col["customer_name"]:"";
		$customer_currency	=	isset($col["currency"])?$col["currency"]:"MYR";
		$discount_rate = 	isset($col["discount"])?$col["discount"]:"0";
		$discount_rate_ori = 	isset($col["discount_ori"])?$col["discount_ori"]:"0";
		$term = 	isset($col["term"])?$col["term"]:"0";
		$tax = 	isset($col["tax"])?$col["tax"]:"exclusive";
		//echo "<pre>"; var_dump($col);exit;
		$temp = $col["orderdata"];
		$counter = count($temp["product"]);
		$amount = 0;
		$discount_amount = 0;
		
		
		$addon_col = $addon_value = "";
		if($dtu===1)
		{
			$addon_col = " ,dtu_batch";
			$addon_value = " ,'".$dtu_month."'";
		}
		
		if(count($col["orderdata"])>0 && $discount_rate>0)
		{
			
			for($i=0;$i<$counter;$i++)
			{
				if($temp["product"][$i]!="TRX FEE")
				{
					$amount += floatval($temp["price"][$i]) * floatval($temp["qty"][$i]);
				}
			}
			$discount_amount = ($amount*$discount_rate)/100;
		}
		//sales order
		
		$sql = Yii::$app->db->createCommand("select sale_order_no from sale_order")->queryAll();
		if(count($sql) && isset($sql[0]["sale_order_no"]) && $sql[0]["sale_order_no"]!="")
		{
			$queryString = "insert into sale_order 
									(sale_order_no, sale_order_date, customer_id, customer_code, customer_name, discount_rate,discount_rate_ori,discount,term,tax,approval_status,create_date,create_user ".$addon_col.")
									select CAST(max(sale_order_no) AS bigint)+1 ,'".$orderdate."','".$customer_id."','".$customer."','".$customer_name."','".$discount_rate."','".$discount_rate_ori."','".$discount_amount."','".$term."','".$tax."',0,getdate(),'".Yii::$app->user->identity->username."' ".$addon_value." from sale_order
									";
		}
		else
		{
			$queryString = "insert into sale_order 
									(sale_order_no, sale_order_date, customer_id, customer_code, customer_name, discount_rate,discount_rate_ori,discount,term,tax,approval_status,create_date,create_user ".$addon_col.")
									values
									('10000000','".$orderdate."','".$customer_id."','".$customer."','".$customer_name."','".$discount_rate."','".$discount_rate_ori."','".$discount_amount."','".$term."','".$tax."',0,getdate(),'".Yii::$app->user->identity->username."' ".$addon_value.")";
		}
		
		try
		{
			$sql = Yii::$app->db->createCommand($queryString);
			$query = $sql->execute();
			$last_id = Yii::$app->db->getLastInsertID();
		}
		catch(Exception $e)
		{
			$flag = false;
			echo $e->getMessage();exit;
		}
				
		//sales order details
		if($last_id>0)
		{
			$temp_sql_list = "";
			if(count($col["orderdata"])>0)
			{
				$temp_sql = "insert into sale_order_detail (sale_order_id, product_code, product_desc, product_value, qty, product_tax) values ";
				for($i=0;$i<$counter;$i++)
				{
					$temp_sql_list .= $temp_sql."('".$last_id."','".$temp["product"][$i]."','".$temp["desc"][$i]."','".$temp["price"][$i]."','".$temp["qty"][$i]."','".$temp["tax"][$i]."');"; 
				}
			}
			//echo $temp_sql_list;exit;
			if($temp_sql_list != "")
			{
				try
				{
					$sql = Yii::$app->db->createCommand($temp_sql_list);
					$query = $sql->execute();
					$flag = true;
				}
				catch(Exception $e)
				{
					$flag = false;
					echo $e->getMessage();exit;
				}
			}
		}
		
		if($flag===true && $dtu===0)
		{
			$this->generatePin($last_id);
		}
		
		return $last_id;
	}
	
	/* get create sales order */
	public function xeroInvoice($id=0)
	{
		$flag = false;
		$sql = Yii::$app->db->createCommand("select * from view_sales_order	where sale_order_id = '".$id."'");
		$query = $sql->queryAll();
//var_dump($query);		
		if(count($query)>0 && isset($query[0]))
		{
			$amount = 0;
			$discount_amount = $query[0]["discount"];
			$discount_rate = $query[0]["discount_rate"];
			$term =  $query[0]["term"];
			$tax =  $query[0]["tax"];
			$customer_name = $query[0]["customer_name"];
			$so_no = $query[0]["sale_order_no"];
			$so_date = date("Y-m-d",strtotime($query[0]["sale_order_date"]));
			
			//prepare for xero
			$lineItems = array();
//		$this->setConfig();	// by hussam
			//$xero = new PrivateApplication($this->config);
		
			foreach($query as $key=>$value)
			{
				//$amount += floatval($value["product_value"]) * floatval($value["qty"]);
				if($value["product_code"]=="TRX FEE") // transaction fee
				{					
//					$itemLookup = $xero->load('Accounting\\Item')->where('Code', 'TRX FEE')->execute()[0];
					// HB update for Xero OAuth2.0
					// Setup Xero Connection
					$accountingApi = $this->newXeroSetup();
					$xeroTenantId = $this->newGetXeroTenantId();

					// Prepare conditions or data:
					$whereCond = 'Code=="TRX FEE"';
					$orderCond = 'Name ASC';
					$if_modified_since = '2000-01-01T12:00:00.002-08:00'; // \DateTime | Only records created or modified since this timestamp will be returned
					// API Request
					$lookup = $accountingApi->getItems($xeroTenantId, $if_modified_since, $whereCond, $orderCond, 2);
					$arrlookup = json_decode($lookup, true);
					//echo $lookup;		
					//var_dump($arrlookup);
					$itemLookup = $arrlookup["Items"];
					//var_dump($itemLookup);

//exit(); // ok
					
//					$lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
					$lineitem = new \XeroAPI\XeroPHP\Models\Accounting\LineItem();
					$lineitem->setDescription("Transaction Fee")
							->setItemCode('TRX FEE')
							->setUnitAmount("-".$value["product_value"])
							->setTaxType($itemLookup[0]["SalesDetails"]["TaxType"])
							->setQuantity(1);

					//$lineItems[] = $lineitem;
					array_push($lineItems, $lineitem);
//					var_dump($lineItems);
//exit();					
				}
				else
				{
					$amount += floatval($value["product_value"]);
					//$itemLookup = $xero->load('Accounting\\Item')->where('Code', $value["product_code"])->execute()[0];
					// HB update for Xero OAuth2.0
					// Setup Xero Connection
					$accountingApi = $this->newXeroSetup();
					$xeroTenantId = $this->newGetXeroTenantId();

					// Prepare conditions or data:
					$whereCond = 'Code=="'.$value["product_code"].'"';
					$orderCond = 'Name ASC';
					$if_modified_since = '2000-01-01T12:00:00.002-08:00'; // \DateTime | Only records created or modified since this timestamp will be returned
					// API Request
					$lookup = $accountingApi->getItems($xeroTenantId, $if_modified_since, $whereCond, $orderCond, 2);
					$arrlookup = json_decode($lookup, true);
					//echo $lookup;		
					//var_dump($arrlookup);
					$itemLookup = $arrlookup["Items"];
//					var_dump($itemLookup);
					//$itemSalesDetailsArr= $itemLookup[0]["SalesDetails"]["TaxType"];
					//echo $itemSalesDetailsArr;
//exit(); 					

//					$lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
					$lineitem = new \XeroAPI\XeroPHP\Models\Accounting\LineItem();
					$lineitem->setItemCode($value["product_code"])
							->setTaxType($itemLookup[0]["SalesDetails"]["TaxType"])
//							->setTaxType($itemSalesDetailsArr["TaxType"])
							->setQuantity($value["qty"]);

					//$lineItems[] = $lineitem;
					array_push($lineItems, $lineitem);
//					var_dump($lineItems);
//exit(); 					
				}
				
			}
			//discount
			//if($discount_rate != '' || $discount_rate > 0) {
			if($discount_rate != '' && $discount_rate > 0) {
//				$itemLookup = $xero->load('Accounting\\Item')->where('Code', 'DISC')->execute()[0];
				// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();

				// Prepare conditions or data:
				$whereCond = 'Code=="DISC"';
				$orderCond = 'Name ASC';
				$if_modified_since = '2000-01-01T12:00:00.002-08:00'; // \DateTime | Only records created or modified since this timestamp will be returned
				// API Request
				$lookup = $accountingApi->getItems($xeroTenantId, $if_modified_since, $whereCond, $orderCond, 2);
				$arrlookup = json_decode($lookup, true);
				//echo $lookup;		
				//var_dump($arrlookup);
				$itemLookup = $arrlookup["Items"];
				//var_dump($itemLookup);
			
			
//				$lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
				$lineitem = new \XeroAPI\XeroPHP\Models\Accounting\LineItem();
				$lineitem->setDescription($discount_rate."% discount")
						->setItemCode('DISC')
						->setUnitAmount("-".$discount_amount)
						->setTaxType($itemLookup[0]["SalesDetails"]["TaxType"])
						->setQuantity(1);


				//$lineItems[] = $lineitem;
				array_push($lineItems, $lineitem);
			}
//exit(); // ok			
			//$contactLookup = $xero->load('Accounting\\Contact')->where('Name', $customer_name)->execute()[0];
	// HB update for Xero OAuth2.0
			// Setup Xero Connection
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();
			// Prepare conditions or data:
			$whereCond = 'Name=="'. $customer_name.'"';
			$orderCond = 'Name ASC';
			// API Request
//echo $whereCond;
//exit();			
			$contactLookup = $accountingApi->getContacts($xeroTenantId, true, $whereCond, $orderCond);
//echo $contactLookup;

			$contactLookupArray = json_decode($contactLookup, true);
//var_dump($contactLookupArray);

//echo $contactLookupArray["Contacts"][0]["ContactID"];
//exit();			

			//$contact = $xero->loadByGUID('Accounting\\Contact', $contactLookup["ContactID"]);
	// HB update for Xero OAuth2.0
			// Setup Xero Connection
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();
			// Prepare conditions or data:
//			$whereCond = 'ContactID=="'. $contactLookup["ContactID"].'"';
//			$orderCond = 'Name ASC';
			// API Request
			//$contact = $accountingApi->getContact($xeroTenantId, $whereCond, $orderCond);
//			$contact = $accountingApi->getContact($xeroTenantId, $invoiceLookup["Contact"]["ContactID"]);
			$contact = new \XeroPHP\Models\Accounting\Contact();
			$contact = $accountingApi->getContact($xeroTenantId, $contactLookupArray["Contacts"][0]["ContactID"]);
			
			//echo $contact;
			//exit();
			
			
			
			$contactArray = json_decode($contact, true);
			
			//var_dump($contactArray);
			//exit();
			
//			echo $contactArray["Contacts"][0]["ContactID"];
//			echo $contactArray["Contacts"][0]["DefaultCurrency"];
			$contacto = new \XeroPHP\Models\Accounting\Contact;
			$contacto->setContactID($contactArray["Contacts"][0]["ContactID"]);
			//echo $contacto["ContactID"];
			$contactoJson = json_encode($contacto);
			//echo $contactoJson;
			$contactoo = json_decode($contactoJson);
			//exit();
			
			//push into xeros
			$today = strtotime(date('Y-m-d'));
			$dueDate = strtotime('+' . $term . ' day', $today);
			$setDueDate = date('Y-m-d',$dueDate);

			//$invoice = new \XeroPHP\Models\Accounting\Invoice($xero);
			$invoice = new \XeroAPI\XeroPHP\Models\Accounting\Invoice; 
			$invoice->setDate(\DateTime::createFromFormat('Y-m-d', $so_date))
				->setDueDate(\DateTime::createFromFormat('Y-m-d', $setDueDate))
				->setType(\XeroAPI\XeroPHP\Models\Accounting\INVOICE::TYPE_ACCREC)
				->setStatus('AUTHORISED')
				->setLineAmountTypes($tax)
				->setCurrencyCode($contactArray["Contacts"][0]["DefaultCurrency"])
				//->setContact($contactArray["Contacts"][0]["ContactID"])
//				->setContact($contacto)
				//->setContact($contactoJson)
				//->setContact($contacto["ContactID"])
				->setReference($so_no);
				
			$invoice->setContact($contactoo);
			
			$invoice->setLineItems($lineItems);	
			// add line items to invoice
			//foreach($lineItems as $item) {
			//  $invoice->addLineItem($item);
			//}

			// save invoice
			// $invoice->save();
			
			$invoicesArr = array();
			array_push($invoicesArr, $invoice);	
			$invoices = new \XeroAPI\XeroPHP\Models\Accounting\Invoices();
			$invoices->setInvoices($invoicesArr);
		//echo $invoices;
		//exit();
			
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();

		try {
			$responseInvoice = $accountingApi->createInvoices($xeroTenantId, $invoices);
			//$responseInvoice = $accountingApi->updateOrCreateInvoices($xeroTenantId, $invoices, false, 2);
			//echo $responseInvoice;
			//print_r($responseInvoice);
		}
		catch(Exception $e){ 
			echo $e;
		}
		//exit();

		$responseInvoiceArray = json_decode($responseInvoice, true);
		//var_dump($responseInvoiceArray);
		//echo $responseInvoiceArray["Invoices"][0]["InvoiceNumber"];
		//exit();
			// return invoice number
			if(isset( $responseInvoiceArray["Invoices"][0]["InvoiceNumber"]) ){
				$flag = true;
				$invoice_no = $responseInvoiceArray["Invoices"][0]["InvoiceNumber"];
				try{
					$sql = Yii::$app->db->createCommand("update sale_order set invoice_no='".$invoice_no."' where sale_order_id=".$id);
					$query = $sql->execute();
				}catch(Exception $e){ }
			}
		}
		return $flag;
	}
	
	/* get sales order detail */
	public function getSalesOrderDetail($id)
	{
		$return = array();
		$sql = Yii::$app->db->createCommand(
											"select *
											from sale_order 
											where sale_order_id='".$id."'"
											);
		$query = $sql->queryAll();
		if(count($query)>0){ $return[0] = $query[0];}else{ $return[0]=array(); }
		
		$sql2 = Yii::$app->db->createCommand(
											"select product_code, product_desc, product_value, qty, product_tax
											from sale_order_detail
											where sale_order_id='".$id."'"
											);
		$query2 = $sql2->queryAll();
		if(count($query2)>0){ $return[1] = $query2;}else{ $return[1]=array(); }
		
		//check if there is credit note created
		$sql3 = Yii::$app->db->createCommand(
											"select credit_note_id, total_amount, discount, credit_type
											from credit_note
											where sale_order_id='".$id."' and approval_status=1"
											);
		$query3 = $sql3->queryAll();
		if(count($query3)>0){ $return[2] = $query3;}else{ $return[2]=array(); }
		//var_dump($sql->getText());exit;
		return $return;
	}
	
	/* get sales order detail */
	public function getSalesOrderToCreditNote($id)
	{
		$return = array();
		$flag = $flag2 = false;
		$sql = Yii::$app->db->createCommand(
											"select *
											from view_sales_order 
											where sale_order_id='".$id."'"
											);
		$query = $sql->queryAll();
		if(count($query)>0){ $flag = true; }
		/*
		$sql2 = Yii::$app->db->createCommand(
											"select product_code,product_value, sum(qty) as ttl
											from view_credit_note 
											where sale_order_id='".$id."'
											group by product_code,product_value"
											
											);
		*/
		$sql2 = Yii::$app->db->createCommand(
											"select credit_type, product_code,product_value, qty as ttl
											from view_credit_note 
											where sale_order_id='".$id."' and approval_status<>-1"
											
											);
		$query2 = $sql2->queryAll();
		if(count($query2)>0){ $flag2 = true; }
		
		if($flag)
		{
			foreach($query as $key=>$value)
			{
				$temp = $value;
				/*
				$temp["product_code"] = $value["product_code"];
				$temp["product_value"] = $value["product_value"];
				$temp["qty"] = $value["qty"];
				*/
				if($flag2)
				{
					foreach($query2 as $key2=>$value2)
					{
						if($temp["product_code"]==$value2["product_code"])
						{
							$is_zero = $temp["qty"]-$value2["ttl"];
							$temp["qty"] = $is_zero;
							if($is_zero<=0)
							{
								$temp["empty"]=1;
							}
							$temp["credited"]=1;
						}
					}
				}
				$return[] = $temp;
			}
		}
		//echo "<pre>";var_dump($return);exit;
		return $return;
	}
	
	/* getSOforCN */ 
	public function getSOforCN($col)
	{
		$id=0;
		$addon ="";
		if(isset($col["invoice_no"]) && $col["invoice_no"]!="0"){ $addon .= " and invoice_no='".$col["invoice_no"]."'";}
		if(isset($col["sale_order_no"]) && $col["sale_order_no"]!="0"){ $addon .= " and sale_order_no='".$col["sale_order_no"]."'";}
		//echo "select sale_order_id from sale_order where 1=1".$addon;exit;
		$sql = Yii::$app->db->createCommand("select sale_order_id from sale_order where 1=1".$addon);
		$query = $sql->queryAll();
		if(count($query)>0 && isset($query[0]["sale_order_id"]))
		{
			$id = $query[0]["sale_order_id"];
		}
		return $id;
	}
	
	/* get generated code details */
	public function getCardDetails($id,$product)
	{
		$sql = Yii::$app->db->createCommand("select cardserial from generated_code where sale_order_id =".$id." and left(cardserial,3)='".$product."'");
		$query = $sql->queryAll();
		return $query;
	}
	
	/* get all generated code details */
	public function getAllCardDetails($col)
	{
		$addon ="";
		$result = array();
		if(isset($col["invoice_no"]) && $col["invoice_no"]!="0"){ $addon .= " and invoice_no='".$col["invoice_no"]."'";}
		if(isset($col["sale_order_no"]) && $col["sale_order_no"]!="0"){ $addon .= " and sale_order_no='".$col["sale_order_no"]."'";}
		if($addon!="")
		{
			$sql = Yii::$app->db->createCommand("select product_code,cardserial,cardcode from view_sale_card_detail where 1=1 ".$addon);
			$query = $sql->queryAll();
			if(count($query)>0)
			{
				foreach($query as $key=>$value)
				{
					if(isset($result[ $value["product_code"] ]))
					{
						$result[ $value["product_code"] ][] = array("cardserial"=>$value["cardserial"],"cardcode"=>$value["cardcode"]);
					}
					else
					{
						$result[ $value["product_code"] ] = array(0=>array("cardserial"=>$value["cardserial"],"cardcode"=>$value["cardcode"]));
					}
				}
			}
		}
		return $result;
	}
	
	/* get sales order list */
	public function getSalesOrderListing()
	{
		$sql = Yii::$app->db->createCommand("select sale_order_id, 
		CASE WHEN dtu_batch <> '' THEN 'DTU' ELSE 'SO' END AS sales_order_mode
		, sale_order_no, theDate as sale_order_date, customer_name, approval_status, invoice_no, ttl ,ttl_cubits from view_sale_order_summary order by sale_order_date desc");
		$query = $sql->queryAll();
		$json="";
		$thestatus = array("0"=>"PENDING","1"=>"APPROVED","-1"=>"REJECTED");
		foreach($query as $key=>$value)
		{
			$json .= ",['".$value["sale_order_no"]."','".$value["sales_order_mode"]."',new Date(".date("Y",strtotime($value["sale_order_date"])).",".(intval(date("m",strtotime($value["sale_order_date"])))-1).",".date("d",strtotime($value["sale_order_date"]))."),'".$value["customer_name"]."','".$thestatus[$value["approval_status"]]."','".$value["invoice_no"]."',{v : ".number_format($value["ttl"],2,'.','').", f : '".number_format($value["ttl"],2,'.',',')."'},{v : ".number_format($value["ttl_cubits"],0,'.','').", f : '".number_format($value["ttl_cubits"],0,'.',',')."'},'".$value["sale_order_id"]."']";
		}
		//var_dump($sql->getText());exit;
		return substr($json,1);
	}
	
	/* get credit_note list */
	public function getCreditNoteListing()
	{
		$sql = Yii::$app->db->createCommand("select credit_note_id, credit_note_no, create_date as credit_note_date, total_amount, approval_status from credit_note order by create_date desc");
		$query = $sql->queryAll();
		$json="";
		$thestatus = array("0"=>"PENDING","1"=>"APPROVED","-1"=>"REJECTED");
		foreach($query as $key=>$value)
		{
			$json .= ",['".$value["credit_note_no"]."',new Date(".date("Y",strtotime($value["credit_note_date"])).",".(intval(date("m",strtotime($value["credit_note_date"])))-1).",".date("d",strtotime($value["credit_note_date"]))."),".number_format(floatval($value["total_amount"]),"3",".","" ).",'".$thestatus[$value["approval_status"]]."','".$value["credit_note_id"]."']";
		}
		//var_dump($sql->getText());exit;
		return substr($json,1);
	}
	
	/* cancel sale order and create credit note */
	public function cancelSaleOrder($col)
	{		
		// get input values
		$id = $col["id"];
		$creditnoteType = $col['creditnote_type'];
		$orderdata = $col["item"];
		$discount = $col['discount'];
		$tax = $col['tax'];
		$total_amount = $col['total_amount'];
		$excel_list = $col["excel"];
		$error_card_list = "";
		
		$not_match = 0;
		$unmatch_item = array();
		$list_to_update_from_excel = array();
		//file check
		if($creditnoteType=="2")
		{
			$counter = count($orderdata["product_code"]);
			//echo "<pre>";var_dump($orderdata["product_code"]);exit;
			//echo "<pre>";var_dump($excel_list);exit;
			if($counter>0)
			{
				for($i=0;$i<$counter;$i++)
				{
					if(isset($excel_list[$orderdata["product_code"][$i]]))
					{ 
						if(count( $excel_list[$orderdata["product_code"][$i]] )!= intval($orderdata["qty"][$i]) )
						{
							$unmatch_item[$orderdata["product_code"][$i]] = count( $excel_list[$orderdata["product_code"][$i]] ) - intval($orderdata["qty"][$i]);
							$not_match++;
						}
						else
						{
							$list_to_update_from_excel[$orderdata["product_code"][$i]] = $excel_list[$orderdata["product_code"][$i]];
						}
					}
				}
			}
			else
			{
				$not_match++;
			}
			
			//not match and stop process
			if($not_match>0){ return $unmatch_item;exit;}
		}
		else if($creditnoteType=="1") // no file check require
		{
			$sql_getcodelist = Yii::$app->db->createCommand("select product_code,cardserial,cardcode from view_sale_card_detail where sale_order_id =".$id);
			$code_list_query = $sql_getcodelist->queryAll();
			if($code_list_query && count($code_list_query)>0)
			{
				foreach($code_list_query as $key=>$value)
				{
					if(!isset($list_to_update_from_excel[$value["product_code"]]))
					{
						$list_to_update_from_excel[$value["product_code"]] = array();
					}
					$list_to_update_from_excel[$value["product_code"]][] = array("cardserial"=>$value["cardserial"],"cardcode"=>$value["cardcode"]);
				}
				//echo "<pre>";var_dump($list_to_update_from_excel);exit;
			}
		}
		//echo "<pre>";var_dump($list_to_update_from_excel);exit;
		//check card status
		$card_check_status = $this->getCubicardStatus($list_to_update_from_excel);
		if($card_check_status && count($card_check_status)>0)
		{
			//cardserial,cardcode,batchid,ownerid,ownermemberid,ownerip,used_date
			foreach($card_check_status as $key=>$value)
			{
				$found = 0;
				if(isset($list_to_update_from_excel[$value["batchid"]]))
				{
					foreach($list_to_update_from_excel[$value["batchid"]] as $key2=>$value2)
					{
						if($value["cardserial"]==$value2["cardserial"] && $value["cardcode"]==$value2["cardcode"])
						{
							$found++;
							$list_to_update_from_excel[$value["batchid"]][$key2]["used"]= (intval($value["ownerid"])>0)?1:0;
							break;
						}
					}
					
					if($found==0)
					{
						$error_card_list .= "serial: ".$value["cardserial"].", code: ".$value["cardcode"]."<br/>";
					}
				}
			}
		}
		else
		{
			$error_card_list = "All Card not found.";
		}
		
		if($error_card_list!="")
		{
			echo "Card Missing!"."<br/>";
			echo $error_card_list;exit;
			echo "<br/>"."Action aborted!";
		}
		//echo "<pre>";var_dump($list_to_update_from_excel);exit;

		$sql = Yii::$app->db->createCommand("select invoice_no,discount_rate, discount from sale_order where sale_order_id =".$id);
		$query = $sql->queryAll();
		
		$invoice_no = $query[0]["invoice_no"];
		$discount_rate = $query[0]["discount_rate"];
		if($creditnoteType=="1"){ 
			$discount = $query[0]["discount"]; 
			//get total amount for full credit
			$sql2 = Yii::$app->db->createCommand("select sum(subtotal) as ttl from (select (product_value * qty) as subtotal from view_sales_order where (sale_order_id = ".$id.")) AS x");
			$query2 = $sql2->queryAll();
			$total_amount = floatval($query2[0]["ttl"])-floatval($discount); 
		}
		$credit_note_no = "CN-".$invoice_no;
		
		if($creditnoteType=="2")
		{
			$credit_note_no = $credit_note_no.'-'.(date('ymdHis'));
		}
		
		try
		{
			$sql = Yii::$app->db->createCommand(
									"insert into credit_note 
									(credit_note_no, sale_order_id, discount_rate,discount,total_amount,credit_type,create_date,create_user,approval_status,tax)
									values
									('".$credit_note_no."','".$id."','".$discount_rate."','".$discount."','".$total_amount."','".$creditnoteType."',getdate(),'".Yii::$app->user->identity->username."',0,'".$tax."')
									");
			$query = $sql->execute();
			$last_id = Yii::$app->db->getLastInsertID();
		}
		catch(Exception $e)
		{
			$flag = false;
			echo $e->getMessage();exit;
		}
		
		//sales order details
		if($last_id>0)
		{
			$temp_sql_list = "";
			if($creditnoteType=="1")
			{
				$sql = Yii::$app->db->createCommand("select product_code, product_desc, product_value, qty,product_tax from sale_order_detail where sale_order_id =".$id);
				$query = $sql->queryAll();
				$temp_sql = "insert into credit_note_detail (credit_note_id, product_code, product_desc, product_value, qty,product_tax) values ";
				foreach($query as $key=>$value)
				{
					$temp_sql_list .= $temp_sql."('".$last_id."','".$value["product_code"]."','".$value["product_desc"]."','".$value["product_value"]."','".$value["qty"]."','".$value["product_tax"]."');"; 
				}
			}
			else
			{
				$counter =count($orderdata["product_code"]);
				if($counter>0)
				{
					$temp_sql = "insert into credit_note_detail (credit_note_id, product_code, product_desc, product_value, qty,product_tax) values ";
					for($i=0;$i<$counter;$i++)
					{
						$temp_sql_list .= $temp_sql."('".$last_id."','".$orderdata["product_code"][$i]."','".$orderdata["product_desc"][$i]."','".$orderdata["unitprice"][$i]."','".$orderdata["qty"][$i]."','".$orderdata["tax"][$i]."');"; 
					}
				}
			}
			
			//echo $temp_sql_list;exit;
			if($temp_sql_list != "")
			{
				try
				{
					$sql = Yii::$app->db->createCommand($temp_sql_list);
					$query = $sql->execute();
					$flag = true;
				}
				catch(Exception $e)
				{
					$flag = false;
					echo $e->getMessage();exit;
				}
			}
			
			if($flag)
			{
				if(count($list_to_update_from_excel)>0)
				{
					foreach($list_to_update_from_excel as $key=>$value){
						foreach($value as $key2=>$value2){
							$sql = Yii::$app->db->createCommand(
							"update generated_code 
							set credit_note='".$last_id."',card_used='".$value2["used"]."'
							where sale_order_id =".$id." and cardserial='".$value2["cardserial"]."' and cardcode='".$value2["cardcode"]."'");
							$query = $sql->execute();
						}
					}
				}
			}
			
			return $last_id;
		}
		
	}
	
	/* check cubicard status */
	public function getCubicardStatus($list)
	{
		$empty = false;
		$query = array();
		if(count($list)>0)
		{
			$list_to_search = "";
			foreach($list as $key=>$value)
			{
				if(count($value)>0)
				{
					foreach($value as $key2=>$value2)
					{
						$list_to_search .= ",'".$value2["cardserial"]."'";
					}
				}
				else
				{
					$empty = true;
					break;
				}
			}
			
			if(!$empty)
			{
				//echo "<pre>";var_dump($list_to_search);exit;
				$sql = Yii::$app->db2->createCommand(
													"select cardserial,cardcode,remarks,invoice_no,batchid,ownerid,ownermemberid,ownerip,used_date,blocked
													from cubicards
													where cardserial in (".substr($list_to_search,1).")"
													);
				$query = $sql->queryAll();
			}
		}
		
		if(!$empty && count($query)>0){ return $query;}else{ return array();}
	}
	
	/* get sales order detail */
	public function getCreditNoteDetail($id)
	{
		$return = array();
		$sql = Yii::$app->db->createCommand(
											"select *
											from view_credit_note
											where credit_note_id='".$id."'"
											);
		$query = $sql->queryAll();
		if(count($query)>0){ return $query;}else{ return array();}
	}
	
	/* credit note */
	public function xeroCreditNote($id)
	{
		$flag = false;
		$sql = Yii::$app->db->createCommand("select * from view_credit_note	where credit_note_id = '".$id."'");
		$query = $sql->queryAll();
		
		if(count($query)>0 && isset($query[0]))
		{
			$discount_amount = $query[0]["discount"];
			$tax = $query[0]["tax"];
			$invoice_no = $query[0]["invoice_no"];
			$creditnoteType	 = $query[0]["credit_type"];
			$credit_note_no = $query[0]["credit_note_no"];
			$create_date = $query[0]["create_date"];
			//prepare for xero
			$lineItems = array();
//		$this->setConfig();	// by hussam
//			$xero = new PrivateApplication($this->config);

			// get invoice
			//$invoiceLookup = $xero->load('Accounting\\Invoice')->where('InvoiceNumber', $invoice_no)->page(1)->execute()[0];
	// HB update for Xero OAuth2.0
			// Setup Xero Connection
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();
			// Prepare conditions or data:
			$whereCond = 'InvoiceNumber=="'. $invoice_no.'"';
			//$orderCond = 'Name ASC';
			// API Request
			$invoiceLookup = $accountingApi->getInvoices($xeroTenantId, null, $whereCond);

			//$invoiceID = $xero->loadByGUID('Accounting\\Invoice', $invoiceLookup["InvoiceID"]);
			$invoiceID = getInvoice($xeroTenantId, $invoiceLookup["InvoiceID"], $unitdp = null);
			//print("<pre>".print_r($invoiceLookup,true)."</pre>");exit;

			// get line items
			$lineItems = array();
			// credit note Amount
			$creditnoteAmount = 0;

			// create line items
			foreach ($invoiceLookup["LineItems"] as $item) {
			  //$lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
			  $lineitem = new \XeroAPI\XeroPHP\Models\Accounting\Invoice\LineItem();
			  // full credit note
			  if ($creditnoteType == '1') {
				$lineitem->setLineItemID($item["LineItemID"])
						  ->setItemCode($item["ItemCode"])
						  ->setDescription($item["Description"])
						  ->setAccountCode($item["AccountCode"])
						  ->setUnitAmount($item["UnitAmount"])
						  ->setTaxType($item["TaxType"])
						  ->setQuantity($item["Quantity"]);
				$lineItems[] = $lineitem;

				$creditnoteAmount = $invoiceLookup["Total"];

			  // partial credit note
			  } else {
					
				foreach($query as $key=>$value)
				{
					if ($value["product_desc"] == $item["Description"]) 
					{
						if ($value["qty"] > 0)
						{
						  $lineitem->setLineItemID($item["LineItemID"])
									->setItemCode($item["ItemCode"])
									->setDescription($value["product_desc"])
									->setAccountCode($item["AccountCode"])
									->setQuantity($value["qty"]);

						  $itemAmount = 0;
						  if ($item["ItemCode"] != 'DISC') { //not discount item
							$itemAmount = $item["UnitAmount"];
							$lineitem->setUnitAmount($itemAmount);
						  }
						  $lineItems[] = $lineitem;

						  $creditnoteAmount += $itemAmount * $value["qty"];
						}
					}
				}
				//set discount
					if($item["ItemCode"] == 'DISC' && $discount_amount>0) {
						$lineitem->setLineItemID($item["LineItemID"])
									->setItemCode($item["ItemCode"])
									->setDescription($item["Description"])
									->setAccountCode($item["AccountCode"])
									->setQuantity($item["Quantity"]);
						$lineitem->setUnitAmount("-".$discount_amount);
						$lineItems[] = $lineitem;
						$creditnoteAmount = $creditnoteAmount-$discount_amount;
					}
			  }
			}

			// get contact
			//$contact = $xero->loadByGUID('Accounting\\Contact', $invoiceLookup["Contact"]["ContactID"]);
	// HB update for Xero OAuth2.0
			// Setup Xero Connection
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();
			// Prepare conditions or data:
//			$whereCond = 'ContactID=="'. $invoiceLookup["Contact"]["ContactID"].'"';
//			$orderCond = 'Name ASC';
			// API Request
//			$contact = $accountingApi->getContact($xeroTenantId, $whereCond, $orderCond);
			$contact = $accountingApi->getContact($xeroTenantId, $invoiceLookup["Contact"]["ContactID"]);

			// create credit note
			//$creditnote = new \XeroPHP\Models\Accounting\CreditNote($xero);
			$creditnote = new \XeroAPI\XeroPHP\Models\Accounting\CreditNote();
			
			//$creditnote->setDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')))
			$creditnote->setDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d',strtotime($create_date))))
			  ->setContact($contact)
			  ->setCreditNoteNumber($credit_note_no)
			  ->setStatus('AUTHORISED')
			  ->setLineAmountType($tax)
			  ->setType('ACCRECCREDIT');

			// add line items to credit note
			foreach($lineItems as $item) {
			  $creditnote->addLineItem($item);
			}

			// save credit note
			$creditnote->save();

			$creditnoteID = $creditnote["CreditNoteID"];

				// set allocation
				//$allocateCN = $xero->loadByGUID('Accounting\\CreditNote', $creditnoteID);
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// Prepare conditions or data:
	//			$whereCond = 'ContactID=="'. $invoiceLookup["Contact"]["ContactID"].'"';
	//			$orderCond = 'Name ASC';
			// API Request
				$allocateCN = $accountingApi->getCreditNote($xeroTenantId, $creditnoteID, $unitdp = null);
				

				//$invoice = new \XeroPHP\Models\Accounting\Invoice($xero);
				$invoice = new \XeroAPI\XeroPHP\Models\Accounting\Invoice();

				$invoice->setInvoiceID($invoiceID);

				//$allocation = new \XeroPHP\Models\Accounting\CreditNote\Allocation($xero);
				$allocation = new \XeroAPI\XeroPHP\Models\Accounting\CreditNote\Allocation();
				$allocation->setInvoice($invoiceID)
					->setAppliedAmount($creditnoteAmount);

				$allocateCN->addAllocation($allocation);
				$allocateCN->save();
				// store
				
				

			// return credit note number
			$cnID = $allocateCN['CreditNoteNumber'];
			if(isset($allocateCN['CreditNoteNumber'])){
				$flag = true;
			}
		}
		return $flag;
	}
		
	/* set sale order approval status */
	public function setApproval($id,$status,$desc="")
	{
		$approval_status = ($status=="yes")?1:-1;
		
		if($approval_status==1 && $status=='yes')
		{
			//push to xero
			$xeroReturn = $this->xeroInvoice($id);
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// Prepare conditions or data:
	//			$whereCond = 'ContactID=="'. $invoiceLookup["Contact"]["ContactID"].'"';
	//			$orderCond = 'Name ASC';
			// API Request
			//	$xeroReturn = $accountingApi->getInvoice($xeroTenantId, $id, $unitdp = null);

			if($xeroReturn)
			{
				$sql = Yii::$app->db->createCommand("update sale_order 
											set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
											where sale_order_id=".$id);
				$query = $sql->execute();	
				//push the code into code db
				$this->pushGeneratedCodeToDB($id); //disable until deployed or test
				echo "<script>window.location.href='index.php?r=pin/viewso&id=".$id."';</script>";exit;
			}
			else
			{
				echo "<script>alert('Failed to push to XERO.')</script>";
			}
		}
		else
		{
			$sql = Yii::$app->db->createCommand("update sale_order 
											set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
											where sale_order_id=".$id);
				$query = $sql->execute();	
				echo "<script>window.location.href='index.php?r=pin/viewso&id=".$id."';</script>";exit;
		}
	}
	
	/* set credit note approval status */
	public function setApprovalCN($id,$status,$desc="")
	{
		$approval_status = ($status=="yes")?1:-1;
		if($approval_status==1 && $status=='yes')
		{
			//push to xero
			////$xeroReturn = $this->xeroCreditNote($id);
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// Prepare conditions or data:
	//			$whereCond = 'ContactID=="'. $invoiceLookup["Contact"]["ContactID"].'"';
	//			$orderCond = 'Name ASC';
			// API Request
			//	$xeroReturn = $accountingApi->getCreditNote($xeroTenantId, $id, $unitdp = null);

			if($xeroReturn)
			{
				$sql = Yii::$app->db->createCommand("update credit_note 
												set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
												where credit_note_id=".$id);
				$query = $sql->execute();

				//block the code in code db
				$this->DeactivatedCodeFromDB($id); //disable until deployed or test
				echo "<script>window.location.href='index.php?r=pin/viewcn&id=".$id."';</script>";exit;
			}
			else
			{
				echo "<script>alert('Failed to push to XERO.')</script>";
			}
		}
		else
		{
			$sql = Yii::$app->db->createCommand("update credit_note 
												set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
												where credit_note_id=".$id);
			$query = $sql->execute();	
			echo "<script>window.location.href='index.php?r=pin/viewcn&id=".$id."';</script>";exit;
		}
	}
	
	/* insert new generated pin into temp table */
	private function newPin($cardserial,$cardcode,$sale_order_id,$detail_id)
	{
		try{
				$sql = Yii::$app->db->createCommand("
					insert into generated_code (cardserial,cardcode,sale_order_id,detail_id,issued_flag)
					values ('".$cardserial."','".$cardcode."','".$sale_order_id."','".$detail_id."',0)
				")->execute();
				$flag = true;
		}catch(Exception $e){ 
			//echo $e->getMessage();exit; 
			$flag = false;
		}
		return $flag;
	}
	
	/*  export the list into excel for finance */
	public function exportCodeList($id)
	{
		$data = false;
		$sql = Yii::$app->db->createCommand("select customer_code, customer_name, sale_order_no, '0' as 'item', product_code, product_desc, cardserial, cardcode, 'B' as 'status'
											from view_sale_card_detail
											where (sale_order_id = '".$id."')
											order by product_code, cardserial");
		$query = $sql->queryAll();
		if(count($query)>0 && isset($query[0]["sale_order_no"])){
			$data["excelformat"] = $query;
			$data["filename"] = 'pin_list_for_SO'.$query[0]["sale_order_no"].'.xls';
		}
		return $data;
	}
	
	//push the generated code into cardcode db if approved
	private function pushGeneratedCodeToDB($id)
	{
		$sql = Yii::$app->db->createCommand("select cardserial,product_code,cardcode, DATEADD(year,5,approval_date) as expire_date, customer_code, approval_date, approval_user, sale_order_no, invoice_no, dtu_batch from view_sale_card_detail where sale_order_id=".$id);
		$query = $sql->queryAll();
		if($query && count($query)>0)
		{
			if($query[0]["dtu_batch"]=="")
			{
				$insert_statement = "";
				//make insert statment list
				foreach($query as $key=>$value)
				{
					$insert_statement .= "insert into cubicards (cardserial,cardcode,batchid,distroid,issued_by,issued_date,expire_date,invoice_no,remarks,blocked)
											values (
											'".$value["cardserial"]."',
											'".$value["cardcode"]."',
											'".$value["product_code"]."',
											'".$value["customer_code"]."',
											'".$value["approval_user"]."',
											'".$value["approval_date"]."',
											'".$value["expire_date"]."',
											'".$value["invoice_no"]."',
											'".$value["sale_order_no"]."',
											0
											);";
				}
				$connection = Yii::$app->db2;
				$transaction = $connection->beginTransaction();
				try
				{
					$sql2 = $connection->createCommand($insert_statement)->execute();
					$transaction->commit();
				}catch(Exception $e)
				{
					$transaction->rollback();
					echo "<pre>";var_dump($e);exit;
				}
			}
		}
	}

	//deactivate cardcode db if approved
	private function DeactivatedCodeFromDB($id)
	{
		$sql = Yii::$app->db->createCommand("select cardserial,cardcode from generated_code where card_used =0 and credit_note=".$id);
		$query = $sql->queryAll();
		if($query && count($query)>0)
		{
			$update_statement = "";
			//make update statment list
			foreach($query as $key=>$value)
			{
				$update_statement .= "update cubicards set blocked='2' where cardserial='".$value["cardserial"]."' and cardcode='".$value["cardcode"]."'";
			}
			$connection = Yii::$app->db2;
			$transaction = $connection->beginTransaction();
			try
			{
				$sql2 = $connection->createCommand($update_statement)->execute();
				$transaction->commit();
			}catch(Exception $e)
			{
				$transaction->rollback();
				echo "<pre>";var_dump($e);exit;
			}
		}
	}

	//create user
	public function createUser($col)
	{
		$flag = false;
		$username	=	isset($col["username"])?$col["username"]:"";
		$password	=	isset($col["password"])?sha1($col["password"]):"";
		$email	=	isset($col["email"])?$col["email"]:"";
		$role	=	isset($col["role"])?$col["role"]:"staff";
		$status	=	isset($col["status"])?$col["status"]:"0";
		
		if($username !="" && $password!="")
		{
			try{
			$sql = Yii::$app->db->createCommand("insert user_login (username,password,email,role,status) values ('".$username."','".$password."','".$email."','".$role."','".$status."')");
			$query = $sql->execute();
			$flag = true;
			}catch(Exception $e)
			{
				$flag = false;
			}
		}
		return $flag;
	}
	
	//change user password
	public function changePassword($password,$id="0")
	{
		$flag = false;
		$userid = ($id!="0")?$id:(Yii::$app->user->identity->id);
		if($password!="")
		{
			try{
			$sql = Yii::$app->db->createCommand("update user_login set password='".$password."' where id=".$userid);
			$query = $sql->execute();
			$flag = true;
			}catch(Exception $e)
			{
				$flag = false;
			}
		}
		return $flag;
	}
	
	//add customer
	public function addCustomer($col)
	{
		$flag = false;

		$customer_id = isset($col["customer_id"])?$col["customer_id"]:"";
		$customer_name = isset($col["customer_name"])?$col["customer_name"]:"";
		$customer_code = isset($col["customer_code"])?$col["customer_code"]:"";
		$customer_flag = isset($col["customer_flag"])?1:0;
		
		if($customer_id !="" && $customer_name!="")
		{
			try{
			$sql = Yii::$app->db->createCommand("insert customer_list values ('".$customer_id."','".$customer_name."','".$customer_code."','".$customer_flag."')");
			$query = $sql->execute();
			$flag = true;
			}catch(Exception $e)
			{
				$flag = false;
			}
		}
		return $flag;
	}
	
	//updateCustomer
	public function updateCustomer($col)
	{
		$flag = false;
		$customer_id = isset($col["customer_id"])?$col["customer_id"]:"";
		$customer_flag = isset($col["customer_flag"])?$col["customer_flag"]:0;
		if($customer_id!="")
		{
			try{
			$sql = Yii::$app->db->createCommand("update customer_list set customer_flag='".$customer_flag."' where customer_id=".$customer_id);
			$query = $sql->execute();
			$flag = true;
			}catch(Exception $e)
			{
				$flag = false;
			}
		}
		return $flag;
	}
	
	//addCubitRate
	public function addCubitRate($col)
	{
		$flag = false;
		$rate_value = isset($col["rate_value"])?$col["rate_value"]:"0";
		$rate_code = isset($col["rate_code"])?$col["rate_code"]:"";
		$rate_currency = isset($col["rate_currency"])?$col["rate_currency"]:"MYR";
		
		try{
			$sql = Yii::$app->db->createCommand("insert into cubitrate (rate_value,rate_code,rate_currency,create_date,create_user) values ('".$rate_value."','".$rate_code."','".$rate_currency."',getdate(),'".Yii::$app->user->identity->username."')");
			$query = $sql->execute();
			$flag = true;
		}catch(Exception $e)
		{
			$flag = false;
		}

		return $flag;
	}
	
	//getCubitRate
	public function getCubitRate()
	{
		$sql = Yii::$app->db->createCommand("select rate_id,rate_code,rate_value,rate_currency,create_date as latest_date from cubitrate a where create_date = 
											(
											select max(create_date) as setup_date from cubitrate 
											where rate_code = a.rate_code
											group by rate_code
											)");
		$query = $sql->queryAll();
		return $query;
	}
	
	//getJournalCode
	public function getJournalCode()
	{
//		$this->setConfig();	// by hussam
//		$xero = new PrivateApplication($this->config);
//		$lookup = $xero->load('Accounting\\TrackingCategory')->execute()[0]["Options"];
		
// HB update for Xero OAuth2.0
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();
		// Prepare conditions or data:
//			$whereCond = 'ContactID=="'. $invoiceLookup["Contact"]["ContactID"].'"';
//			$orderCond = 'Name ASC';
	// API Request
		$xeroReturn = $accountingApi->getTrackingCategories($xeroTenantId, $where = null, $order = null, $include_archived = null);
		
		return $xeroReturn;	//$lookup;
	}
	
	//getCubitRateHistory
	public function getCubitRateHistory($code)
	{
		$sql = Yii::$app->db->createCommand("select * from cubitrate where rate_code='".$code."' order by create_date desc");
		$query = $sql->queryAll();
		return $query;
	}
	
	//get pending dtu
	public function getPendingDTU()
	{
		$mask = array(	"tm"=>"truemoney"
						,"offg"=> "offgamersth"
						);
		
		$reverse_mask = array(
								"truemoney"	=>    "tm" 
								,"offgamersth" =>    "offg" 
								
								);
		
		$sql = Yii::$app->db->createCommand("select customer_code,dtu_batch from sale_order where dtu_batch<>'' order by create_date");
		$query = $sql->queryAll();
		$todayMonth = date("Y-m-d 00:00:00.000");
		$sql2 = Yii::$app->db3->createCommand("select pgw, theDate, sum(ttl) as ttl from 
													(
													select case when trn_ecomm_provider like 'truemoney%' then 'truemoney' else trn_ecomm_provider end AS pgw, trn_grand_total as ttl,DATENAME(d, edit_date) + ' ' + DATENAME(m, edit_date) + ' ' +  CONVERT(varchar(4), DATEPART(year, edit_date)) as theDate 
													from trn_purchase where (edit_date between DATEADD(DAY,-50,'".$todayMonth."') and '".$todayMonth."') and flag_processed='99'
													and CONVERT(datetime, edit_date, 101) >= '12/1/2018'
													) as x
													group by pgw, theDate
											");
											
		
											
		$query2 = $sql2->queryAll();
		//echo "<pre>";var_dump($query2);exit;
		if(count($query)>0 && count($query2)>0)
		{
			foreach($query as $key=>$value)
			{
				foreach($query2 as $key2=>$value2)
				{
					$query2[$key2]["customer"]=$reverse_mask[ strtolower($value2["pgw"]) ];
					if(isset($mask[ strtolower($value["customer_code"])]) && strtolower($value2["pgw"]) == $mask[ strtolower($value["customer_code"])])
					{
						if($value2["theDate"] == $value["dtu_batch"])
						{
							unset($query2[$key2]);
						}
					}
				}
			}
		}
		else
		{
			foreach($query2 as $key2=>$value2)
			{
				$query2[$key2]["customer"]=$reverse_mask[ strtolower($value2["pgw"]) ];
			}
		}
		//echo "<pre>";var_dump($query2);exit;
		return $query2;
	}
	
	//push dtu records
	public function getDirectTopup($col)
	{
		$return = array();
		$return[0] = array();
		$data_date = date("Y-m-d",strtotime($col["dtu_month"]));
		//echo $col["dtu_month"];exit;
		//mask the customer_code with current using trn_ecomm_provider param for now, will improve when revamp the whole thing
		switch( strtolower($col["customer_code"]) )
		{
			case "offg" : $pgw = "offgamersth";
				break;
			case "tm" : $pgw = "truemoney";
				break;
			default : $pgw = "";
				break;
		}
		
		$sql_string = "";
		//use query according to the trn_ecomm_provider for now, will improve when revamp the whole thing
		switch($pgw)
		{
			case "offgamersth" :
							$sql_string = "select b.trn_product_price as value, (b.trn_product_price*10) as cubits, sum(b.trn_product_qty) as qty
											from trn_purchase a, trn_purchase_details b
											where (a.edit_date between '".$data_date." 00:00:00.000' and DATEADD(DAY,+1,'".$data_date." 00:00:00.000'))
											and a.trn_ecomm_provider ='".$pgw."' and flag_processed='99' and flag_credited='1' and a.trn_id = b.trn_id
											group by trn_product_id, trn_product_price";
				break;
				
			case "truemoney" :
							$sql_string = "select  trn_grand_total as value, trn_approval_code as cubits, count(trn_id) as qty
											from trn_purchase
											where (edit_date between '".$data_date." 00:00:00.000' and DATEADD(DAY,+1,'".$data_date." 00:00:00.000'))
											and trn_ecomm_provider like '".$pgw."%' and flag_processed='99'
											group by trn_grand_total,trn_approval_code";
				break;
		}
		
		if($sql_string!="")
		{	
			//start query
			//echo $sql_string;exit;
			$sql = Yii::$app->db3->createCommand($sql_string);
			$query = $sql->queryAll();
			//echo "<pre>";var_dump($query);exit;
			if($query && count($query)>0)
			{
				$return[0] = $query;
			}
		}
		$cur = substr($col["currency"],0,2);
		$return[1] = $cur;
		return $return;
	}
	
	//push allocation to Xero
	public function pushAllocation()
	{
		//echo 'test cb';exit;
		$myJournal = '';
		$game = array();
		$flag = 0;
		$game_sql = Yii::$app->db4->createCommand("select game_code, game_name, game_code_xero, game_type from game_xero_setting");
		$game_query = $game_sql->queryAll();
		if($game_query && count($game_query)>0)
		{
			foreach($game_query as $key=>$value){
				$game[strtolower($value["game_code"])] = array("xero" => $value["game_code_xero"],"name"=> $value["game_name"],"type"=>$value["game_type"]);
			}
		}
		//echo "<pre>";var_dump($game);exit;
		$cubitrate = array();
		$cubitrate_sql = Yii::$app->db->createCommand("select a.rate_code, a.rate_value from cubitrate a 
														where create_date = 
														(select max(create_date) as topdate 
														from cubitrate 
														where rate_code = a.rate_code 
														group by rate_code)
													");
		$cubitrate_query = $cubitrate_sql->queryAll();
		if($cubitrate_query && count($cubitrate_query)>0)
		{
			foreach($cubitrate_query as $key=>$value){
				$cubitrate[$value["rate_code"]] = $value["rate_value"];
			}
		}
		// Day after wanted day m/d/y 00:00:00.000
		//$data_date = "1/22/2021 00:00:00.000";
		$data_date = date("m/d/Y 00:00:00.000");
		
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between DATEADD(DAY,-1,'".$data_date."') and '".$data_date."')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		
		/*
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between '6/30/2019 23:59:58.000' and '7/1/2019 00:00:00.000')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		*/									
		$query = $sql->queryAll();
		//echo date("Y-m-d",strtotime($data_date . "-1 days"));exit;
		//var_dump($query);exit;
		if(count($query)>0)
		//if(1==0)
		{
			// set journal
//		$this->setConfig();	// by hussam
			//$xero = new PrivateApplication($this->config);
			//$journal = new \XeroPHP\Models\Accounting\ManualJournal($xero);
			$journal = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournal();
			$journal->setNarration(date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation');
			$myJournal = '{"Narration": "'.date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation", ';
			
			//$journal->setNarration( ' 31 Oct 2018 CMP Allocation')
			$journal->setDate( \DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days"))) );
			$myJournal = $myJournal.'"Date": "'.\DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days")))->format('Y-m-d').'", ';
			
			$journal->setStatus('POSTED');
			$myJournal = $myJournal.'"Status": "POSTED", ';
			
			$journal->setShowOnCashBasisReports(false);
			$myJournal = $myJournal.'"ShowOnCashBasisReports": "false", ';
			
			// set tracking
			foreach($query as $key=>$value)
			{
				$current_gc = strtolower($value["game_code"]);
				if(isset($game[$current_gc]))
				{
					if($game[$current_gc]["type"]=="mobile")
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('412010')
						//->setAccountCode('468900')
						//setTrackingCategories
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);
						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);
						
						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "412010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';

					}
					else
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('411010')
						//->setAccountCode('468900')
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);

						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);

						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "411010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';
					}
					
					//echo $debit;
					//echo "<br>";
					//echo $credit;
					//echo "<br>";
					$myJournal = $myJournal.'}';
				}
				else
				{
					$flag++;
				}
			}
			if($flag===0)
			{
//				$journal->save();

				//echo "<pre>";var_dump($journal);

				$journalArr = [];	//array();
				array_push($journalArr, $journal);	
				//var_dump($journalArr);

				$journals = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournals();
				$journals->setManualJournals($journalArr);
				//echo $journals;
				//echo "<pre>";var_dump($journals);
				//var_dump($journals);
				//echo "<br>";
				
//				$journali = $journals->getManualJournals();
				//echo "<pre>";var_dump($journali);
				//echo $journali;

//				$journalk = array();
//				array_push($journalk, $journal);
				//echo "<pre>";var_dump($journalk);

				$journalJ = json_encode($journals);
				//echo $journalJ;
				
				//exit();
				
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// API Request
				// create manual journals
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journals);
// 				$response = $accountingApi->createManualJournals($xeroTenantId, $journalk);
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journalJ);

//
			// Get header info from Storage Classe from DB 
				$storage = new StorageClass();
				$xeroTenantId = (string)$storage->getSession()['tenant_id'];
				$access_token = (string)$storage->getSession()['token'];

			// Call the API
				// Set the header fields
				$header = array(
					'Authorization: Bearer '. $access_token,
					'Xero-tenant-id: '. $xeroTenantId,
					'Accept: application/json',
					'Content-Type: application/json'
					//'Content-Type: multipart/form-data'
				);

			// Prepare data:


			// Build and set post fields
				$postjson = $myJournal;

				echo $postjson;
				echo "<br>";
				//exit();

				$ch = curl_init();
				//$url = "https://api.xero.com/api.xro/2.0/Contacts";
				$url = "https://api.xero.com/api.xro/2.0/ManualJournals";
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch, CURLOPT_URL, $url);

				//curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
				curl_setopt($ch, CURLOPT_POST, 1);                //1 for a post request

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);

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

				//$responseObj = json_decode($response);
				//var_dump($responseObj);
				//echo "<br>";

//			
				echo $response;
				echo "<br>";
				echo "success";exit;
			}
		}
		else
		{
			echo "no data";exit;
		}
	}
	
	//push allocation to Xero manual
	public function pushAllocationM()
	{
	    echo 'test cb';exit;
		$myJournal = '';
		$game = array();
		$flag = 0;
		$game_sql = Yii::$app->db4->createCommand("select game_code, game_name, game_code_xero, game_type from game_xero_setting");
		$game_query = $game_sql->queryAll();
		if($game_query && count($game_query)>0)
		{
			foreach($game_query as $key=>$value){
				$game[strtolower($value["game_code"])] = array("xero" => $value["game_code_xero"],"name"=> $value["game_name"],"type"=>$value["game_type"]);
			}
		}
		//echo "<pre>";var_dump($game);exit;
		$cubitrate = array();
		$cubitrate_sql = Yii::$app->db->createCommand("select a.rate_code, a.rate_value from cubitrate a 
														where create_date = 
														(select max(create_date) as topdate 
														from cubitrate 
														where rate_code = a.rate_code 
														group by rate_code)
													");
		$cubitrate_query = $cubitrate_sql->queryAll();
		if($cubitrate_query && count($cubitrate_query)>0)
		{
			foreach($cubitrate_query as $key=>$value){
				$cubitrate[$value["rate_code"]] = $value["rate_value"];
			}
		}
		// Day after wanted day m/d/y 00:00:00.000
		$data_date = "1/22/2021 00:00:00.000";
		//$data_date = date("m/d/Y 00:00:00.000");
		
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between DATEADD(DAY,-1,'".$data_date."') and '".$data_date."')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		
		/*
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between '6/30/2019 23:59:58.000' and '7/1/2019 00:00:00.000')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		*/									
		$query = $sql->queryAll();
		//echo date("Y-m-d",strtotime($data_date . "-1 days"));exit;
		//var_dump($query);exit;
		if(count($query)>0)
		//if(1==0)
		{
			// set journal
//		$this->setConfig();	// by hussam
			//$xero = new PrivateApplication($this->config);
			//$journal = new \XeroPHP\Models\Accounting\ManualJournal($xero);
			$journal = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournal();
			$journal->setNarration(date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation');
			$myJournal = '{"Narration": "'.date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation", ';
			
			//$journal->setNarration( ' 31 Oct 2018 CMP Allocation')
			$journal->setDate( \DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days"))) );
			$myJournal = $myJournal.'"Date": "'.\DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days")))->format('Y-m-d').'", ';
			
			$journal->setStatus('POSTED');
			$myJournal = $myJournal.'"Status": "POSTED", ';
			
			$journal->setShowOnCashBasisReports(false);
			$myJournal = $myJournal.'"ShowOnCashBasisReports": "false", ';
			
			// set tracking
			foreach($query as $key=>$value)
			{
				$current_gc = strtolower($value["game_code"]);
				if(isset($game[$current_gc]))
				{
					if($game[$current_gc]["type"]=="mobile")
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('412010')
						//->setAccountCode('468900')
						//setTrackingCategories
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);
						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);
						
						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "412010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';

					}
					else
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('411010')
						//->setAccountCode('468900')
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);

						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);

						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "411010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';
					}
					
					//echo $debit;
					//echo "<br>";
					//echo $credit;
					//echo "<br>";
					$myJournal = $myJournal.'}';
				}
				else
				{
					$flag++;
				}
			}
			if($flag===0)
			{
//				$journal->save();

				//echo "<pre>";var_dump($journal);

				$journalArr = [];	//array();
				array_push($journalArr, $journal);	
				//var_dump($journalArr);

				$journals = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournals();
				$journals->setManualJournals($journalArr);
				//echo $journals;
				//echo "<pre>";var_dump($journals);
				//var_dump($journals);
				//echo "<br>";
				
//				$journali = $journals->getManualJournals();
				//echo "<pre>";var_dump($journali);
				//echo $journali;

//				$journalk = array();
//				array_push($journalk, $journal);
				//echo "<pre>";var_dump($journalk);

				$journalJ = json_encode($journals);
				//echo $journalJ;
				
				//exit();
				
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// API Request
				// create manual journals
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journals);
// 				$response = $accountingApi->createManualJournals($xeroTenantId, $journalk);
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journalJ);

//
			// Get header info from Storage Classe from DB 
				$storage = new StorageClass();
				$xeroTenantId = (string)$storage->getSession()['tenant_id'];
				$access_token = (string)$storage->getSession()['token'];

			// Call the API
				// Set the header fields
				$header = array(
					'Authorization: Bearer '. $access_token,
					'Xero-tenant-id: '. $xeroTenantId,
					'Accept: application/json',
					'Content-Type: application/json'
					//'Content-Type: multipart/form-data'
				);

			// Prepare data:


			// Build and set post fields
				$postjson = $myJournal;

				echo $postjson;
				echo "<br>";
				//exit();

				$ch = curl_init();
				//$url = "https://api.xero.com/api.xro/2.0/Contacts";
				$url = "https://api.xero.com/api.xro/2.0/ManualJournals";
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch, CURLOPT_URL, $url);

				//curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
				curl_setopt($ch, CURLOPT_POST, 1);                //1 for a post request

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);

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

				//$responseObj = json_decode($response);
				//var_dump($responseObj);
				//echo "<br>";

//			
				echo $response;
				echo "<br>";
				echo "success";exit;
			}
		}
		else
		{
			echo "no data";exit;
		}
	}

	//push allocation to Xero manual
	public function pushAllocationMn()
	{
	    echo 'test cb';exit;
		$myJournal = '';
		$game = array();
		$flag = 0;
		$game_sql = Yii::$app->db4->createCommand("select game_code, game_name, game_code_xero, game_type from game_xero_setting");
		$game_query = $game_sql->queryAll();
		if($game_query && count($game_query)>0)
		{
			foreach($game_query as $key=>$value){
				$game[strtolower($value["game_code"])] = array("xero" => $value["game_code_xero"],"name"=> $value["game_name"],"type"=>$value["game_type"]);
			}
		}
		//echo "<pre>";var_dump($game);exit;
		$cubitrate = array();
		$cubitrate_sql = Yii::$app->db->createCommand("select a.rate_code, a.rate_value from cubitrate a 
														where create_date = 
														(select max(create_date) as topdate 
														from cubitrate 
														where rate_code = a.rate_code 
														group by rate_code)
													");
		$cubitrate_query = $cubitrate_sql->queryAll();
		if($cubitrate_query && count($cubitrate_query)>0)
		{
			foreach($cubitrate_query as $key=>$value){
				$cubitrate[$value["rate_code"]] = $value["rate_value"];
			}
		}
		// Day after wanted day m/d/y 00:00:00.000
		$data_date = "12/31/2020 00:00:00.000";
		//$data_date = date("m/d/Y 00:00:00.000");
		
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between DATEADD(DAY,-1,'".$data_date."') and '".$data_date."')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		
		/*
		$sql = Yii::$app->db5->createCommand("select allocation_game_code as game_code, sum(allocation_cubits) as amount 
											from allocation
											where (edit_date between '6/30/2019 23:59:58.000' and '7/1/2019 00:00:00.000')
											and allocation_sid = 1 and (allocation_product_id = '100' or allocation_product_id = '11' or allocation_product_id = '9')
											and allocation_login_id not in ('4482660','4013529','7008494')
											group by allocation_game_code
											");
		*/									
		$query = $sql->queryAll();
		//echo date("Y-m-d",strtotime($data_date . "-1 days"));exit;
		//var_dump($query);exit;
		if(count($query)>0)
		//if(1==0)
		{
			// set journal
//		$this->setConfig();	// by hussam
			//$xero = new PrivateApplication($this->config);
			//$journal = new \XeroPHP\Models\Accounting\ManualJournal($xero);
			$journal = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournal();
			$journal->setNarration(date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation');
			$myJournal = '{"Narration": "'.date("d M Y",strtotime($data_date . "-1 days")) . ' CMP Allocation", ';
			
			//$journal->setNarration( ' 31 Oct 2018 CMP Allocation')
			$journal->setDate( \DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days"))) );
			$myJournal = $myJournal.'"Date": "'.\DateTime::createFromFormat('Y-m-d', date("Y-m-d",strtotime($data_date . "-1 days")))->format('Y-m-d').'", ';
			
			$journal->setStatus('POSTED');
			$myJournal = $myJournal.'"Status": "POSTED", ';
			
			$journal->setShowOnCashBasisReports(false);
			$myJournal = $myJournal.'"ShowOnCashBasisReports": "false", ';
			
			// set tracking
			foreach($query as $key=>$value)
			{
				$current_gc = strtolower($value["game_code"]);
				if(isset($game[$current_gc]))
				{
					if($game[$current_gc]["type"]=="mobile")
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('412010')
						//->setAccountCode('468900')
						//setTrackingCategories
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);
						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);
						
						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "412010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';

					}
					else
					{
						//$trackingCategory = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory->setName('Game')
						->setOption($game[$current_gc]["xero"]);

						//$trackingCategory2 = new \XeroPHP\Models\Accounting\TrackingCategory($xero);
						$trackingCategory2 = new \XeroAPI\XeroPHP\Models\Accounting\TrackingCategory();
						$trackingCategory2->setName('Assignment')
						->setOption("Game Related");
						
						// set debit line
						//$debit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$debit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$debit->setLineAmount('-' . ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('411010')
						//->setAccountCode('468900')
//						->addTracking($trackingCategory)
//						->addTracking($trackingCategory2);
						->setTracking($trackingCategory)
						->setTracking($trackingCategory2);
						//$journal->addJournalLine($debit);

						// set credit line
						//$credit = new \XeroPHP\Models\Accounting\ManualJournal\JournalLine($xero);
						$credit = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine();
						$credit->setLineAmount( ($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])) )
						->setAccountCode('229010');
						//->setAccountCode('241140');
						//$journal->addJournalLine($credit);

						//$journalLinesArr = array($debit, $credit);
						$journalLinesArr = array();
						array_push($journalLinesArr, $debit);
						array_push($journalLinesArr, $credit);
						$journal->setJournalLines($journalLinesArr);

						$myJournal = $myJournal.'"JournalLines": ['
												.'{"LineAmount": "-'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"'
												.', "AccountCode": "411010"'
												.', "Tracking": [
														{"Game": "'.$game[$current_gc]["xero"].'"},
														{"Name": "Assignment", "Option": "Game Related"}'
												.']}, '
												.'{"LineAmount": "'.($value["amount"]*floatval($cubitrate[ $game[$current_gc]["xero"] ])).'"' 
												.', "AccountCode": "229010"'
												.'}'
												.']';
					}
					
					//echo $debit;
					//echo "<br>";
					//echo $credit;
					//echo "<br>";
					$myJournal = $myJournal.'}';
				}
				else
				{
					$flag++;
				}
			}
			if($flag===0)
			{
//				$journal->save();

				//echo "<pre>";var_dump($journal);

				$journalArr = [];	//array();
				array_push($journalArr, $journal);	
				//var_dump($journalArr);

				$journals = new \XeroAPI\XeroPHP\Models\Accounting\ManualJournals();
				$journals->setManualJournals($journalArr);
				//echo $journals;
				//echo "<pre>";var_dump($journals);
				//var_dump($journals);
				//echo "<br>";
				
//				$journali = $journals->getManualJournals();
				//echo "<pre>";var_dump($journali);
				//echo $journali;

//				$journalk = array();
//				array_push($journalk, $journal);
				//echo "<pre>";var_dump($journalk);

				$journalJ = json_encode($journals);
				//echo $journalJ;
				
				//exit();
				
		// HB update for Xero OAuth2.0
				// Setup Xero Connection
				$accountingApi = $this->newXeroSetup();
				$xeroTenantId = $this->newGetXeroTenantId();
				// API Request
				// create manual journals
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journals);
// 				$response = $accountingApi->createManualJournals($xeroTenantId, $journalk);
//				$response = $accountingApi->createManualJournals($xeroTenantId, $journalJ);

//
			// Get header info from Storage Classe from DB 
				$storage = new StorageClass();
				$xeroTenantId = (string)$storage->getSession()['tenant_id'];
				$access_token = (string)$storage->getSession()['token'];

			// Call the API
				// Set the header fields
				$header = array(
					'Authorization: Bearer '. $access_token,
					'Xero-tenant-id: '. $xeroTenantId,
					'Accept: application/json',
					'Content-Type: application/json'
					//'Content-Type: multipart/form-data'
				);

			// Prepare data:


			// Build and set post fields
				$postjson = $myJournal;

				echo $postjson;
				echo "<br>";
				//exit();

				$ch = curl_init();
				//$url = "https://api.xero.com/api.xro/2.0/Contacts";
				$url = "https://api.xero.com/api.xro/2.0/ManualJournals";
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch, CURLOPT_URL, $url);

				//curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
				curl_setopt($ch, CURLOPT_POST, 1);                //1 for a post request

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);

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

				//$responseObj = json_decode($response);
				//var_dump($responseObj);
				//echo "<br>";

//			
				echo $response;
				echo "<br>";
				echo "success";exit;
			}
		}
		else
		{
			echo "no data";exit;
		}
		
	}
	
	//get tax rate
	public function getTaxRate($taxrate)
	{
		$tax=0;
//		$this->setConfig();	// by hussam
		//$xero = new PrivateApplication($this->config);
		//$lookup = $xero->load('Accounting\\TaxRate')->where("TaxType",$taxrate)->execute();

	// HB update for Xero OAuth2.0
			// Setup Xero Connection
			$accountingApi = $this->newXeroSetup();
			$xeroTenantId = $this->newGetXeroTenantId();
			// Prepare conditions or data:
			$whereCond = 'TaxType=="'. $taxrate.'"';
			//$orderCond = 'Name ASC';
			// API Request
			$lookup = $accountingApi->getTaxRates($xeroTenantId, $whereCond, $order = null, $taxrate);

		if(count($lookup))
		{
			$tax = $lookup[0]->getEffectiveRate();
		}
		return $tax;
	}


	public function genReport()
	{
		$data = array();
		/*
		$sql = "select convert(varchar,create_date,112) as theDate,trn_grand_total as value, trn_approval_code as cubits, count(trn_id) as qty
				from trn_purchase
				where (create_date between '6/1/2018 00:00:00.000' and DATEADD(DAY,+1,'6/30/2018 00:00:00.000'))
				and trn_ecomm_provider like 'truemoney%' and flag_processed='99'
				group by trn_grand_total,trn_approval_code,convert(varchar,create_date,112)
				order by theDate,value,qty
				";
		*/		
		$sql = "select convert(varchar,a.create_date,112) as theDate,b.trn_product_price as value, (b.trn_product_price*10) as cubits, sum(b.trn_product_qty) as qty
				from trn_purchase a, trn_purchase_details b
				where (a.create_date between '6/1/2018 00:00:00.000' and DATEADD(DAY,+1,'6/30/2018 00:00:00.000'))
				and a.trn_ecomm_provider ='offgamersth' and flag_processed='99' and flag_credited='1' and a.trn_id = b.trn_id
				group by trn_product_id, trn_product_price,convert(varchar,a.create_date,112)
				order by theDate,value,qty
				";
		$sql = Yii::$app->db3->createCommand($sql);
		$query = $sql->queryAll();
		foreach($query as $key=>$value)
		{
			$data[$value["theDate"]][$value["value"]] = $value["qty"];
		}
		return $data;
	}
	
	//push Test
	public function pushTest()
	{
		// Setup Xero Connection
		$accountingApi = $this->newXeroSetup();
		$xeroTenantId = $this->newGetXeroTenantId();

		// Prepare conditions or data:
		$whereCond = 'Name=="Trade Customer"';
		$orderCond = 'Name ASC';

		//$result = $accountingApi->getOrganisation($xeroTenantId);
		//$result = $accountingApi->getContactGroups($xeroTenantId);
		$result = $accountingApi->getContactGroups($xeroTenantId, $whereCond, $orderCond)[0];
		$arr = json_decode($result, true);
		
		echo $arr["ContactGroupID"];
		echo "<br>";

// echo $this->getCustomer();
// echo $this->getProduct();
 echo "<br>";
 //var_dump($this->getCustomer());
// var_dump($this->getProduct());
// echo "<br>";
// var_dump($this->getProduct('DTU'));
// echo "<br>";
// var_dump($this->getProduct('CUBI'));
// echo "<br>";
//$this->xeroInvoice();
//var_dump($this->xeroInvoice());
 echo "<br>";


 echo "<br>";

 
		//$result = $accountingApi->getContacts($xeroTenantId);
		//$result = $accountingApi->getItems($xeroTenantId);
		//$result = $accountingApi->getInvoices($xeroTenantId);
		//$result = $accountingApi->getInvoice($xeroTenantId, '2d863f15-4d62-4f10-bc9c-662f67cab0e5');
		//$result = $accountingApi->getItem($xeroTenantId);
		//$result = $accountingApi->getJournals($xeroTenantId);
		//$result = $accountingApi->getManualJournals($xeroTenantId);
		//$result = $accountingApi->getManualJournal($xeroTenantId, '0f40eba3-4881-4121-8974-c67bac1f1bd9');
		//$result = $accountingApi->getTrackingCategories($xeroTenantId);
		
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		//$result = $ex->getAccount($xeroTenantId, $accountingApi, true);
		//$result = $ex->getAccount($xeroTenantId, $accountingApi);
		//$result = $ex->getAccounts($xeroTenantId, $accountingApi);
		//$result = $ex->getContact($xeroTenantId, $accountingApi);
		//$result = $ex->getContacts($xeroTenantId, $accountingApi);
		//$result = $ex->getContactGroup($xeroTenantId, $accountingApi, true);
		//$result = $ex->getCreditNote($xeroTenantId, $accountingApi);
		//$result = $ex->getInvoice($xeroTenantId, $accountingApi);
		//$result = $ex->getItem($xeroTenantId, $accountingApi);
		//$result = $ex->getJournal($xeroTenantId, $accountingApi);
		//$result = $ex->getManualJournal($xeroTenantId, $accountingApi);
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		
		//echo $result;
        exit();

	}
	
	// API request using Library 
	public function pushTestingapi()
	{
		// Storage Classe uses sessions for storing token > extend to your DB of choice
		$storage = new StorageClass();
		$xeroTenantId = (string)$storage->getSession()['tenant_id'];
		$access_token = (string)$storage->getSession()['token'];

		//$configr = new \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );		  
		$configr = new \XeroAPI\XeroPHP\Configuration();
		$configr->getDefaultConfiguration()->setAccessToken($access_token);		  
		$accountingApi = new \XeroAPI\XeroPHP\Api\AccountingApi(
			new \GuzzleHttp\Client(),
			$configr->getDefaultConfiguration()
		);

		$ex = new ExampleClass();
		$ex->init($accountingApi);

		// Prepare conditions or data:
		$whereCond = 'Name=="Trade Customer"';
		$orderCond = 'Name ASC';
		
		//$result = $accountingApi->getOrganisation($xeroTenantId);
		//$result = $accountingApi->getContactGroups($xeroTenantId);
		//$result = $accountingApi->getContactGroups($xeroTenantId, $whereCond, $orderCond)[0];
		
		//$result = $accountingApi->getContacts($xeroTenantId);
		//$result = $accountingApi->getItems($xeroTenantId);
		//$result = $accountingApi->getInvoices($xeroTenantId);
		//$result = $accountingApi->getInvoice($xeroTenantId, '2d863f15-4d62-4f10-bc9c-662f67cab0e5');
		//$result = $accountingApi->getItem($xeroTenantId);
		//$result = $accountingApi->getJournals($xeroTenantId);
		$result = $accountingApi->getManualJournals($xeroTenantId);
		//$result = $accountingApi->getManualJournal($xeroTenantId, '0f40eba3-4881-4121-8974-c67bac1f1bd9');
		//$result = $accountingApi->getTrackingCategories($xeroTenantId);
		
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		//$result = $ex->getAccount($xeroTenantId, $accountingApi, true);
		//$result = $ex->getAccount($xeroTenantId, $accountingApi);
		//$result = $ex->getAccounts($xeroTenantId, $accountingApi);
		//$result = $ex->getContact($xeroTenantId, $accountingApi);
		//$result = $ex->getContacts($xeroTenantId, $accountingApi);
		//$result = $ex->getContactGroup($xeroTenantId, $accountingApi, true);
		//$result = $ex->getCreditNote($xeroTenantId, $accountingApi);
		//$result = $ex->getInvoice($xeroTenantId, $accountingApi);
		//$result = $ex->getItem($xeroTenantId, $accountingApi);
		//$result = $ex->getJournal($xeroTenantId, $accountingApi);
		//$result = $ex->getManualJournal($xeroTenantId, $accountingApi);
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		//$result = $ex->getOrganisation($xeroTenantId, $accountingApi);
		
		echo $result;
        exit();
	}

	// API request using CURL
	public function pushSampleapi()
	{
	// Get header info from Storage Classe from DB 
		$storage = new StorageClass();
		$xeroTenantId = (string)$storage->getSession()['tenant_id'];
		$access_token = (string)$storage->getSession()['token'];

	// Call the API
		// Set the header fields
		$header = array(
			'Authorization: Bearer '. $access_token,
			'Xero-tenant-id: '. $xeroTenantId,
			'Accept: application/json',
			'Content-Type: application/json'
			//'Content-Type: multipart/form-data'
		);

	// Prepare data:


	// Build and set post fields
		$postjson = '';

		echo $postjson;
		echo "<br>";


		$ch = curl_init();
		//$url = "https://api.xero.com/api.xro/2.0/Contacts";
		//$url = "https://api.xero.com/api.xro/2.0/Invoices";
		$url = "https://api.xero.com/api.xro/2.0/ManualJournals";
		
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: ' . 'Bearer ' . $access_token, 'accept: application/json', 'Xero-Tenant-Id: ' . $xero_tenant_id));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request
		//curl_setopt($ch, CURLOPT_POST, 1);                //1 for a post request

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);

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

		echo "The response for API request: ";
		echo "<br>";
		echo $response;	
		echo "<br>";
		
		//$responseObj = json_decode($response);
		//var_dump($responseObj);
		//echo "<br>";
		exit();
	}
	
}
