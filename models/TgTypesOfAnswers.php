<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tg_types_of_answers".
 *
 * @property int $id
 * @property string $type
 *
 * @property TgCommands[] $tgCommands
 */
class TgTypesOfAnswers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tg_types_of_answers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
        ];
    }

    /**
     * Gets query for [[TgCommands]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTgCommands()
    {
        return $this->hasMany(TgCommands::className(), ['type_of_answer' => 'id']);
    }
}
