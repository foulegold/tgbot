<?php

namespace app\controllers;

use yii\web\Controller;

class TelegrafController extends Controller
{
    public function actionCreateAccount()
    {
        return $this->render('create-account');
    }

    public function actionCreatePage()
    {
        return $this->render('create-page');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

}
