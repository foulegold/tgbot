<?php

namespace app\controllers;

use aki\telegram\types\Message;
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
            if ($result->getCallback_query() != null) {
                $message = new Message($result->getCallback_query()->message);
                $text = str_ireplace("tg_", "", $result->getCallback_query()->data);
            } else {
                $message = $result->getMessage();
                $text = $message->text;
            }
        } else {
            $text = $message->text;
        }

//        HandleHook::saveVarDump($message);
        // Обрабатываем только приватные сообщения
        if ($message->getChat()->type === "private") {
            TgUsers::checkNewUser($message);

            $lastlvl = TgCurrentlevel::find()->where(['user_id' => $message->getChat()->id])->one($this->db);

            $command_in = trim(mb_strtolower($text, 'UTF-8'));
            $tgCommand = TgCommands::find()->where(['command_in' => $command_in])->one($this->db);
            $answers = $tgCommand->tgAnswers;

            if ($lastlvl != null){
                if ($lastlvl->currentCommand->wait_answer == 1){
                    $this->switchCaseWaitAnswer($lastlvl, $message);
                    die();
                }
            }

            $this->switchCaseCommandIn($command_in, $message, $answers, $result != null);

            if ($tgCommand != null) {
                HandleHook::writeDownCurrentLevel($message->getChat()->id, $tgCommand->id);
            }
//            HandleHook::sendMessage(['chat_id' => $message->getChat()->id, 'text' => "111__"]);
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
                    'chat_id' => $message->getChat()->id,
                    'text' => "Извините, но я не знаю такой команды.\nДля возвращения в главное меню нажмите /start"
                ]);
                HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "__" . $command_in]);
                break;
        }
    }

    private function switchCaseWaitAnswer($lastlvl, $message)
    {
        $command_in_id = "";   // '/start'
        $type_of_answer = $lastlvl->currentCommand->typeOfAnswer->type;
        //HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "__" . $type_of_answer]);

        // TODO: Убрать ожидание ответа при нажатии стартовых кнопок
        switch ($type_of_answer)
        {
            case "link":
                break;
            case "vk_linkID":
                $WriteDownSubscriptions = new WriteDownSubscriptions();
                $command_in_id = $WriteDownSubscriptions->vk_linkID($message);
                break;

            default:
                HandleHook::sendMessage([
                    'chat_id' => $message->getChat()->id,
                    'text' => "Извините, но но получилось обработать ваш запрос.\nДля возвращения в главное меню нажмите /start"
                ]);
                break;
        }

        if ($command_in_id != '') {
            HandleHook::writeDownCurrentLevel($message->getChat()->id, $command_in_id);
            $tgCommand = TgCommands::find()->where(['id' => $command_in_id])->one($this->db);
            $message->text = $tgCommand->command_in;
            $this->handleHook(null, $message);

            //HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "1_" . $message['text']]);
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
//            HandleHook::saveVarDump($e->getMessage());
            die();
        }
        // TODO: Добавить логирование ошибок
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
