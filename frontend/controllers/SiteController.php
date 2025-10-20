<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public $enableCsrfValidation = false; // allow POST from JS

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

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

    /** Home page */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /** Chatbot page (UI only) */
    public function actionChatbot()
    {
        return $this->render('chatbot');
    }

    /** Bridge endpoint between Yii2 frontend and FastAPI backend */
    public function actionChat()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $message = Yii::$app->request->post('message', Yii::$app->request->get('message', ''));

        if (empty($message)) {
            return [
                'topic' => 'EMPTY QUERY',
                'content' => 'Please type something to continue.',
                'url' => ''
            ];
        }

        // FastAPI backend endpoint
        $apiUrl = 'http://127.0.0.1:8000/chat?q=' . urlencode($message);

        try {
            $response = @file_get_contents($apiUrl);
            if ($response === false) {
                throw new \Exception('Failed to connect to FastAPI backend.');
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return [
                    'topic' => 'ERROR',
                    'content' => 'Invalid response format from backend.',
                    'url' => ''
                ];
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'topic' => 'ERROR',
                'content' => 'Backend error: ' . $e->getMessage(),
                'url' => ''
            ];
        }
    }

    /** Login */
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
        return $this->render('login', ['model' => $model]);
    }

    /** Logout */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /** Contact */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', ['model' => $model]);
    }

    /** About */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
