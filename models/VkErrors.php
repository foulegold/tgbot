<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vk_errors".
 *
 * @property int $error_code
 * @property string $error_msg_en
 * @property string $error_msg_ru
 * @property string $description_en
 * @property string $description_ru
 */
class VkErrors extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vk_errors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['error_code'], 'required'],
            [['error_code'], 'integer'],
            [['error_msg_en', 'error_msg_ru', 'description_en', 'description_ru'], 'string', 'max' => 255],
            [['error_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'error_code' => 'Error Code',
            'error_msg_en' => 'Error Msg En',
            'error_msg_ru' => 'Error Msg Ru',
            'description_en' => 'Description En',
            'description_ru' => 'Description Ru',
        ];
    }
}
