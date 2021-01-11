<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "social_networks".
 *
 * @property int $id
 * @property string $name
 *
 * @property TgPages[] $tgPages
 */
class SocialNetworks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'social_networks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[TgPages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgPages()
    {
        return $this->hasMany(TgPages::className(), ['social_network' => 'id']);
    }
}
