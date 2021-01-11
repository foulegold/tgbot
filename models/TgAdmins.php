<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_admins".
 *
 * @property int $id
 * @property string $username
 * @property int $active
 * @property int $get_errors Получать ошибки
 */
class TgAdmins extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_admins';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'active', 'get_errors'], 'integer'],
            [['username'], 'string', 'max' => 100],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'active' => 'Active',
            'get_errors' => 'Get Errors',
        ];
    }
}
