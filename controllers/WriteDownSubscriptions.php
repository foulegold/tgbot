<?php

namespace app\controllers;

use app\models\Pages;
use app\models\SocialNetworks;
use app\models\TgCurrentlevel;
use app\models\TgSubscription;
use app\models\VkAuth;
use app\models\VkErrors;
use VK\Client\VKApiClient;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;
use Yii;

class WriteDownSubscriptions
{
    const ANSWER_SUCCESS       = "Подписка успешно подключена.";
    const ANSWER_ERROR         = "Не удалось подключить подписку.\nПричина: ";
    const ANSWER_UNKNOWN_ERROR = "Не удалось подключить подписку.\nПричина: Неизвестная ошибка.";
    const ANSWER_BLOCK_PAGE    = "Не удалось подключить подписку.\nПричина: Страница заблокирована или удалена.";
    const ANSWER_CLOSED_PAGE   = "Не удалось подключить подписку.\nПричина: Страница закрыта.\nМожно подключать только открытые страницы.";

    const REDIRECT_SUCCESS     = 1; // '/start'
    const REDIRECT_ERROR       = 2; // 'подключить подписку'

    public $db;
    public $tg_bot;
    public $vkToken;

    public function vk_linkID($message)
    {
        // Обрабатываем ссылку, чтобы получить ID
        $pageID = $this->handleLink(trim($message['text']));
//        HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "1_" . $pageID]);

        // TODO: Обработать получение группы ВК, не только страницы
        // Получаем страницу в VK
        $vk = new VKApiClient();
        try {
            $response = $vk->users()->get($this->vkToken, [
                'user_ids' => $pageID
            ]);
            $response = $response[0];
        } catch (VKApiException $e) {
            $error_code = $e->getErrorCode();
//            HandleHook::sendMessage(['chat_id' => 347860214, 'text' => '1_' . $e->getErrorCode()]);
        } catch (VKClientException $e) {
            // TODO: Добавить обработчик этого события
//            HandleHook::sendMessage(['chat_id' => 347860214, 'text' => '2_поймал']);
        }

        // Проверка на ошибки и подготовка ответа пользователю
        $succ = false;
        $redirect = static::REDIRECT_ERROR;
        $answer = static::ANSWER_UNKNOWN_ERROR;
        // TODO: Добавить обработку массива в ответе
        if ($error_code != ''){
//            HandleHook::sendMessage(['chat_id' => 347860214, 'text' => "2222"]);
            $error = VkErrors::find()->where(['error_code' => $error_code])->one($this->db);
            if ($error != null) {
                $answer = static::ANSWER_ERROR . $error->error_msg_ru . " \n" . $error->description_ru;
            }
        } else {
            if (isset($response['deactivated'])){
                $answer = static::ANSWER_BLOCK_PAGE;
            } else {
                if ($response['is_closed'] == true){
                    $answer = static::ANSWER_CLOSED_PAGE;
                } elseif ($response['id'] != ''){
                    $answer = static::ANSWER_SUCCESS;
                    $redirect = static::REDIRECT_SUCCESS;
                    $succ = true;
                    $pageID = $response['id'];
                }
            }
        }
//        foreach ($response[0] as $key => $val) {
//        }

        // Запишем подписку в базу
        if ($succ) {
            $this->writeDownSubscription($message, $pageID, 'vk.com');
        }

        return $this->answerToUser($message, $answer, $redirect);
    }

    public function answerToUser($message, $answer, $redirect)
    {
        if ($redirect == static::REDIRECT_SUCCESS){
            HandleHook::writeDownCurrentLevel($message->_chat->id, 1);
            $answers = HandleHook::getAnswersForStartLevel($answer);
            $options = HandleHook::prepareOptions($message, $answers);
            HandleHook::sendMessage($options);
//            HandleHook::sendMessage(['chat_id' => 347860214, 'text' => '____']);
            $res ='';
        } else {
            HandleHook::sendMessage([
                'chat_id' => $message->_chat->id,
                'text' => $answer
            ]);
            $lastlvl = TgCurrentlevel::find()->where(['user_id' => $message->_chat->id])->one($this->db);
            $res = $lastlvl->currentCommand->previous_command_id;
        }

        return $res;
    }

    public function writeDownSubscription($message, $pageID, $social_network_name)
    {
        $social_network = SocialNetworks::find()->where(['name' => $social_network_name])->one($this->db);
        $page = Pages::find()->where(['page_id' => $pageID, 'social_network_id' => $social_network->id])->one($this->db);
        if ($page == null){
            $page = new Pages;
            $page->page_id = $pageID;
            $page->social_network_id = $social_network->id;
            $page->link = 'http://' . $social_network_name . '/id' . $pageID;
            $page->save();
        }

        $vk = new VKApiClient();
        $result = $vk->wall()->get($this->vkToken, [
            'owner_id' => $pageID,
            'count' => 1,
            'filter' => 'owner'
        ]);

        $tg_sub = TgSubscription::find()->where(['user_id' => $message->_chat->id, 'page_id' => $page->page_id])->one($this->db);
        if ($tg_sub == null) {
            $tg_sub = new TgSubscription;
            $tg_sub->user_id = $message->_chat->id;
            $tg_sub->page_id = $page->page_id;
        }
        $tg_sub->active = 1;
        $tg_sub->wall_count = $result['count'];
        $tg_sub->save();
    }

    public function handleLink($text)
    {
        $pageID = '';
        $mas = explode('/', $text);
        if (count($mas) == 1){
            $pageID = str_replace('@', '', $mas[0]);
        } else {
            $masID = explode('?', $mas[count($mas) - 1]);
            $pageID = $masID[0];
            if ($pageID == ''){
                $masID = explode('?', $mas[count($mas) - 2]);
                $pageID = $masID[0];
            }
        }
        return $pageID;
    }

    public function __construct()
    {
        $this->db = Yii::$app->db;
        $this->db->open();
        $this->tg_bot = Yii::$app->tg_bot;

        $vkAuth = VkAuth::find()->where(['username' => '79231656362'])->one($this->db);
        $this->vkToken = $vkAuth->token;
    }

    public function __destruct()
    {
        $this->db->close();
        unset($this->db);
    }
}