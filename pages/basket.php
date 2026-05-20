<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/voucher.php';

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    voucherLogout();
    voucherSetFlash('Вы вышли из системы.', 'info');
    voucherRedirect('../index.php');
}

if (!voucherIsAuthorized()) {
    exit;
}

$order = voucherCollectOrder();
if ($order === null) {
    voucherSetFlash('Сначала завершите оформление заказа.', 'error');
    voucherRedirect('order.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_email') {
        $result = voucherSendMail($order);
        voucherSetFlash($result['message'], $result['ok'] ? 'success' : 'error');
        voucherRedirect('basket.php');
    }

    if ($action === 'save_file') {
        $result = voucherSaveOrderToFile($order);
        voucherSetFlash($result['message'], $result['ok'] ? 'success' : 'error');
        voucherRedirect('basket.php');
    }
}

$flash = voucherConsumeFlash();
$services = voucherServices();
$image = voucherServiceImage($order['service_key']);
?>
<html>
    <head>
        <title>Работа</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="../css/style.css" rel="stylesheet" type="text/css">
    </head>

    <body topmargin="0" bottommargin="0" rightmargin="0" leftmargin="0" background="../images/back_main.gif">
        <table cellpadding="0" cellspacing="0" border="0" align="center" width="583" height="614">
            <tr>
                <td valign="top" width="583" height="208" background="../images/row1.gif">
                    <div style="margin-left:88px; margin-top:57px"><img src="../images/w1.gif"></div>
                    <div style="margin-left:50px; margin-top:69px">
                        <a href="../index.php">Главная<img src="../images/m1.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="20" height="10">
                        <a href="order.php">Заказ<img src="../images/m2.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="basket.php">Корзина<img src="../images/m3.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="index-3.php">О компании<img src="../images/m4.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="index-4.php">Контакты<img src="../images/m5.gif" border="0"></a>
                    </div>
                    <div style="margin-left:350px; margin-top:8px; font-size:12px;">
                        Вы зашли как admin
                        <form method="post" style="display:inline; margin-left:8px;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit">Выйти</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top" width="583" height="338" bgcolor="#FFFFFF">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td valign="top" height="338" width="42"></td>
                            <td valign="top" height="338" width="492">
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="492" valign="top" height="106">
                                            <div style="margin-left:1px; margin-top:2px; margin-right:10px"><br>
                                                <div style="margin-left:5px"><img src="../images/1_p1.gif" align="left"></div>
                                                <div style="margin-left:95px"><font class="title">Корзина</font><br></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td valign="top" height="232" width="248">
                                                        <div style="margin-left:6px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                        <div style="margin-left:6px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                        <div style="margin-top:10px; margin-left:6px; font-size:12px;">
                                                            <?php if ($flash): ?>
                                                                <div style="color:<?= $flash['type'] === 'error' ? '#b00000' : '#116611' ?>; margin-bottom:8px;">
                                                                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" width="200" alt="" style="display:block; margin-bottom:8px;">
                                                            <b>Тип услуги:</b> <?= htmlspecialchars($order['service']['label'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <b>Базовая цена:</b> <?= (int)$order['amounts']['base'] ?> руб.<br>
                                                            <b>Машина:</b> <?= htmlspecialchars($order['car']['label'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <b>Цена машины:</b> <?= (int)$order['amounts']['car'] ?> руб.<br>
                                                            <b>Код услуги:</b> <?= htmlspecialchars((string)$order['prep_code'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <?php if ($order['service_key'] === 'sale'): ?>
                                                                <b>Ускоренное оформление:</b> <?= $order['fast_sale'] ? 'Да' : 'Нет' ?><br>
                                                            <?php else: ?>
                                                                <b>Количество дней:</b> <?= (int)$order['days'] ?><br>
                                                            <?php endif; ?>
                                                            <br>
                                                            <b>Дополнительные опции:</b><br>
                                                            <?php foreach ($order['extra_options'] as $option): ?>
                                                                - <?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$option['price'] ?> руб.)<br>
                                                            <?php endforeach; ?>
                                                            <br>
                                                            <b>Предварительная подготовка:</b><br>
                                                            <?php foreach ($order['preparations'] as $option): ?>
                                                                - <?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$option['price'] ?> руб.)<br>
                                                            <?php endforeach; ?>
                                                            <br>
                                                            <b>Клиент:</b><br>
                                                            <?= htmlspecialchars($order['customer']['name'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <?= htmlspecialchars($order['customer']['phone'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <?= htmlspecialchars($order['customer']['email'], ENT_QUOTES, 'UTF-8') ?><br>
                                                        </div>
                                                    </td>
                                                    <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                                    <td valign="top" height="215" width="243">
                                                        <div style="margin-left:22px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                        <div style="margin-left:22px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                        <div style="margin-left:22px; margin-top:13px; font-size:12px;">
                                                            <b>Итоговая сумма:</b> <?= (int)$order['amounts']['total'] ?> руб.<br>
                                                        </div>
                                                        <div style="margin-top:16px;">
                                                            <form method="post" style="display:inline;">
                                                                <input type="hidden" name="action" value="save_email">
                                                                <button type="submit">Отправить на почту</button>
                                                            </form>
                                                            <form method="post" style="display:inline; margin-left:8px;">
                                                                <input type="hidden" name="action" value="save_file">
                                                                <button type="submit">Записать в файл</button>
                                                            </form>
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
                <td valign="top" width="583" height="68" background="../images/row3.gif">
                    <div style="margin-left:51px; margin-top:31px">
                        <a href="#"><img src="../images/p1.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="26" height="9">
                        <a href="#"><img src="../images/p2.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="30" height="9">
                        <a href="#"><img src="../images/p3.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="149" height="9">
                        <a href="index-5.php"><img src="../images/copyright.gif" border="0"></a>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>
