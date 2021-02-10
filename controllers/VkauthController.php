<?php

namespace app\controllers;

use app\models\VkAuth;
use Rework;

class VkauthController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionAuth()
    {
        //var_dump($_REQUEST['username']);
        //$token = '';
        $username = trim($_REQUEST['username']);
        if ($username != "") {
            $token = Rework::token($username, trim($_REQUEST['password']), trim($_REQUEST['code']))['access_token'];

            if ($token != ''){
                $vkAuth = VkAuth::find()->where(['username' => $username])->one();
                if ($vkAuth == null) {
                    $vkAuth = new VkAuth;
                    $vkAuth->username = $username;
                }
                $vkAuth->token = $token;
                $vkAuth->save();
            }
        }
        return $this->render('index', ['token' => $token]);
    }

}
