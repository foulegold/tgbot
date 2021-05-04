<?php

namespace app\controllers;

use app\models\TelegrafAccounts;
use Exception;
use SSitdikov\TelegraphAPI\Client\TelegraphClient;
use SSitdikov\TelegraphAPI\Request\CreateAccountRequest;
use SSitdikov\TelegraphAPI\Request\CreatePageRequest;
use SSitdikov\TelegraphAPI\Type\Account;
use SSitdikov\TelegraphAPI\Type\ContentType\ImageType;
use SSitdikov\TelegraphAPI\Type\ContentType\LinkType;
use SSitdikov\TelegraphAPI\Type\Page;
use Yii;
use yii\web\Controller;

class TelegrafController extends Controller
{
    public function actionCreateAccount()
    {
        $telegraph = new TelegraphClient();

        $account = new Account();
        $account->setShortName(Yii::$app->tg_bot->botUsername);
        $account->setAuthorName(Yii::$app->tg_bot->botUsername);
        $account->setAuthorUrl('http://t.me/MyFriendDima_Bot');
        try {
            $account = $telegraph->createAccount(new CreateAccountRequest($account));
        } catch (Exception $e) {}

        $newRow = TelegrafAccounts::addNewRow($account);
        return $this->render('create-account', ['account' => $account]);
    }

    public function actionCreatePage()
    {
        $telegraph = new TelegraphClient();
        $account = TelegrafController::createAccauntModel();

        $page = new Page();
        $page->setTitle('Test article');
        $page->setAuthorName($account->getAuthorName());

        $link = new LinkType();
        $link->setHref('https://telegra.ph/Test-article-05-04');
        $link->setText('Use this method to create a new Telegraph account. Most users only need one account, but this can be useful for channel administrators who would like to keep individual author names and profile links for each of their channels. On success, returns an Account object with the regular fields and an additional access_token field.');

        $image = new ImageType();
        $image->setSrc('http://telegra.ph/file/6a5b15e7eb4d7329ca7af.jpg');

        $page->setContent([$link, $image,]);

        $page = $telegraph->createPage(new CreatePageRequest($page, $account));

        return $this->render('create-page', ['pageUrl' => $page->getUrl()]);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public static function createAccauntModel() :Account
    {
        $TelegrafAccounts = TelegrafAccounts::findOne(['id' => '1']);
        $account = new Account();
        if ($TelegrafAccounts) {
            $account->setShortName($TelegrafAccounts->short_name);
            $account->setAuthorName($TelegrafAccounts->author_name);
            $account->setAuthorUrl($TelegrafAccounts->author_url);
            $account->setAccessToken($TelegrafAccounts->access_token);
            $account->setAuthUrl($TelegrafAccounts->auth_url);
            $account->setPageCount($TelegrafAccounts->page_count);
        }
        return $account;
    }
}
