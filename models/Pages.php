<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "pages".
 *
 * @property int $page_id ID профиля
 * @property int|null $social_network_id Соц. сеть
 * @property string $link Ссылка
 *
 * @property SocialNetworks $socialNetwork
 * @property TgSubscription[] $tgSubscriptions
 */
class Pages extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_id', 'link'], 'required'],
            [['page_id', 'social_network_id'], 'integer'],
            [['link'], 'string', 'max' => 255],
            [['page_id'], 'unique'],
            [['social_network_id'], 'exist', 'skipOnError' => true, 'targetClass' => SocialNetworks::className(), 'targetAttribute' => ['social_network_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'page_id' => 'Page ID',
            'social_network_id' => 'Social Network ID',
            'link' => 'Link',
        ];
    }

    /**
     * Gets query for [[SocialNetwork]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSocialNetwork()
    {
        return $this->hasOne(SocialNetworks::className(), ['id' => 'social_network_id']);
    }

    /**
     * Gets query for [[TgSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgSubscriptions()
    {
        return $this->hasMany(TgSubscription::className(), ['page_id' => 'page_id']);
    }
}
