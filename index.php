<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/voucher.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'login') {
        $login = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (voucherLogin($login, $password)) {
            voucherSetFlash('Вы успешно вошли как admin.', 'success');
            voucherRedirect('pages/order.php');
        }

        voucherSetFlash('Неверный логин или пароль.', 'error');
        voucherRedirect('index.php');
    }

    if ($action === 'logout') {
        voucherLogout();
        voucherSetFlash('Вы вышли из системы.', 'info');
        voucherRedirect('index.php');
    }
}

$isAuthorized = voucherIsAuthorized();
$flash = voucherConsumeFlash();
?>
<html>
    <head>
        <title>Работа</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>

    <body topmargin="0" bottommargin="0" rightmargin="0"  leftmargin="0"   background="images/back_main.gif">
        <table cellpadding="0" cellspacing="0" border="0"  align="center" width="583" height="614">
            <tr>
                <td valign="top" width="583" height="208" background="images/row1.gif">
                    <div style="margin-left:88px; margin-top:57px "><img src="images/w1.gif"></div>
                    <div style="margin-left:50px; margin-top:69px ">
                        <a href="index.php">Главная<img src="images/m1.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="10" height="10">
                        <a href="pages/order.php">Заказ<img src="images/m2.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/basket.php">Корзина<img src="images/m3.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/index-3.php">О компании<img src="images/m4.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/index-4.php">Контакты<img src="images/m5.gif" border="0" ></a>
                    </div>
                    <div style="margin-left:350px; margin-top:8px; font-size:12px;">
                        <?php if ($isAuthorized): ?>
                            Вы зашли как admin
                            <form method="post" style="display:inline; margin-left:8px;">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit">Выйти</button>
                            </form>
                        <?php else: ?>
                            Не авторизован
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top" width="583" height="338"  bgcolor="#FFFFFF">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td valign="top" height="338" width="42"></td>
                            <td valign="top" height="338" width="492">
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="492" valign="top" height="106">
                                            <div style="margin-left:1px; margin-top:2px; margin-right:10px "><br>
                                                <div style="margin-left:5px "><img src="./images/1_p1.gif" align="left"></div>
                                                <div style="margin-left:95px "><font class="title">Авторизация</font><br></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td valign="top" height="232" width="248">
                                                        <div style="margin-left:6px; margin-top:2px; "><img src="./images/hl.gif"></div>
                                                        <div style="margin-left:6px; margin-top:7px; "><img src="./images/1_w2.gif"></div>
                                                        <div style="margin-left:6px; margin-top:12px; font-size:12px;">
                                                            Логин: <b>admin</b><br><br>
                                                            Пароль: <b>123</b>
                                                        </div>
                                                    </td>
                                                    <td valign="top" height="215" width="1" background="./images/tal.gif" style="background-repeat:repeat-y"></td>
                                                    <td valign="top" height="215" width="243">
                                                        <div style="margin-left:22px; margin-top:2px; "><img src="./images/hl.gif"></div>
                                                        <div style="margin-left:22px; margin-top:7px; "><img src="./images/1_w2.gif"></div>
                                                        <div style="margin-left:22px; margin-top:13px; font-size:12px;">
                                                            <?php if ($flash): ?>
                                                                <div style="color:<?= $flash['type'] === 'error' ? '#b00000' : '#116611' ?>; margin-bottom:10px;">
                                                                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (!$isAuthorized): ?>
                                                                <form method="post" action="index.php">
                                                                    <input type="hidden" name="action" value="login">
                                                                    <div style="margin-bottom:8px;">Логин:<br><input type="text" name="login" style="width:190px;"></div>
                                                                    <div style="margin-bottom:10px;">Пароль:<br><input type="password" name="password" style="width:190px;"></div>
                                                                    <button type="submit">Войти</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <div style="margin-bottom:10px;">Доступ к заказу открыт.</div>
                                                                <a href="pages/order.php">Перейти к оформлению</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td valign="top" height="338" width="49"></td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td valign="top" width="583" height="68" background="images/row3.gif">
                    <div style="margin-left:51px; margin-top:31px ">
                        <a href="#"><img src="images/p1.gif" border="0"></a>
                        <img src="images/spacer.gif" width="26" height="9">
                        <a href="#"><img src="images/p2.gif" border="0"></a>
                        <img src="images/spacer.gif" width="30" height="9">
                        <a href="#"><img src="images/p3.gif" border="0"></a>
                        <img src="images/spacer.gif" width="149" height="9">
                        <a href="pages/index-5.php"><img src="images/copyright.gif" border="0"></a>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>
