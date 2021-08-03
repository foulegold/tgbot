<?php

namespace app\controllers;

use aki\telegram\types\InputMedia\InputMediaPhoto;
use aki\telegram\types\MessageEntity;
use app\models\TgSubscription;
use app\models\VkAuth;
use app\models\VkErrors;
use VK\Client\VKApiClient;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;
use Yii;

class VkchecksubController extends \yii\web\Controller
{
    public $db;
    public $tg_bot;
    public $vkToken;

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionChecksub()
    {
        $this->setPrimaryOptions();

        $user_id = '347860214';    // убрать
        $TgSubscriptions = TgSubscription::find()->where(['user_id' => $user_id, 'active' => '1'])->all($this->db);

        $result_arr = [];
        $error_code = '';
        $vk = new VKApiClient();
        foreach ($TgSubscriptions as $row){
            try {
                // Сначала запрашиваем 1 пост, чтобы узнать общее количество.
                // Потом сравниваем с полученным и получаем нужное количество.
                $result = $vk->wall()->get($this->vkToken, [
                    'owner_id' => $row->page_id,
                    'count' => 2,
                    'filter' => 'owner'
                ]);
                $newPostsCount = 1;
                if (isset($result['items'])) {
                    foreach ($result['items'] as $item_row) {
                        if ($item_row['is_pinned'] == 1) {
                            continue;
                        }
                        $newPostsCount = $item_row['id'] - $row->lastPostId;
                    }
                }
                $newPostsCount = 2;    // убрать
                if ($newPostsCount <= 0) {
                    // TODO: Сообщить, что нет новых постов для веб версии
                    // Ничего не делать, если это автоматический запрос
                    continue;
                }
                if ($newPostsCount > 1){
                    $result = $vk->wall()->get($this->vkToken, [
                        'owner_id' => $row->page_id,
                        'count' => $newPostsCount + 1,  // +1 для проверки на закрепленное сообщение
                        'filter' => 'owner'
                    ]);
                }
                $items_row = array_reverse($result['items']); // items — это массив постов
//                var_dump($items_row);
//                die();
                foreach ($items_row as $item_row) {
                    $row->lastPostId = $row->lastPostId - 2;    // убрать
                    // Так как количество постов может быть огромным, проверяем а не старое лиэто сообщение
                    if ($row->lastPostId < $item_row['id']) {
                        // Не ваводим закрепленное сообщение, если оно старое
                        if ($item_row['is_pinned'] == 1 and $item_row['id'] < $row->lastPostId) {
                            continue;
                        }
                        // TODO: Добавить приветственное сообщение, чтобы было понятно, от кого оно
                        // С помощью этой махинации получаем только текст сообщения
    //                    $post_text = json_encode($item_row['text'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    //                    $post_text = str_ireplace('"', '', $post_text);
    //                    $post_text = str_ireplace('\n', '\n', $post_text);
                        $text_photo = '';
                        $photo_url = [];
                        if ($item_row['attachments']) {
                            foreach ($item_row['attachments'] as $attach) {
                                if ($attach['type'] == 'photo') {
                                    $max_height = 0;
                                    $max_size = $attach['photo']['sizes'][0];
                                    foreach ($attach['photo']['sizes'] as $size) {
                                        if ($size['height'] > $max_height) {
                                            $max_height = $size['height'];
                                            $max_size = $size;
                                        }
                                    }

                                    $photo_url[] = $max_size['url'];
                                }
                            }
                        }
                        foreach ($photo_url as $val) {
                            $text_photo .= '[ ](' . $val . ')';
                        }

                        $postsText = $item_row['text'];
                        $this->checkTextForUrls($postsText);

                        // TODO: Обработать текст поста на символы, используемые в parse_mode
                        $answ_opt = [
                            'chat_id' => $user_id,
                            'text' => $postsText,
                            'parse_mode' => 'Markdown',
                        ];
                        if (count($photo_url) <= 1) {
                            $answ_opt['text'] = $text_photo . $postsText;
                        }
                        HandleHook::sendMessage($answ_opt);

                        // Если в посте больше одной фотки, то отправляем их отдельным сообщением
                        if (count($photo_url) > 1) {
                            // TODO: Добавить обработку количества фоток. Разрешено отправлять по 2-10 штук.
                            $media = [];
                            foreach ($photo_url as $val){
                                $media[] = new InputMediaPhoto([
                                    'type' => 'photo',
                                    'media' => $val
                                ]);
                            }

                            $answ_opt = [
                                'chat_id' => $user_id,
                                'media' => $media
                            ];
                            $resultResp = Yii::$app->tg_bot->sendMediaGroup($answ_opt);
                        }
                        $row->lastPostId = $item_row['id'];
                        $row->save();

                        $result_arr[$row->page_id] = $result;
                    }
                }
            } catch (VKApiException $e) {
                $error_code = $e->getErrorCode();
            } catch (VKClientException $e) {
                // TODO: Добавить обработчик этого события
                $error_code = $e->getErrorCode();
            }

            if ($error_code != '') {
                $result[$row->page_id]['error_code'] = $error_code;
//                $error = VkErrors::find()->where(['error_code' => $error_code])->one($this->db);
//                if ($error != null) {
//                    $result[$row->page_id]['error_code'] = $error->error_msg_ru . " \n" . $error->description_ru;
//                }
            }
        }
        return $this->render('index', ['result_arr' => $result_arr]);
    }

    private function setPrimaryOptions()
    {
        $this->db = Yii::$app->db;
        $this->db->open();
        $this->tg_bot = Yii::$app->tg_bot;

        $vkAuth = VkAuth::find()->where(['username' => '792316563621'])->one($this->db);
        $this->vkToken = $vkAuth->token;
    }

    // Обработка на возможные ссылки в тексте поста
    private function checkTextForUrls(&$postsText)
    {
        $posStartUrl = stripos($postsText, '\[id');
        while ($posStartUrl) {
            var_dump($postsText);
            die();
            $posEndId = stripos($postsText, '|', $posStartUrl);
            $posEndUrl = stripos($postsText, ']', $posEndId);
            $userId = mb_substr($postsText, $posStartUrl + 1, $posEndId - $posStartUrl);

            $entity = new MessageEntity;
            $entity->type = 'url';
            $entity->offset = $posStartUrl;
            $entity->length = strlen($userId);
            $entity->url = 'https://vk.com/' . $userId;

            $posStartUrl = stripos($postsText, '[id', $posEndUrl);
        }
    }
}
