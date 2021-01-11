<?php
/* @var $this yii\web\View */
?>
<h1>Проверка новых постов</h1>
<!--<form method="post" action="vkchecksub/checksub">-->

<?php
    if ($result_arr == '') {
        echo \yii\bootstrap\Html::a('Проверить новые посты', 'vkchecksub/checksub', ['class' => 'btn btn-default']);
    } else {
        ?>
        <br><br>
        <?php
//        echo "<pre>" . var_dump($result) . "</pre>";;
        foreach ($result_arr as $page_row) {
            $items_row = array_reverse($page_row['items']);
            foreach ($items_row as $row) {
                $json = json_encode($row['text'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//                echo "<pre>" . $json . "</pre>";
                echo "<pre>" . $row['text'] . "</pre>";
            }
        }
    }
?>

<!--</form>-->
