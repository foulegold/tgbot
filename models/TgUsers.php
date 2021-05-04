<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_users".
 *
 * @property int $id
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $language_code
 * @property int $is_bot
 * @property int $active
 *
 * @property TgChatHistory[] $tgChatHistories
 * @property TgCurrentlevel $tgCurrentlevel
 * @property TgSubscription[] $tgSubscriptions
 */
class TgUsers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'is_bot', 'active'], 'integer'],
            [['username', 'first_name', 'last_name'], 'string', 'max' => 100],
            [['language_code'], 'string', 'max' => 10],
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
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'language_code' => 'Language Code',
            'is_bot' => 'Is Bot',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[TgChatHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgChatHistories()
    {
        return $this->hasMany(TgChatHistory::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[TgCurrentlevel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgCurrentlevel()
    {
        return $this->hasOne(TgCurrentlevel::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[TgSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgSubscriptions()
    {
        return $this->hasMany(TgSubscription::className(), ['user_id' => 'id']);
    }

    public static function checkNewUser($message)
    {
        $user = TgUsers::findOne(['id' => $message->getChat()->id]);
        if ($user == null){
            $newUser = new TgUsers;
            $newUser->id = $message->getChat()->id;
            $newUser->username = $message->getFrom()->username;
            $newUser->first_name = $message->getFrom()->first_name;
            $newUser->last_name = $message->getFrom()->last_name;
            $newUser->language_code = $message->getFrom()->language_code;
            $newUser->is_bot = (int)$message->getFrom()->is_bot;
            $newUser->save();
        }
    }
}
