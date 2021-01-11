<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_currentlevel".
 *
 * @property int $user_id
 * @property int $current_command_id
 *
 * @property TgUsers $user
 * @property TgCommands $currentCommand
 */
class TgCurrentlevel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_currentlevel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'current_command_id'], 'integer'],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => TgUsers::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['current_command_id'], 'exist', 'skipOnError' => true, 'targetClass' => TgCommands::className(), 'targetAttribute' => ['current_command_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'current_command_id' => 'Current Command ID',
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
     * Gets query for [[CurrentCommand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentCommand()
    {
        return $this->hasOne(TgCommands::className(), ['id' => 'current_command_id']);
    }
}
