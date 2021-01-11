<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_answers".
 *
 * @property int $id
 * @property int $command_in_id Id входящей команды
 * @property string $answer Команда для ответа пользователю
 * @property int $itsa_button
 * @property int $inline_button Является ли поле кнопкой клавиатуры
 * @property int $active
 *
 * @property TgCommands $commandIn
 */
class TgAnswers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_answers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['command_in_id', 'answer'], 'required'],
            [['command_in_id', 'itsa_button', 'inline_button', 'active'], 'integer'],
            [['answer'], 'string', 'max' => 255],
            [['command_in_id'], 'exist', 'skipOnError' => true, 'targetClass' => TgCommands::className(), 'targetAttribute' => ['command_in_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'command_in_id' => 'Command In ID',
            'answer' => 'Answer',
            'itsa_button' => 'Itsa Button',
            'inline_button' => 'Inline Button',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[CommandIn]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCommandIn()
    {
        return $this->hasOne(TgCommands::className(), ['id' => 'command_in_id']);
    }
}
