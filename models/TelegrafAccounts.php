<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "telegraf_accounts".
 *
 * @property int $id
 * @property string $short_name
 * @property string $author_name
 * @property string $author_url
 * @property string $access_token
 * @property string $auth_url
 * @property int $page_count
 */
class TelegrafAccounts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegraf_accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_count'], 'integer'],
            [['short_name', 'author_name', 'author_url', 'access_token', 'auth_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'short_name' => 'Short Name',
            'author_name' => 'Author Name',
            'author_url' => 'Author Url',
            'access_token' => 'Access Token',
            'auth_url' => 'Auth Url',
            'page_count' => 'Page Count',
        ];
    }

    public static function addNewRow($account)
    {
        $TelegrafAccounts               = new TelegrafAccounts();
        $TelegrafAccounts->short_name   = $account->getShortName();
        $TelegrafAccounts->author_name  = $account->getAuthorName();
        $TelegrafAccounts->author_url   = $account->getAuthorUrl();
        $TelegrafAccounts->access_token = $account->getAccessToken();
        $TelegrafAccounts->auth_url     = $account->getAuthUrl();
        $TelegrafAccounts->page_count   = $account->getPageCount();
        $TelegrafAccounts->save();
        return $TelegrafAccounts;
    }
}
