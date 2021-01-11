<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_commands".
 *
 * @property int $id
 * @property string $command_in
 * @property int $wait_answer
 * @property int|null $type_of_answer
 * @property int $previous_command_id
 *
 * @property TgAnswers[] $tgAnswers
 * @property TgTypesOfAnswers $typeOfAnswer
 * @property TgCurrentlevel[] $tgCurrentlevels
 */
class TgCommands extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_commands';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['command_in'], 'required'],
            [['wait_answer', 'type_of_answer', 'previous_command_id'], 'integer'],
            [['command_in'], 'string', 'max' => 255],
            [['type_of_answer'], 'exist', 'skipOnError' => true, 'targetClass' => TgTypesOfAnswers::className(), 'targetAttribute' => ['type_of_answer' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'command_in' => 'Command In',
            'wait_answer' => 'Wait Answer',
            'type_of_answer' => 'Type Of Answer',
            'previous_command_id' => 'Previous Command ID',
        ];
    }

    /**
     * Gets query for [[TgAnswers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgAnswers()
    {
        return $this->hasMany(TgAnswers::className(), ['command_in_id' => 'id']);
    }

    /**
     * Gets query for [[TypeOfAnswer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTypeOfAnswer()
    {
        return $this->hasOne(TgTypesOfAnswers::className(), ['id' => 'type_of_answer']);
    }

    /**
     * Gets query for [[TgCurrentlevels]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgCurrentlevels()
    {
        return $this->hasMany(TgCurrentlevel::className(), ['current_command_id' => 'id']);
    }
}
