<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_subscription".
 *
 * @property int $id
 * @property int $user_id ID tg пользователя
 * @property int $page_id ID профиля
 * @property int $active Активность
 * @property int $wall_count
 *
 * @property TgUsers $user
 * @property Pages $page
 */
class TgSubscription extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'page_id'], 'required'],
            [['user_id', 'page_id', 'active', 'wall_count'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => TgUsers::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pages::className(), 'targetAttribute' => ['page_id' => 'page_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'page_id' => 'Page ID',
            'active' => 'Active',
            'wall_count' => 'Wall Count',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(TgUsers::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Page]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Pages::className(), ['page_id' => 'page_id']);
    }
}
