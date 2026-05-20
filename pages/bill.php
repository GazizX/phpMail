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

$step1 = $_SESSION[VOUCHER_DATA_KEY] ?? null;
if (!is_array($step1) || !isset(voucherServices()[$step1['service'] ?? ''])) {
    voucherSetFlash('Сначала оформите заявку на странице заказа.', 'error');
    voucherRedirect('order.php');
}

$serviceKey = (string)$step1['service'];
$cars = voucherCarsByService()[$serviceKey] ?? [];
$preparationsConfig = voucherPreparationsByService()[$serviceKey] ?? ['code' => '', 'options' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'bill_back') {
    voucherRedirect('order.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'bill_next') {
    $data = voucherStep2FromRequest($_POST, $serviceKey);
    $errors = voucherValidateStep2($data);

    if ($errors) {
        voucherSetFlash(implode(' ', $errors), 'error');
        $_SESSION[VOUCHER_STEP2_KEY] = $data;
        voucherRedirect('bill.php');
    }

    $_SESSION[VOUCHER_STEP2_KEY] = $data;
    voucherRedirect('basket.php');
}

$flash = voucherConsumeFlash();
$step2 = $_SESSION[VOUCHER_STEP2_KEY] ?? [
    'car' => '',
    'preparations' => [],
    'days' => 1,
    'fast_sale' => 0,
    'service' => $serviceKey,
];
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
                                                <div style="margin-left:95px"><font class="title">Продолжение оформления</font><br></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <form method="post" action="bill.php">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="top" height="232" width="248">
                                                            <div style="margin-left:6px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:6px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                            <div style="margin-top:10px; margin-left:6px; font-size:12px;">
                                                                <font class="title">Предварительная подготовка</font><br>
                                                                <?php foreach (($preparationsConfig['options'] ?? []) as $prepKey => $prep): ?>
                                                                    <label style="display:block; margin-top:4px;">
                                                                        <input type="checkbox" name="preparations[]" value="<?= htmlspecialchars($prepKey, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($prepKey, $step2['preparations'], true) ? 'checked' : '' ?>>
                                                                        <?= htmlspecialchars($prep['label'] . ' (+' . $prep['price'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </td>
                                                        <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                                        <td valign="top" height="215" width="243">
                                                            <div style="margin-left:22px; margin-top:2px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:22px; margin-top:7px;"><img src="../images/1_w2.gif"></div>
                                                            <div style="margin-left:22px; margin-top:13px; font-size:12px;">
                                                                <font class="title">Марка машины</font><br>
                                                                <?php foreach ($cars as $carKey => $car): ?>
                                                                    <label style="display:block; margin-top:4px;">
                                                                        <input type="radio" name="car" value="<?= htmlspecialchars($carKey, ENT_QUOTES, 'UTF-8') ?>" <?= ($step2['car'] ?? '') === $carKey ? 'checked' : '' ?>>
                                                                        <?= htmlspecialchars($car['label'] . ' (+' . $car['price'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                                <div style="height:8px;"></div>
                                                                <?php if ($flash): ?>
                                                                    <div style="color:<?= $flash['type'] === 'error' ? '#b00000' : '#116611' ?>; margin-bottom:8px;">
                                                                        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <font class="title">Условия</font><br><br>
                                                                Код услуги: <b><?= htmlspecialchars((string)($preparationsConfig['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b><br><br>

                                                                <?php if ($serviceKey === 'sale'): ?>
                                                                    <label>
                                                                        <input type="checkbox" name="fast_sale" value="1" <?= !empty($step2['fast_sale']) ? 'checked' : '' ?>>
                                                                        Ускоренное оформление
                                                                    </label>
                                                                <?php else: ?>
                                                                    Количество дней:<br>
                                                                    <input type="number" name="days" min="<?= $serviceKey === 'leasing' ? '30' : '1' ?>" value="<?= htmlspecialchars((string)max($serviceKey === 'leasing' ? 30 : 1, (int)($step2['days'] ?? 1)), ENT_QUOTES, 'UTF-8') ?>" style="width:80px;">
                                                                <?php endif; ?>

                                                                <div style="margin-top:18px;">
                                                                    <button type="submit" formaction="order.php" formmethod="get" name="action" value="bill_back">Вернуться назад</button>
                                                                    <button type="submit" name="action" value="bill_next">Далее</button>
                                                                </div>
                                                            </div>
                                                            <div style="margin-left:22px; margin-top:16px;"><img src="../images/hl.gif"></div>
                                                            <div style="margin-left:22px; margin-top:7px;"><img src="../images/1_w4.gif"></div>
                                                            <div style="margin-left:22px; margin-top:9px; font-size:11px;">
                                                                <?= htmlspecialchars(voucherServices()[$serviceKey]['description'], ENT_QUOTES, 'UTF-8') ?>
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
