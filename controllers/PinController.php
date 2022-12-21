<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Pin;

class PinController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

	public function beforeAction($action)
	{
	  if (!isset(Yii::$app->user->identity->id)) {
            return $this->redirect('index.php?r=site/login');
        }
	  //return true;
	  return true;
	}

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if (!isset(Yii::$app->user->identity->username)) {
            return $this->redirect('index.php?r=site/login');
        }
        return $this->render('index');
    }
	
	public function actionCreateso()
	{
		if(Yii::$app->user->identity->role=="finance")
		{
			return $this->render('no_access');exit;
		}
		$result = array();
		$model = new Pin();
		$customerlist = $model->getCustomer();
		$productlist = $model->getProduct("CUBI");
		$col=array();
		if(isset($_POST["customer"]))
		{
			$col["customer"] = isset($_POST["customer"])?$_POST["customer"]:"";
			$col["customer_name"] = isset($_POST["customer_name"])?$_POST["customer_name"]:"";
			$col["customer_id"] = isset($_POST["customer_id"])?$_POST["customer_id"]:"";
			$col["currency"] = isset($_POST["currency"])?$_POST["currency"]:"MYR";
			$col["discount"] = isset($_POST["discount"])?$_POST["discount"]:"0";
			$col["discount_ori"] = isset($_POST["discount_ori"])?$_POST["discount_ori"]:"0";
			$col["term"] = isset($_POST["term"])?$_POST["term"]:"0";
			$col["tax"] = isset($_POST["tax"])?$_POST["tax"]:"exclusive";
			$col["orderdate"] = isset($_POST["orderdate"])?$_POST["orderdate"]:"";
			$col["orderdata"] = $_POST["orderdata"];
			$pin = new Pin();
			$soid = $pin->addNewSalesOrder($col);
			if($soid>0)
			{
				$this->redirect("index.php?r=pin/viewso&id=".$soid);
			}
		}
		return $this->render('sales_order',array('customerlist'=>$customerlist,'productlist'=>$productlist,'result'=>$result,'postdata'=>$col));
	}
		
	public function actionListso()
	{
		$pin = new Pin();
		$sales_order_list = $pin->getSalesOrderListing();
		return $this->render('sales_order_list',array('sales_order_list'=>$sales_order_list));
	}
	
	public function actionListcn()
	{
		$pin = new Pin();
		$credit_note_list = $pin->getCreditNoteListing();
		return $this->render('credit_note_list',array('credit_note_list'=>$credit_note_list));
	}
	
	public function actionViewso()
	{
		$pin = new Pin();
		$id = (isset($_REQUEST["id"]) && $_REQUEST["id"]!="")?$_REQUEST["id"]:"0";
		if(isset($_POST["approved"]) && $_POST["approved"]=="YES"){ $pin->setApproval($id,"yes");}
		if(isset($_POST["approval_desc"]) && $_POST["approval_desc"]){ $pin->setApproval($id,"no",$_POST["approval_desc"]);}
		list($sales_order_data,$sales_order_details,$credit_note_data)= $pin->getSalesOrderDetail($id);
		return $this->render('sales_order_view',array('sales_order_data'=>$sales_order_data,'sales_order_details'=>$sales_order_details,'credit_note_data'=>$credit_note_data));
	}
	
	public function actionGetsoforcn()
	{
		$col["invoice_no"] = (isset($_POST["invoice_no"]) && $_POST["invoice_no"]!="")?$_POST["invoice_no"]:"0";
		$col["sale_order_no"] = (isset($_POST["sale_order_no"]) && $_POST["sale_order_no"]!="")?$_POST["sale_order_no"]:"0";
		$pin = new Pin();
		$id = $pin->getSOforCN($col);
		$this->redirect("index.php?r=pin/cancelso&id=".$id);
	}
	
	public function actionCancelso()
	{
		/*
		if(Yii::$app->user->identity->role=="finance")
		{
			return $this->render('no_access');exit;
		}
		*/
		$id = (isset($_REQUEST["id"]) && $_REQUEST["id"]!="")?$_REQUEST["id"]:"0";
		$pin = new Pin();
		$col=array();
		$msg="";
		$check_flag = $uploadOk = true;
		$unmatch_item = $card_checker = array();
		if(isset($_POST["creditnote_type"]))
		{
			if($_POST["creditnote_type"]=="2" && isset($_FILES["upload"]["tmp_name"]))
			{
				$target_dir = "uploads/";
				$target_file = $target_dir . basename($_FILES["upload"]["name"]);
				$FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				
				// Allow certain file formats
				if($FileType != "xls" && $FileType != "xlsx" ) {
					$msg = "Sorry, only Excel files are allowed.";
					$uploadOk = false;
				}
				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == false) {
					$msg = "Sorry, your file was not uploaded.";
				// if everything is ok, try to upload file
				} else {
					if (move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)) {
						
						$objPHPExcel = \PHPExcel_IOFactory::load('uploads/'.$_FILES["upload"]["name"]);
						$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
						//echo "<pre>";print_r($sheetData);exit;
						if(count($sheetData)>0)
						{
							foreach($sheetData as $key=>$value)
							{
								if(isset($card_checker[ $value["A"] ]))
								{
									$card_checker[ $value["A"] ][] = array("cardserial"=>$value["B"],"cardcode"=>$value["C"]);
								}
								else
								{
									$card_checker[ $value["A"] ] = array(0=>array("cardserial"=>$value["B"],"cardcode"=>$value["C"]));
								}
							}
							$check_flag = (count($card_checker)>0)?true:false;
						}else{$check_flag=false;}
						
						//echo "<pre>";var_dump($card_checker);exit;
					} else {
						$msg = "Sorry, there was an error uploading your file.";
					}
				}
			}
			
			if($uploadOk==true && $check_flag==true)
			{
				$col["id"]	= $id;
				$col["discount"] = isset($_POST["discount"])?$_POST["discount"]:"0";
				$col["creditnote_type"] = isset($_POST["creditnote_type"])?$_POST["creditnote_type"]:"";
				$col["item"] = $_POST["item"];
				$col["excel"] = $card_checker;
				$col["tax"] = $_POST["tax"];
				$col["total_amount"] = isset($_POST["total_amount"])?$_POST["total_amount"]:"0";
				$cnid = $pin->cancelSaleOrder($col);
				if(is_array($cnid))
				{
					$unmatch_item = $cnid;
					$msg = "Cards quantity uploaded not matched with quantity credited";
				}
				else if($cnid>0)
				{
					$this->redirect("index.php?r=pin/viewcn&id=".$cnid);
				}
			}
		}
		//list($sales_order_data,$sales_order_details)= $pin->getSalesOrderDetail($id);
		$checker = $pin->getSalesOrderToCreditNote($id);
		//return $this->render('sales_order_cancel',array('sales_order_data'=>$sales_order_data,'sales_order_details'=>$sales_order_details,'checker'=>$checker));
		return $this->render('sales_order_cancel',array('checker'=>$checker,'msg'=>$msg,'unmatch_item'=>$unmatch_item));
	}
	
	public function actionCheckcardstatus()
	{
		$uploadOk = true;
		$checklist = false;
		$card_checker = array();
		$msg="";
		$pin = new Pin();
		//use upload
		if(isset($_FILES["upload"]["tmp_name"]))
		{
			
			$target_dir = "uploads/";
			$target_file = $target_dir . basename($_FILES["upload"]["name"]);
			$FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			
			// Allow certain file formats
			if($FileType != "xls" && $FileType != "xlsx" ) {
				$msg = "Sorry, only Excel files are allowed.";
				$uploadOk = false;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == false) {
				$msg = "Sorry, your file was not uploaded.";
			// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)) {
					
					$objPHPExcel = \PHPExcel_IOFactory::load('uploads/'.$_FILES["upload"]["name"]);
					$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
					//echo "<pre>";print_r($sheetData);
					if(count($sheetData)>0)
					{
						foreach($sheetData as $key=>$value)
						{
							if(isset($card_checker[ $value["A"] ]))
							{
								$card_checker[ $value["A"] ][] = array("cardserial"=>$value["B"],"cardcode"=>$value["C"]);
							}
							else
							{
								$card_checker[ $value["A"] ] = array(0=>array("cardserial"=>$value["B"],"cardcode"=>$value["C"]));
							}
						}
					}
					//echo "<pre>";var_dump($card_checker);exit;
				} else {
					$msg = "Sorry, there was an error uploading your file.";
				}
			}
			if($uploadOk && count($card_checker)>0)
			{
				$checklist = $pin->getCubicardStatus($card_checker);
			}
			else if($msg=="")
			{
				$msg = "Sorry, your file was empty.";
			}
		}
		
		//use search
		if(isset($_POST["go"]))
		{
			$col["invoice_no"] = (isset($_POST["invoice_no"]) && $_POST["invoice_no"]!="")?$_POST["invoice_no"]:"0";
			$col["sale_order_no"] = (isset($_POST["sale_order_no"]) && $_POST["sale_order_no"]!="")?$_POST["sale_order_no"]:"0";
			$item = $pin->getAllCardDetails($col);
			//echo "<pre>";var_dump($item);exit;
			if(count($item)>0)
			{
				$checklist = $pin->getCubicardStatus($item);
			}
		}
		
		//use single search
		if(isset($_POST["checksingle"]))
		{
			$col["cardserial"] = (isset($_POST["cardserial"]) && $_POST["cardserial"]!="")?$_POST["cardserial"]:"0";
			//sturcture it with hardcode
			$item[0] = array($col);
			$checklist = $pin->getCubicardStatus($item);
		}
		return $this->render("check_card_status",array("checklist"=>$checklist,"msg"=>$msg));
	}
	
	public function actionViewcn()
	{
		$id = (isset($_REQUEST["id"]) && $_REQUEST["id"]!="")?$_REQUEST["id"]:"0";
		$pin = new Pin();
		if(isset($_POST["approved"]) && $_POST["approved"]=="YES"){ $pin->setApprovalCN($id,"yes");}
		if(isset($_POST["approval_desc"])){ $pin->setApprovalCN($id,"no",$_POST["approval_desc"]);}
		$credit_note_data= $pin->getCreditNoteDetail($id);
		return $this->render('credit_note_view',array('credit_note_data'=>$credit_note_data));
	}
	
	public function actionAjaxcarddetails()
	{
		$id = (isset($_POST["id"]) && $_POST["id"]!="")?$_POST["id"]:"";
		$product = (isset($_POST["product"]) && $_POST["product"]!="")?$_POST["product"]:"";
		$pin = new Pin();
		$details = $pin->getCardDetails($id,$product);
		return json_encode($details);
	}
	
	/*
	public function actionEditso($id)
	{
		$pin = new Pin();
		$success=  false;
		//if update data
		if(isset($_POST["invoice_no"]))
		{
			$col["invoice_no"] = $_POST["invoice_no"];
			$col["id"] = $id;
			$success = $pin->updateSaleOrder($col);
		}
		list($sales_order_data,$sales_order_details) = $pin->getSalesOrderDetail($id);
		return $this->render('sales_order_edit',array('sales_order_data'=>$sales_order_data,'sales_order_details'=>$sales_order_details,'success'=>$success));
	}
	*/
		
	public function actionSetting()
	{
		return $this->render('setting',array());
	}
	
	public function actionCreateuser()
	{
		$success = false;
		if(isset($_POST["username"]))
		{
			$col["username"] = isset($_POST["username"])?$_POST["username"]:"";
			$col["password"] = isset($_POST["password"])?$_POST["password"]:"";
			$col["email"] = isset($_POST["email"])?$_POST["email"]:"";
			$col["role"] = isset($_POST["role"])?$_POST["role"]:"staff";
			$col["status"] = isset($_POST["status"])?$_POST["status"]:"";
			$pin = new Pin();
			$success = $pin->createUser($col);
		}
		
		return $this->render('user_create',array('success'=>$success));
	}
	
	public function actionChangepassword()
	{
		$success = false;
		$password = isset($_POST["password"])?sha1($_POST["password"]):"";
		if($password!="")
		{
			$pin = new Pin();
			$success = $pin->changePassword($password);
		}
		
		return $this->render('user_changepassword',array('success'=>$success));
	}
	
	public function actionManagecustomer()
	{
		$success = false;
		$pin = new Pin();
		if(isset($_POST["addNew"]))
		{
			$col["customer_id"] = isset($_POST["customer_id"])?$_POST["customer_id"]:"";
			$col["customer_name"] = isset($_POST["customer_name"])?$_POST["customer_name"]:"";
			$col["customer_code"] = isset($_POST["customer_code"])?$_POST["customer_code"]:"";
			$col["customer_flag"] = isset($_POST["customer_flag"])?$_POST["customer_flag"]:"";
			
			$success = $pin->addCustomer($col);
		}else if(isset($_POST["update"]))
		{
			$col["customer_id"] = isset($_POST["customer_id"])?$_POST["customer_id"]:"";
			$col["customer_flag"] = isset($_POST["customer_flag"])?$_POST["customer_flag"]:"0";
			$success = $pin->updateCustomer($col);
		}
		$customer_list = $pin->getCustomer();
		return $this->render('manage_customer',array('customer_list'=>$customer_list,'success'=>$success));
	}
	
	public function actionManagecubitrate()
	{
		$success = false;
		$pin = new Pin();
		if(isset($_POST["addNew"]))
		{
			$col["rate_value"] = isset($_POST["rate_value"])?$_POST["rate_value"]:"0";
			$col["rate_code"] = isset($_POST["rate_code"])?$_POST["rate_code"]:"unknown";
			$col["rate_currency"] = isset($_POST["rate_currency"])?$_POST["rate_currency"]:"MYR";
			
			$success = $pin->addCubitRate($col);
		}
		$rate_list = $pin->getCubitRate();
		$game_code = $pin->getJournalCode();
		return $this->render('manage_cubitrate',array('rate_list'=>$rate_list,'game_code'=>$game_code,'success'=>$success));
	}
	
	public function actionCubitratehistory()
	{
		$id = isset($_POST["id"])?$_POST["id"]:"0";
		$pin = new Pin();
		$history_list = $pin->getCubitRateHistory($id);
		echo json_encode($history_list);
	}
	
	public function actionExport()
	{
		$id = (isset($_REQUEST["id"]) && $_REQUEST["id"]!="")?$_REQUEST["id"]:"0";
		$pin = new Pin();
		$data = $pin->exportCodeList($id);
		if($data!==false)
		{
			$file = \Yii::createObject([
				'class' => 'codemix\excelexport\ExcelFile',
				'sheets' => [

					'Pin' => [
						'data' => $data["excelformat"],
						'titles' => ['Customer', 'Name', 'Sales Document','Sales Document Item','Material','Material Description','Serial Number','Pin','S/N Status'],
					],
				]
			]);
			$filename = $data["filename"];
			$file->send($filename);
		}
		else
		{
			echo "<script>alert('Failed to export, please check with system administrator.');</script>";
		}
	}
	
	public function actionGetpendingdtu()
	{
		if(Yii::$app->user->identity->role=="finance")
		{
			return $this->render('no_access');exit;
		}
		$result = array();
		$pin = new Pin();
		$result = $pin->getPendingDTU();
		$customerlist = $pin->getCustomer();
		return $this->render('sales_order_dtu_list',array('customerlist'=>$customerlist,"result"=>$result));
	}
	
	public function actionCreatedtuso()
	{
		if(Yii::$app->user->identity->role=="finance")
		{
			return $this->render('no_access');exit;
		}
		$result = array();
		$model = new Pin();
		$tax_rate = 7;
		//$customerlist = $model->getCustomer();
		$productlist = $model->getProduct("DTU");
		$cubicard_list = $order_details = $col=array();
		$currency="";
		
		if(isset($_POST["submitDTU"]) && $_POST["submitDTU"]=="go")
		{
			//echo "<pre>";var_dump($_POST["orderdata"]);exit;
			$col["customer"] = isset($_POST["customer"])?$_POST["customer"]:"";
			$col["customer_name"] = isset($_POST["customer_name"])?$_POST["customer_name"]:"";
			$col["customer_id"] = isset($_POST["customer_id"])?$_POST["customer_id"]:"";
			$col["currency"] = isset($_POST["currency"])?$_POST["currency"]:"MYR";
			$col["discount"] = isset($_POST["discount"])?$_POST["discount"]:"0";
			$col["discount_ori"] = isset($_POST["discount_ori"])?$_POST["discount_ori"]:"0";
			$col["term"] = isset($_POST["term"])?$_POST["term"]:"0";
			$col["tax"] = isset($_POST["tax"])?$_POST["tax"]:"exclusive";
			$col["orderdate"] = isset($_POST["orderdate"])?$_POST["orderdate"]:"";
			$col["orderdata"] = $_POST["orderdata"];
			$col["dtu_month"] = isset($_POST["dtu_month"])?$_POST["dtu_month"]:"";
			$pin = new Pin();
			$soid = $pin->addNewSalesOrder($col,1);
			if($soid>0)
			{
				$this->redirect("index.php?r=pin/viewso&id=".$soid);
			}
		}
		else if(isset($_POST["customer"]))
		{
			$col["customer_code"] = isset($_POST["customer"])?$_POST["customer"]:"";
			$col["currency"] = isset($_POST["currency"])?$_POST["currency"]:"MYR";
			$col["orderdate"] = date("Y-m-d");
			$col["dtu_month"] = isset($_POST["dtu_month"])?date("Y-m-d",strtotime($_POST["dtu_month"])):date("Y-m-d",strtotime(date("Y-m-d")." -1 days"));
			
			$pin = new Pin();
			list($order_details,$currency) = $pin->getDirectTopup($col);
			
			if(count($productlist)>0)
			{
				foreach($productlist as $key=>$value)
				{
					$temp = array();
					$temp["value"]= $value["product_value"];
					$temp["desc"] = $value["product_desc"];
					$temp["code"] = $value["product_code"];
					$temp["currency"] = substr($value["product_code"],0,2);
					
					if(!isset($cubicard_list[ $temp["currency"] ]))
					{
						$cubicard_list[ $temp["currency"] ] = array();
					}
					$cubicard_list[ $temp["currency"] ][] = $temp;
				}
			}
		}
		else
		{
			return $this->render('sales_order_dtu',array('no_data'=>1));
		}
		//echo "<pre>";var_dump($order_details);var_dump($cubicard_list);exit;
		return $this->render('sales_order_dtu',array('cubicard_list'=>$cubicard_list,'order_details'=>$order_details,"currency"=>$currency,"tax_rate"=>$tax_rate));
	}
	
	public function actionLoadcustomerlist()
	{
		$model = new Pin();
		$customerlist = $model->getCustomer();
		$json = array();
		foreach($customerlist as $key=>$value)
		{
			$temp = array();
			$temp["name"] = $value["customer_name"];
			$temp["code"] = $value["customer_code"];
			$temp["currency"] =	$value["currency"];
			array_push($json,$temp);
		}
		echo json_encode($json);
	}
	
	public function actionAllocation()
	{
		$pin = new Pin();
		$pin->pushAllocation();
	}
	
	public function actionAllocationm()
	{
		$pin = new Pin();
		$pin->pushAllocationM();
	}
	public function actionTaxrate()
	{
		$taxtype = (isset($_POST["taxtype"]))?$_POST["taxtype"]:"";
		$pin = new Pin();
		echo $pin->getTaxRate($taxtype);exit;
	}
	public function actionTestingapi()
	{
		$pin = new Pin();
		$pin->pushTestingapi();
	}
	
	
	public function actionTempsl()
	{
		$pin = new Pin();
		$data = $pin->genReport();
		return $this->render('genrep',array('data'=>$data));
	}
}
