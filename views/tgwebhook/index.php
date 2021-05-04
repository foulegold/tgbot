<?php
/* @var $this yii\web\View */

echo "<h1>result: {$result}</h1>";

if (Yii::$app->user->isGuest):

else:
    if (!$_REQUEST['webhookAdr']):
?>
<form action="setwebhook" method="post" name="form">
    <p><input type="text" id="webhookAdr" name="webhookAdr">
    <button>Установить webhook</button></p>
<?php
    endif;
?>
</form>
<form action="delwebhook" method="post" name="form">
    <p><button>Удалить webhook</button></p>
</form>
<?php
endif;
?>