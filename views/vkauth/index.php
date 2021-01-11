<?php
/* @var $this yii\web\View */


if ($token == "")
{
    //var_dump($_REQUEST['username']);
    echo '<form method="get" action="auth">
        <p>username <input name="username" type="text" value="'.$_REQUEST['username'].'"></p>
        <p>password <input name="password" type="text" value="'.$_REQUEST['password'].'"></p>
        <p>2fa code <input name="code" type="text"></p>
        <button>Отправить</button>
      </form>';
}
else
{
    echo '<p>token: '.$token.'</p>';
}
?>
