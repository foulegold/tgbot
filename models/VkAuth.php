<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vk_auth".
 *
 * @property string $username
 * @property string $token
 */
class VkAuth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vk_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'token'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['token'], 'string', 'max' => 255],
            [['username'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'token' => 'Token',
        ];
    }
}
