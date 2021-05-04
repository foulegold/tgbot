<?php

namespace app\controllers;

use app\models\TgAdmins;
use app\models\TgAnswers;
use app\models\TgCurrentlevel;
use Yii;

class HandleHook
{
    public $db;
    public $tg_bot;

    public static function prepareOptions($message, $answers)
    {

        $options = array();
        $arr_buttons = array();
        $text = "";
        $reply_markup = '';
        foreach ($answers as $row) {
            if ($row['itsa_button'] == 0) {
                $text .= "\n" . $row['answer'];
            } elseif ($row['inline_button'] == 1) {
                $button = [
                    'text' => $row['answer'],
                    'callback_data' => "tg_" . $row['answer']
                ];
                $arr_buttons[] = $button;
                $reply_markup = ['inline_keyboard' => [$arr_buttons]];
            } else {
                $button = [
                    'text' => $row['answer']
                ];
                $arr_buttons[] = $button;
                $reply_markup = ['keyboard' => [$arr_buttons], 'resize_keyboard' => true];
            }
        }
        if (count($arr_buttons)) {
            $options['reply_markup'] = json_encode($reply_markup);
        }
        $options['chat_id'] = $message->getChat()->id;
        $options['text'] = trim($text);
        //HandleHook::sendMessage(['chat_id' => $message->getChat()->id,'text' => "111_" . $text]);

        return $options;
    }

    public function start($message, $answers)
    {
        $options = HandleHook::prepareOptions($message, $answers);
        HandleHook::sendMessage($options);
    }

    public function plugSubscription($message, $answers)
    {
        $options = HandleHook::prepareOptions($message, $answers);
        HandleHook::sendMessage($options);
    }

    public function vkCom($message, $answers, $edit)
    {
        $this->socialHandler($message, $answers, $edit);
    }

    public function instagramCom($message, $answers, $edit)
    {
        $this->socialHandler($message, $answers, $edit);
    }

    public function socialHandler($message, $answers, $edit)
    {
        $options = HandleHook::prepareOptions($message, $answers);
        if ($edit) {
            $options['message_id'] = $message->message_id;
            HandleHook::editMessageText($options);
        } else {
            HandleHook::sendMessage($options);
        }
    }

    public function writeDownMessageID($message)
    {

    }

    public function cancel($message)
    {
        $answers = HandleHook::getAnswersForStartLevel('Операция отменена');
        $options = HandleHook::prepareOptions($message, $answers);

        Yii::$app->tg_bot->deleteMessage(['chat_id' => $message->getChat()->id, 'message_id' => $message->message_id]);

        //HandleHook::sendMessage(['chat_id' => $message->getChat()->id,'text' => "111_" . $options['message_id']]);
        HandleHook::sendMessage($options);
    }

    public static function getAnswersForStartLevel($text)
    {
        $answers = [];
        $answers_model = TgAnswers::findAll(['command_in_id' => 1, 'itsa_button' => 1, 'active' => 1]);
        foreach ($answers_model as $answ_model) {
            $answ = [];
            foreach ($answ_model as $key => $val) {
                $answ[$key] = $val;
            }
            if ($answ != '') {
                $answers[] = $answ;
            }
        }
        $answers[] = ['answer' => $text, 'itsa_button' => '0', 'inline_button' => '0'];
        return $answers;
    }

    public static function editMessageText($options)
    {
        //if ($options['text'] != $message['text']) {
            $resultResp = Yii::$app->tg_bot->editMessageText($options);
            //HandleHook::sendMessage(['chat_id' => $options['chat_id'], 'text' => "1_" . $resultResp->description]);
//            HandleHook::sendMessage(['chat_id' => $options['chat_id'], 'text' => "3_" . $options['text']]);
//            HandleHook::sendMessage(['chat_id' => $options['chat_id'], 'text' => "4_" . $message['text']]);
            // TODO: Обработать ошибки при отправке сообщения (например проверить не остановил ли юзер бота)
        //}
    }

    public static function sendMessage($options)
    {
        Yii::$app->tg_bot->sendMessage($options);
        // TODO: Обработать ошибки при отправке сообщения (например проверить не остановил ли юзер бота)
    }

    public static function sendMessageToAdmins($text, $getErrors = true)
    {
        $db = Yii::$app->db;
        $db->open();
        $admins = TgAdmins::find()->where(['active' => 1, 'get_errors' => (int)$getErrors])->all($db);
        foreach ($admins as $row) {
            HandleHook::sendMessage([
                'chat_id' => $row['id'],
                'text' => $text
            ]);
        }
    }

    public static function writeDownCurrentLevel($user_id, $command_in_id)
    {
        $lastlvl = TgCurrentlevel::find()->where(['user_id' => $user_id])->one();
        if ($lastlvl == null) {
            $lastlvl = new TgCurrentlevel;
            $lastlvl->user_id = $user_id;
        }
        $lastlvl->current_command_id = $command_in_id;
        $lastlvl->save();
        return $lastlvl;
    }

    public function __construct()
    {
        $this->db = Yii::$app->db;
        $this->db->open();
        $this->tg_bot = Yii::$app->tg_bot;
    }

    public function __destruct()
    {
        $this->db->close();
        unset($this->db);
    }

    public static function saveVarDump($text)
    {
        ob_start();
        var_dump($text);
        file_put_contents('../error.html', ob_get_clean());
        die();
    }
}