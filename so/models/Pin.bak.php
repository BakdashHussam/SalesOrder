<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


use XeroPHP\Application\PrivateApplication;


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
		// test site
		$this->config = [
			'oauth' => [
				'callback'         => 'http://localhost/',
				'consumer_key'     => '8XA5W9DVCNMCR0QVIGVERZWFH9ENTX',
				'consumer_secret'  => 'C2TC2VYQOXFKAJTBQZ1ABIKPS8IZ1Z',
				'signature_location'    => \XeroPHP\Remote\OAuth\Client::SIGN_LOCATION_QUERY,
				'rsa_private_key'  => file_get_contents("file:///C:/inetpub/wwwroot/basic/cert/xero.pem"),
			],
			'curl' => [
				CURLOPT_USERAGENT   => 'cubinetdemo2',
			]
		];

		// live site
		// $this->config = [
		//   'oauth' => [
		//       'callback'         => 'http://localhost/',
		//       'consumer_key'     => 'WDDLGLDY2UCJQXB0TRASI4UGH0NM23',
		//       'consumer_secret'  => 'PIEGQS9H82GCRSXDMNIJSK3ZMI07LT',
		//       'signature_location'    => \XeroPHP\Remote\OAuth\Client::SIGN_LOCATION_QUERY,
		//       'rsa_private_key'  => 'file:///Users/won/Documents/Work/_CLIENT/Cubinet/Xero/certs/privatekey.pem',
		//   ],
		//   'curl' => [
		//       CURLOPT_USERAGENT   => 'CISB',
		//   ]
		// ];
		
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
		$sql = Yii::$app->db->createCommand("select sale_order_id, product_code, qty
											from sale_order_detail
											where sale_order_id='".$id."'");
		$query = $sql->queryAll();
		$failed_cardserial = "";
		foreach($query as $key=>$value)
		{
			$sale_order_id	= $value["sale_order_id"];
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
				$success = $this->newPin($cardserial,$cardcode,$sale_order_id);
				if(!$success)
				{
					//second attempt
					$cardcode = str_shuffle($cardcode);
					$success = $this->newPin($cardserial,$cardcode,$sale_order_id);
					if(!$success) 
					{
						//last attempt
						$cardcode = str_shuffle($cardcode);
						$success = $this->newPin($cardserial,$cardcode,$sale_order_id);
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
		$this->setConfig();
		$customer_list = array();
		$temp = array();
		
		$xero = new PrivateApplication($this->config);
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
		return ($customer_list);
	}
	
	/* get product list */
	public function getProduct($type="")
	{
		$this->setConfig();
		$itemlist = array();
		$xero = new PrivateApplication($this->config);
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
		$lookup = $xero->load('Accounting\\Item')->where($mode)->orderBy('Name', 'asc')->execute();
		//echo "<pre>";var_dump($lookup);exit;
		foreach ($lookup as $item) {
			$itemlist[$item["Code"]] = array(
											"product_value"=>$item["SalesDetails"]["UnitPrice"],
											"product_code"=>$item["Code"],
											"product_desc"=>$item["Name"]
											);
        }
		unset($itemlist["DISC"]);
		ksort($itemlist);
		return ($itemlist);
	}
	
	/* get create sales order */
	public function addNewSalesOrder($col)
	{
		$last_id=0;
		$flag = false;
		$orderdate	=	isset($col["orderdate"])?$col["orderdate"]:"";
		$customer	=	isset($col["customer"])?$col["customer"]:"";
		$customer_id	=	isset($col["customer_id"])?$col["customer_id"]:"";
		$customer_name	=	isset($col["customer_name"])?$col["customer_name"]:"";
		$customer_currency	=	isset($col["currency"])?$col["currency"]:"MYR";
		$discount_rate = 	isset($col["discount"])?$col["discount"]:"0";
		$term = 	isset($col["term"])?$col["term"]:"0";
		$tax = 	isset($col["tax"])?$col["tax"]:"inclusive";
		//echo "<pre>"; var_dump($col);exit;
		$temp = $col["orderdata"];
		$counter = count($temp["product"]);
		$amount = 0;
		$discount_amount = 0;
		
		if(count($col["orderdata"])>0 && $discount_rate>0)
		{
			
			for($i=0;$i<$counter;$i++)
			{
				$amount += floatval($temp["price"][$i]) * floatval($temp["qty"][$i]);
			}
			$discount_amount = ($amount*$discount_rate)/100;
		}
		//sales order
		try
		{
			$sql = Yii::$app->db->createCommand(
									"insert into sale_order 
									(sale_order_no, sale_order_date, customer_id, customer_code, customer_name, discount_rate,discount,term,tax,approval_status,create_date,create_user)
									select CAST(max(sale_order_no) AS bigint)+1 ,'".$orderdate."','".$customer_id."','".$customer."','".$customer_name."','".$discount_rate."','".$discount_amount."','".$term."','".$tax."',0,getdate(),'".Yii::$app->user->identity->username."' from sale_order
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
			if(count($col["orderdata"])>0)
			{
				$temp_sql = "insert into sale_order_detail (sale_order_id, product_code, product_desc, product_value, qty) values ";
				for($i=0;$i<$counter;$i++)
				{
					$temp_sql_list .= $temp_sql."('".$last_id."','".$temp["product"][$i]."','".$temp["desc"][$i]."','".$temp["price"][$i]."','".$temp["qty"][$i]."');"; 
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
		
		if($flag===true)
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
		
		if(count($query)>0 && isset($query[0]))
		{
			$amount = 0;
			$discount_amount = $query[0]["discount"];
			$discount_rate = $query[0]["discount_rate"];
			$term =  $query[0]["term"];
			$tax =  $query[0]["tax"];
			$customer_name = $query[0]["customer_name"];
			$so_no = $query[0]["sale_order_no"];
		
			//prepare for xero
			$lineItems = array();
			$this->setConfig();
			$xero = new PrivateApplication($this->config);
		
			foreach($query as $key=>$value)
			{
				//$amount += floatval($value["product_value"]) * floatval($value["qty"]);
				
				$amount += floatval($value["product_value"]);
				
				$itemLookup = $xero->load('Accounting\\Item')->where('Code', $value["product_code"])->execute()[0];
				$lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
				$lineitem->setItemCode($value["product_code"])
						->setTaxType($itemLookup["SalesDetails"]["TaxType"])
						->setQuantity($value["qty"]);

				$lineItems[] = $lineitem;
				
				
				if($discount_rate != '' || $discount_rate > 0) {
				$itemLookup = $xero->load('Accounting\\Item')->where('Code', 'DISC')->execute()[0];
				  $lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);
				  $lineitem->setDescription($discount_rate."% discount")
							->setItemCode('DISC')
							->setUnitAmount("-".$discount_amount)
							->setTaxType($itemLookup["SalesDetails"]["TaxType"])
							->setQuantity(1);

				  $lineItems[] = $lineitem;
				}
			}
			
			
			$contactLookup = $xero->load('Accounting\\Contact')->where('Name', $customer_name)->execute()[0];
			$contact = $xero->loadByGUID('Accounting\\Contact', $contactLookup["ContactID"]);
				
			//push into xero
			$today = strtotime(date('Y-m-d'));
			$dueDate = strtotime('+' . $term . ' day', $today);
			$setDueDate = date('Y-m-d',$dueDate);

			$invoice = new \XeroPHP\Models\Accounting\Invoice($xero);
			$invoice->setDueDate(\DateTime::createFromFormat('Y-m-d', $setDueDate))
				->setType(\XeroPHP\Models\Accounting\INVOICE::INVOICE_TYPE_ACCREC)
				->setStatus('AUTHORISED')
				->setCurrencyCode($contact["DefaultCurrency"])
				->setContact($contact)
				->setReference($so_no);
				
			// add line items to invoice
			foreach($lineItems as $item) {
			  $invoice->addLineItem($item);
			}

			// save invoice
			$invoice->save();

			// return invoice number
			if(isset( $invoice['InvoiceNumber']) ){
				$invoice_no = $invoice['InvoiceNumber'];
				try{
					$sql = Yii::$app->db->createCommand("update sale_order set invoice_no='".$invoice_no."' where sale_order_id=".$id);
					$query = $sql->execute();
				}catch(Exception $e){ }
			}
		}
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
											"select product_code, product_desc, product_value, qty
											from sale_order_detail
											where sale_order_id='".$id."'"
											);
		$query2 = $sql2->queryAll();
		if(count($query2)>0){ $return[1] = $query2;}else{ $return[1]=array(); }
		//var_dump($sql->getText());exit;
		return $return;
	}
	
	/* get generated code details */
	public function getCardDetails($id,$product)
	{
		$sql = Yii::$app->db->createCommand("select cardserial from generated_code where sale_order_id =".$id." and left(cardserial,3)='".$product."'");
		$query = $sql->queryAll();
		return $query;
	}
	
	/* get sales order list */
	public function getSalesOrderListing()
	{
		$sql = Yii::$app->db->createCommand("select sale_order_id, sale_order_no, sale_order_date, customer_name, approval_status from sale_order order by sale_order_date desc");
		$query = $sql->queryAll();
		$json="";
		$thestatus = array("0"=>"PENDING","1"=>"APPROVED","-1"=>"REJECTED");
		foreach($query as $key=>$value)
		{
			$json .= ",['".$value["sale_order_no"]."','".date("d M Y",strtotime($value["sale_order_date"]))."','".$value["customer_name"]."','".$thestatus[$value["approval_status"]]."','".$value["sale_order_id"]."']";
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

		$sql = Yii::$app->db->createCommand("select invoice_no,discount_rate, discount from sale_order where sale_order_id =".$id);
		$query = $sql->queryAll();
		
		$invoice_no = $query[0]["invoice_no"];
		$discount_rate = $query[0]["discount_rate"];
		if($creditnoteType=="1"){ $discount = $query[0]["discount"]; }
		$credit_note_no = "CN".$invoice_no;
		
		if($creditnoteType=="2")
		{
			$credit_note_no = $credit_note_no.'-'.(date('ymdHis'));
		}
		
		try
		{
			$sql = Yii::$app->db->createCommand(
									"insert into credit_note 
									(credit_note_no, sale_order_id, discount_rate,discount,credit_type,create_date,create_user,approval_status)
									values
									('".$credit_note_no."','".$id."','".$discount_rate."',".$discount.",'".$creditnoteType."',getdate(),'".Yii::$app->user->identity->username."',0)
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
				$sql = Yii::$app->db->createCommand("select product_code, product_desc, product_value, qty from sale_order_detail where sale_order_id =".$id);
				$query = $sql->queryAll();
				$temp_sql = "insert into credit_note_detail (credit_note_id, product_code, product_desc, product_value, qty) values ";
				foreach($query as $key=>$value)
				{
					$temp_sql_list .= $temp_sql."('".$last_id."','".$value["product_code"]."','".$value["product_desc"]."','".$value["product_value"]."','".$value["qty"]."');"; 
				}
			}
			else
			{
				$counter =count($orderdata["product_code"]);
				if($counter>0)
				{
					$temp_sql = "insert into credit_note_detail (credit_note_id, product_code, product_desc, product_value, qty) values ";
					for($i=0;$i<$counter;$i++)
					{
						$temp_sql_list .= $temp_sql."('".$last_id."','".$orderdata["product_code"][$i]."','".$orderdata["product_desc"][$i]."','".$orderdata["unitprice"][$i]."','".$orderdata["qty"][$i]."');"; 
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
			return $last_id;
		}
		
	}
	
	/* credit note */
	public function xeroCreditNote($col)
	{
		$this->setConfig();
		$xero = new PrivateApplication($this->config);
		
		// get input values
		$id = isset($col["id"])?$col["id"]:"0";
		$invoiceNumber = $col["invoice_no"];
		$creditnoteType = $col['type'];
		$orderdata = $col["orderdata"];

		// get invoice
		$invoiceLookup = $xero->load('Accounting\\Invoice')->where('InvoiceNumber', $invoiceNumber)->page(1)->execute()[0];
		$invoiceID = $xero->loadByGUID('Accounting\\Invoice', $invoiceLookup["InvoiceID"]);
		// print("<pre>".print_r($invoiceLookup,true)."</pre>");

		// get line items
		$lineItems = array();
		// credit note Amount
		$creditnoteAmount = 0;
		// unique id for partial credit note
		$uniqid = '';

		// create line items
		foreach ($invoiceLookup["LineItems"] as $item) {
		  $lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);

		  // full credit note
		  if ($creditnoteType == '1') {
			$lineitem->setLineItemID($item["LineItemID"])
					  ->setItemCode($item["ItemCode"])
					  ->setDescription($item["Description"])
					  ->setAccountCode($item["AccountCode"])
					  ->setUnitAmount($item["UnitAmount"])
					  ->setQuantity($item["Quantity"]);
			$lineItems[] = $lineitem;

			$creditnoteAmount = $invoiceLookup["Total"];

		  // partial credit note
		  } else {
			for ($i=0; $i < count($orderdata["product"]); $i++) {
			  if ($orderdata["desc"] == $item["Description"]) {
				if ($orderdata["qty"][$i] > 0) {
				  $lineitem->setLineItemID($item["LineItemID"])
							->setItemCode($item["ItemCode"])
							->setDescription($orderdata["desc"][$i])
							->setAccountCode($item["AccountCode"])
							->setQuantity($orderdata["qty"][$i]);

				  $itemAmount = 0;
				  if ($item["ItemCode"] != 'DISC') { //not discount item
					$itemAmount = $item["UnitAmount"];
					$lineitem->setUnitAmount($itemAmount);
				  } else {
					$itemAmount = $discountAmount * -1;
					$lineitem->setUnitAmount($itemAmount);
				  }
				  $lineItems[] = $lineitem;

				  $creditnoteAmount += $itemAmount * $orderdata["qty"][$i];
				}
			  }
			}
			$uniqid = '-' .data("YmdHis");
		  }
		}

		// get contact
		$contact = $xero->loadByGUID('Accounting\\Contact', $invoiceLookup["Contact"]["ContactID"]);

		// create credit note
		$creditnote = new \XeroPHP\Models\Accounting\CreditNote($xero);
		$creditnote->setDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')))
		  ->setContact($contact)
		  ->setCreditNoteNumber('CN-' . $invoiceLookup["InvoiceNumber"] . $uniqid)
		  ->setStatus('AUTHORISED')
		  ->setType('ACCRECCREDIT');

		// add line items to credit note
		foreach($lineItems as $item) {
		  $creditnote->addLineItem($item);
		}

		// save credit note
		$creditnote->save();

		$creditnoteID = $creditnote["CreditNoteID"];

			// set allocation
			$allocateCN = $xero->loadByGUID('Accounting\\CreditNote', $creditnoteID);

			$invoice = new \XeroPHP\Models\Accounting\Invoice($xero);
			$invoice->setInvoiceID($invoiceID);

			$allocation = new \XeroPHP\Models\Accounting\CreditNote\Allocation($xero);
			$allocation->setInvoice($invoiceID)
				->setAppliedAmount($creditnoteAmount);

			$allocateCN->addAllocation($allocation);
			$allocateCN->save();

		// return credit note number
		$cnID = $allocateCN['CreditNoteNumber'];
		
		
		try{
			$sql = Yii::$app->db->createCommand("insert into credit_note (credit_note_no,sale_order_id,discount,credit_tye) values ('".$cnID."','".$id."','".$creditnoteType."')");
			$query = $sql->execute();
		}catch(Exception $e){ echo $e->getMessage();exit;return false;}
		
		try{
			$sql = Yii::$app->db->createCommand("update generated_code set credit_note = '".$cnID."',credit_note_status='cancel' where sale_order_id=".$id);
			$query = $sql->execute();
		}catch(Exception $e){ echo $e->getMessage();exit;return false;}
	}
	
	/* set sale order approval status */
	public function setApproval($id,$status,$desc="")
	{
		$approval_status = ($status=="yes")?1:-1;
		$sql = Yii::$app->db->createCommand("update sale_order 
											set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
											where sale_order_id=".$id);
		$query = $sql->execute();
		
		if($approval_status==1 && $status=='yes')
		{
			//push the code into code db
			//$this->pushGeneratedCodeToDB($id); //disable until deployed or test
			
			//push to xero
			$this->xeroInvoice($id);
			echo "<script>window.location.href='index.php?r=pin/viewso&id=".$id."';</script>";exit;
		}
	}
	/* set credit note approval status */
	public function setApprovalCN($id,$status,$desc="")
	{
		$approval_status = ($status=="yes")?1:-1;
		$sql = Yii::$app->db->createCommand("update credit_note 
											set approval_status='".$approval_status."',approval_user='".Yii::$app->user->identity->username."',approval_desc='".$desc."', approval_date=getdate()
											where sale_order_id=".$id);
		$query = $sql->execute();
		
		if($approval_status==1 && $status=='yes')
		{
			//block the code in code db
			//$this->DeactivatedCodeFromDB($id); //disable until deployed or test
			
			//push to xero
			$this->xeroCreditNote($id);
			echo "<script>window.location.href='index.php?r=pin/viewso&id=".$id."';</script>";exit;
		}
	}
	
	/* insert new generated pin into temp table */
	private function newPin($cardserial,$cardcode,$sale_order_id)
	{
		try{
				$sql = Yii::$app->db->createCommand("
					insert into generated_code (cardserial,cardcode,sale_order_id,issued_flag)
					values ('".$cardserial."','".$cardcode."','".$sale_order_id."',0)
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
		$sql = Yii::$app->db->createCommand("select cardserial,product_code,cardcode, DATEADD(year,5,approval_date) as expire_date, customer_code, approval_date, approval_user, sale_order_no, invoice_no from view_sale_card_detail where sale_order_id=".$id);
		$query = $sql->queryAll();
		if($query && count($query)>0)
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
	
}
