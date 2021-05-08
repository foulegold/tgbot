<?php

namespace app\controllers;

use aki\telegram\types\InputMedia\InputMediaPhoto;
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

        $user_id = '347860214';
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
                    'count' => 1,
                    'filter' => 'owner'
                ]);
                $newPostsCount = $result['count'] - $row->wall_count;
                $newPostsCount = 2;
                if ($newPostsCount <= 0){
                    // TODO: Сообщить, что нет новых постов. На случай, когда ручной запрос.
                    // Ничего не делать, если это автоматический запрос
                    continue;
                }
                if ($newPostsCount > 1){
                    $result = $vk->wall()->get($this->vkToken, [
                        'owner_id' => $row->page_id,
                        'count' => $newPostsCount,
                        'filter' => 'owner'
                    ]);
                }
                $items_row = array_reverse($result['items']); // items — это массив постов
                foreach ($items_row as $item_row) {
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

                    // TODO: Обработать текст поста на символы, используемые в parse_mode
                    $answ_opt = [
                        'chat_id' => $user_id,
                        'text' => $item_row['text'],
                        'parse_mode' => 'Markdown',
                    ];
                    if (count($photo_url) <= 1) {
                        $answ_opt['text'] = $text_photo . $item_row['text'];
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
                    $row->wall_count = $result['count'];
                    $row->save();

                    $result_arr[$row->page_id] = $result;
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

        $vkAuth = VkAuth::find()->where(['username' => '79231656362'])->one($this->db);
        $this->vkToken = $vkAuth->token;
    }

}
