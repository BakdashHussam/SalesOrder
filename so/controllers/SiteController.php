<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if (Yii::$app->user->isGuest) {
            return $this->redirect('index.php?r=site/login');
        }else{
		return  $this->redirect('index.php?r=pin/index');
		}
       
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
	
	public function actionListing()
    {
		$username = isset($_REQUEST["name"])?$_REQUEST["name"]:"";
		$query= array();
		if($username!="")
		{
			$sql = Yii::$app->db3->createCommand("
			select * from (
					select trn_id,trn_scash_value as bonus, 0 as 'topup', 0 as 'allocation', trn_create_user as 'memberid',trn_create_date as 'create_date' from log_promo_exe where trn_create_user='".$username."'
					union
					select trn_id, 0 as 'bonus', topup_value as topup, 0 as 'allocation', memberid, trn_timestamp as 'create_date' from trn_topup where memberid='".$username."'
					union
					select trn_id,0 as 'bonus', 0 as 'topup',  cubits_allocated as  allocation,memberid ,trn_timestamp as 'create_date' from trn_cubitsAllocation where trn_sid =1  and memberid='".$username."'

			) as tbl order by create_date
			");
			$query = $sql->queryAll();
		}
		return $this->render("listing",array("listing"=>$query));
    }
}
