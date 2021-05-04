<?php

namespace app\controllers;

use app\models\TgCommands;
use app\models\TgCurrentlevel;
use app\models\TgUsers;
use Exception;
use Yii;
use yii\web\Controller;

class TgwebhookController extends Controller
{
    public $db;
    public $tg_bot;

    private function handleHook($result = null, $message = null)
    {
        // TODO: Добавить обработку двойного получения нажатия кнопки
        if ($message == null) {
            if (isset($result->_callback_query)) {
                $message = $result->_callback_query->message;
                $text = str_ireplace("tg_", "", $message->data);
            } else {
                $message = $result->_message;
                $text = $message->text;
            }
        } else {
            $text = $message->text;
        }

        // Обрабатываем только приватные сообщения
        if ($message->_chat->type === "private") {
            $this->checkNewUser($message);

            $lastlvl = TgCurrentlevel::find()->where(['user_id' => $message->_chat->id])->one($this->db);

            $command_in = trim(mb_strtolower($text, 'UTF-8'));
            $tgCommand = TgCommands::find()->where(['command_in' => $command_in])->one($this->db);
            $answers = $tgCommand->tgAnswers;

            if ($lastlvl != null){
                if ($lastlvl->currentCommand->wait_answer == 1){
                    $this->switchCaseWaitAnswer($lastlvl, $message);
                    return;
                }
            }

            $this->switchCaseCommandIn($command_in, $message, $answers, $result != null);

            if ($tgCommand != null) {
                HandleHook::writeDownCurrentLevel($message->_chat->id, $tgCommand->id);
            }
            HandleHook::sendMessage(['chat_id' => $message->_chat->id, 'text' => "111__"]);
       }
    }

    private function switchCaseCommandIn($command_in, $message, $answers, $edit)
    {
        $HandleHook = new HandleHook();
        switch ($command_in)
        {
            case "/start":
                $HandleHook->start($message, $answers);
                break;
            case "подключить подписку":
                $HandleHook->plugSubscription($message, $answers);
                break;
            case "отмена":
                $HandleHook->cancel($message);
                break;

            case "vk.com":
                $HandleHook->vkCom($message, $answers, $edit);
                break;
            case "instagram.com":
                $HandleHook->instagramCom($message, $answers, $edit);
                break;

            default:
                //HandleHook::sendMessageToAdmins("Неопознанная команда от @" . $message['from']['username'] . "\nТекст сообщения: " . $text);
                HandleHook::sendMessage([
                    'chat_id' => $message->_chat->id,
                    'text' => "Извините, но я не знаю такой команды.\nДля возвращения в главное меню нажмите /start"
                ]);
                //HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "__" . $command_in]);
                break;
        }
    }

    private function switchCaseWaitAnswer($lastlvl, $message)
    {
        $command_in_id = "";   // '/start'
        $type_of_answer = $lastlvl->currentCommand->typeOfAnswer->type;
        //HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "__" . $type_of_answer]);

        // TODO: Убрать ожидание ответа при нажатии стартовых кнопок
        $WriteDownSubscriptions = new WriteDownSubscriptions();
        switch ($type_of_answer)
        {
            case "link":
                $asd= '';
                break;
            case "vk_linkID":
                $command_in_id = $WriteDownSubscriptions->vk_linkID($message);
                break;

            default:
                HandleHook::sendMessage([
                    'chat_id' => $message->_chat->id,
                    'text' => "Извините, но но получилось обработать ваш запрос.\nДля возвращения в главное меню нажмите /start"
                ]);
                break;
        }

        if ($command_in_id != '') {
            HandleHook::writeDownCurrentLevel($message->_chat->id, $command_in_id);
            $tgCommand = TgCommands::find()->where(['id' => $command_in_id])->one($this->db);
            $message->text = $tgCommand->command_in;
            $this->handleHook(null, $message);

            //HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "1_" . $message['text']]);
        }
    }

    private function checkNewUser($message)
    {
        $user = TgUsers::find()->where(['id' => $message->_chat->id])->one($this->db);
        if ($user == null){
            $userRow = new TgUsers;
            $userRow->id = $message->_chat->id;
            $userRow->username = $message->_from->username;
            $userRow->first_name = $message->_from->first_name;
            $userRow->last_name = $message->_from->last_name;
            $userRow->language_code = $message->_from->language_code;
            $userRow->is_bot = (int)$message->_from->is_bot;
            $userRow->save();
        }
    }

    public function actionHook()
    {
        try {
            $result = $this->tg_bot->getInput();
        } catch (Exception $e) {
            HandleHook::sendMessageToAdmins($e->getMessage());
            die();
        }
//        var_dump($result);
//        die();
        try {
            $this->handleHook($result);
        } catch (Exception $e) {
            HandleHook::sendMessageToAdmins($e->getMessage());
            die();
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSetwebhook()
    {
        if ($_REQUEST['webhookAdr'] != "") {
            $jsonResponse = $this->tg_bot->setWebhook([
                'url' => 'https://' . $_REQUEST['webhookAdr'] . '.ngrok.io/tgwebhook/hook'
            ]);
            return $this->render('index', [
                'result' => $jsonResponse->description
            ]);
        } else {
            return $this->redirect('index');
        }
    }

    public function actionDelwebhook()
    {
        $jsonResponse = $this->tg_bot->deleteWebhook();

        return $this->render('index', [
            'result' => $jsonResponse->description
        ]);
    }
    public function __construct($id, $module, $config = [])
    {
        $this->db = Yii::$app->db;
        $this->db->open();
        $this->tg_bot = Yii::$app->tg_bot;

        parent::__construct($id, $module, $config);
    }

    public function __destruct()
    {
        $this->db->close();
        unset($this->db);
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}
