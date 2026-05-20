<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/voucher.php';

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    voucherLogout();
    voucherSetFlash('Вы вышли из системы.', 'info');
    voucherRedirect('../api/index.php');
}

if (!voucherIsAuthorized()) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'order_next') {
    $data = voucherStep1FromRequest($_POST);
    $errors = voucherValidateStep1($data);

    if ($errors) {
        voucherSetFlash(implode(' ', $errors), 'error');
        $_SESSION[VOUCHER_DATA_KEY] = $data;
        voucherRedirect('order.php');
    }

    $_SESSION[VOUCHER_DATA_KEY] = $data;
    unset($_SESSION[VOUCHER_STEP2_KEY]);
    voucherRedirect('bill.php');
}

$flash = voucherConsumeFlash();
$services = voucherServices();
$extraOptions = voucherExtraOptions();
$step1 = $_SESSION[VOUCHER_DATA_KEY] ?? [
    'service' => 'rental',
    'name' => '',
    'phone' => '',
    'email' => '',
    'extra_options' => [],
];

if (!isset($services[$step1['service'] ?? ''])) {
    $step1['service'] = 'rental';
}
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
                    <div style="margin-left:88px; margin-top:57px "><img src="../images/w1.gif"></div>
                    <div style="margin-left:50px; margin-top:69px ">
                        <a href="../api/index.php">Главная<img src="../images/m1.gif" border="0"></a>
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
                                                <div style="margin-left:95px"><font class="title">Оформление заявки</font><br></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <form method="post" action="order.php">
                                                <input type="hidden" name="action" value="order_next">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="top" height="232" width="248">
                                                            <div style="margin-left:6px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:6px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                            <div style="margin-top:10px; margin-left:6px; font-size:12px;">
                                                                <font class="title">Дополнительные опции</font><br>
                                                                <?php foreach ($extraOptions as $optionKey => $option): ?>
                                                                    <label style="display:block; margin-top:4px;">
                                                                        <input type="checkbox" name="extra_options[]" value="<?= htmlspecialchars($optionKey, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($optionKey, $step1['extra_options'], true) ? 'checked' : '' ?>>
                                                                        <?= htmlspecialchars($option['label'] . ' (+' . $option['price'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </td>
                                                        <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                                        <td valign="top" height="215" width="243">
                                                            <div style="margin-left:22px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:22px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                            <div style="margin-left:22px; margin-top:13px; font-size:12px;">
                                                                <font class="title">Тип услуги</font><br>
                                                                <select name="service" style="width:190px;">
                                                                    <?php foreach ($services as $serviceKey => $service): ?>
                                                                        <option value="<?= htmlspecialchars($serviceKey, ENT_QUOTES, 'UTF-8') ?>" <?= ($step1['service'] ?? '') === $serviceKey ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($service['label'] . ' (' . $service['base_price'] . ' руб.)', ENT_QUOTES, 'UTF-8') ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <div style="height:8px;"></div>
                                                                <?php if ($flash): ?>
                                                                    <div style="color:<?= $flash['type'] === 'error' ? '#b00000' : '#116611' ?>; margin-bottom:8px;">
                                                                        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <font class="title">Контактные данные</font><br><br>
                                                                Имя:<br>
                                                                <input type="text" name="name" value="<?= htmlspecialchars((string)($step1['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="width:200px;"><br><br>
                                                                Телефон:<br>
                                                                <input type="text" name="phone" value="<?= htmlspecialchars((string)($step1['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="width:200px;"><br><br>
                                                                Почта:<br>
                                                                <input type="text" name="email" value="<?= htmlspecialchars((string)($step1['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" style="width:200px;"><br><br>
                                                                <button type="submit">Далее</button>
                                                            </div>
                                                            <div style="margin-left:22px; margin-top:16px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:22px; margin-top:7px;"><img src="../images/1_w4.gif"></div>
                                                            <div style="margin-left:22px; margin-top:9px; font-size:11px;">
                                                                <?= htmlspecialchars($services[$step1['service']]['description'], ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </form>
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
                    <div style="margin-left:51px; margin-top:31px ">
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
