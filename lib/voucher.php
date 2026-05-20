<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const VOUCHER_AUTH_COOKIE = 'voucher_auth';
const VOUCHER_FLASH_KEY = 'voucher_flash';
const VOUCHER_DATA_KEY = 'voucher_order_data';
const VOUCHER_STEP2_KEY = 'voucher_order_step2';

function voucherServices(): array
{
    return [
        'rental' => [
            'label' => 'Прокат',
            'base_price' => 100,
            'description' => 'Прокат на несколько дней',
        ],
        'sale' => [
            'label' => 'Продажа',
            'base_price' => 500,
            'description' => 'Комиссионные услуги',
        ],
        'leasing' => [
            'label' => 'Лизинг',
            'base_price' => 2100,
            'description' => 'От 30 дней',
        ],
    ];
}

function voucherExtraOptions(): array
{
    return [
        'leather' => [
            'label' => 'Кожаный салон',
            'price' => 50,
            'description' => 'Натуральная кожа',
        ],
        'heated_seats' => [
            'label' => 'Подогрев сидений',
            'price' => 30,
            'description' => 'Только передние',
        ],
        'sunroof' => [
            'label' => 'Люк',
            'price' => 100,
            'description' => 'Полностью прозрачный',
        ],
    ];
}

function voucherCarsByService(): array
{
    return [
        'rental' => [
            'peugeot' => ['label' => 'Peugeot', 'price' => 200],
            'lada_priora' => ['label' => 'Lada Priora', 'price' => 100],
            'nissan' => ['label' => 'Nissan', 'price' => 300],
        ],
        'sale' => [
            'citroen' => ['label' => 'Citroen', 'price' => 500],
            'skoda' => ['label' => 'Skoda', 'price' => 300],
            'lexus' => ['label' => 'Lexus', 'price' => 800],
        ],
        'leasing' => [
            'kia' => ['label' => 'Kia', 'price' => 50],
            'honda' => ['label' => 'Honda', 'price' => 100],
            'mazda' => ['label' => 'Mazda', 'price' => 80],
        ],
    ];
}

function voucherPreparationsByService(): array
{
    return [
        'rental' => [
            'code' => 'A1',
            'options' => [
                'fuel' => ['label' => 'Бензин', 'price' => 50],
                'tires' => ['label' => 'Шины', 'price' => 100],
                'washer' => ['label' => 'Омыватель', 'price' => 200],
            ],
        ],
        'sale' => [
            'code' => 'A2',
            'options' => [
                'polish' => ['label' => 'Полировка', 'price' => 100],
                'interior_cleaning' => ['label' => 'Чистка салона', 'price' => 50],
                'service' => ['label' => 'ТО', 'price' => 200],
            ],
        ],
        'leasing' => [
            'code' => 'A3',
            'options' => [
                'fuel' => ['label' => 'Бензин', 'price' => 50],
                'interior_cleaning' => ['label' => 'Чистка салона', 'price' => 200],
                'engine_cleaning' => ['label' => 'Чистка двигателя', 'price' => 100],
            ],
        ],
    ];
}

function voucherServiceImage(string $service): string
{
    $map = [
        'rental' => '../images/rental.jpeg',
        'sale' => '../images/sale.jpg',
        'leasing' => '../images/leasing.jpg',
    ];

    return $map[$service] ?? '../images/rental.jpeg';
}

function voucherServiceImageFile(string $service): string
{
    return basename(voucherServiceImage($service));
}

function voucherPublicBaseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');

    if ($host === '' || $script === '') {
        return '';
    }

    $projectPath = rtrim(str_replace('\\', '/', dirname(dirname($script))), '/');

    return $scheme . '://' . $host . $projectPath;
}

function voucherServiceImageUrl(string $service): string
{
    $baseUrl = voucherPublicBaseUrl();
    if ($baseUrl === '') {
        return '';
    }

    return $baseUrl . '/images/' . voucherServiceImageFile($service);
}

function voucherLogin(string $login, string $password): bool
{
    if ($login !== 'admin' || $password !== '123') {
        return false;
    }

    setcookie(VOUCHER_AUTH_COOKIE, '1', [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    return true;
}

function voucherLogout(): void
{
    setcookie(VOUCHER_AUTH_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    unset($_SESSION[VOUCHER_DATA_KEY], $_SESSION[VOUCHER_STEP2_KEY]);
}

function voucherIsAuthorized(): bool
{
    return ($_COOKIE[VOUCHER_AUTH_COOKIE] ?? '') === '1';
}

function voucherSetFlash(string $message, string $type = 'info'): void
{
    $_SESSION[VOUCHER_FLASH_KEY] = ['message' => $message, 'type' => $type];
}

function voucherConsumeFlash(): ?array
{
    if (!isset($_SESSION[VOUCHER_FLASH_KEY])) {
        return null;
    }

    $flash = $_SESSION[VOUCHER_FLASH_KEY];
    unset($_SESSION[VOUCHER_FLASH_KEY]);

    return $flash;
}

function voucherNormalizeName(string $name): string
{
    return trim(preg_replace('/\s+/', ' ', $name));
}

function voucherStep1FromRequest(array $request): array
{
    $service = (string)($request['service'] ?? '');
    $name = voucherNormalizeName((string)($request['name'] ?? ''));
    $phone = trim((string)($request['phone'] ?? ''));
    $email = trim((string)($request['email'] ?? ''));

    $extraOptionsInput = $request['extra_options'] ?? [];
    $extraOptions = is_array($extraOptionsInput) ? array_values($extraOptionsInput) : [];

    return [
        'service' => $service,
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'extra_options' => $extraOptions,
    ];
}

function voucherValidateStep1(array $data): array
{
    $errors = [];
    $services = voucherServices();

    if (!isset($services[$data['service'] ?? ''])) {
        $errors[] = 'Выберите тип услуги.';
    }
    if (($data['name'] ?? '') === '') {
        $errors[] = 'Введите имя заказчика.';
    }
    if (($data['phone'] ?? '') === '') {
        $errors[] = 'Введите телефон.';
    }
    if (!filter_var(($data['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail.';
    }

    $allowedExtra = array_keys(voucherExtraOptions());
    foreach ($data['extra_options'] ?? [] as $key) {
        if (!in_array($key, $allowedExtra, true)) {
            $errors[] = 'Выбрана некорректная доп. опция.';
            break;
        }
    }

    return $errors;
}

function voucherStep2FromRequest(array $request, string $service): array
{
    $prepInput = $request['preparations'] ?? [];
    $preparations = is_array($prepInput) ? array_values($prepInput) : [];

    return [
        'car' => (string)($request['car'] ?? ''),
        'preparations' => $preparations,
        'days' => isset($request['days']) ? (int)$request['days'] : 0,
        'fast_sale' => isset($request['fast_sale']) ? 1 : 0,
        'service' => $service,
    ];
}

function voucherValidateStep2(array $data): array
{
    $errors = [];
    $service = $data['service'] ?? '';
    $cars = voucherCarsByService()[$service] ?? [];
    $prepConfig = voucherPreparationsByService()[$service]['options'] ?? [];

    if (!isset($cars[$data['car'] ?? ''])) {
        $errors[] = 'Выберите марку машины.';
    }

    foreach ($data['preparations'] ?? [] as $prepKey) {
        if (!isset($prepConfig[$prepKey])) {
            $errors[] = 'Выбрана некорректная подготовка.';
            break;
        }
    }

    if ($service === 'sale') {
        $data['days'] = 0;
    } elseif ($service === 'leasing') {
        if (($data['days'] ?? 0) < 30) {
            $errors[] = 'Для лизинга укажите 30 дней или больше.';
        }
    } elseif (($data['days'] ?? 0) < 1) {
        $errors[] = 'Для проката укажите количество дней (не меньше 1).';
    }

    return $errors;
}

function voucherCollectOrder(): ?array
{
    if (!isset($_SESSION[VOUCHER_DATA_KEY], $_SESSION[VOUCHER_STEP2_KEY])) {
        return null;
    }

    $step1 = $_SESSION[VOUCHER_DATA_KEY];
    $step2 = $_SESSION[VOUCHER_STEP2_KEY];

    $services = voucherServices();
    $extras = voucherExtraOptions();
    $carsByService = voucherCarsByService();
    $prepsByService = voucherPreparationsByService();

    $serviceKey = $step1['service'];
    $service = $services[$serviceKey] ?? null;
    if ($service === null) {
        return null;
    }

    $car = $carsByService[$serviceKey][$step2['car']] ?? null;
    if ($car === null) {
        return null;
    }

    $selectedExtras = [];
    $extrasTotal = 0;
    foreach ($step1['extra_options'] as $extraKey) {
        if (!isset($extras[$extraKey])) {
            continue;
        }
        $selectedExtras[] = $extras[$extraKey];
        $extrasTotal += (int)$extras[$extraKey]['price'];
    }

    $selectedPreparations = [];
    $preparationsTotal = 0;
    $prepConfig = $prepsByService[$serviceKey]['options'] ?? [];
    foreach ($step2['preparations'] as $prepKey) {
        if (!isset($prepConfig[$prepKey])) {
            continue;
        }
        $selectedPreparations[] = $prepConfig[$prepKey];
        $preparationsTotal += (int)$prepConfig[$prepKey]['price'];
    }

    $days = (int)($step2['days'] ?? 0);
    $fastSale = ((int)($step2['fast_sale'] ?? 0)) === 1;

    $daysCharge = 0;
    $fastSalePrice = 0;
    if ($serviceKey === 'sale') {
        $fastSalePrice = $fastSale ? 150 : 0;
    } elseif ($serviceKey === 'leasing') {
        $daysCharge = max(30, $days) * 20;
    } else {
        $daysCharge = max(1, $days) * 20;
    }

    $total = (int)$service['base_price']
        + (int)$car['price']
        + $extrasTotal
        + $preparationsTotal
        + $daysCharge
        + $fastSalePrice;

    return [
        'customer' => [
            'name' => $step1['name'],
            'phone' => $step1['phone'],
            'email' => $step1['email'],
        ],
        'service' => $service,
        'service_key' => $serviceKey,
        'car' => $car,
        'days' => $days,
        'fast_sale' => $fastSale,
        'prep_code' => $prepsByService[$serviceKey]['code'] ?? '',
        'extra_options' => $selectedExtras,
        'preparations' => $selectedPreparations,
        'amounts' => [
            'base' => (int)$service['base_price'],
            'car' => (int)$car['price'],
            'extras' => $extrasTotal,
            'preparations' => $preparationsTotal,
            'days_charge' => $daysCharge,
            'fast_sale' => $fastSalePrice,
            'total' => $total,
        ],
    ];
}

function voucherBuildMailText(array $order): string
{
    $customerName = $order['customer']['name'];
    $lines = [];
    $lines[] = 'Уважаемый(ая) ' . $customerName . '!';
    $lines[] = '';
    $lines[] = 'Наш автосалон рад предложить Вам услугу ' . strtolower($order['service']['label']) . ' автомобиля ' . $order['car']['label'] . '.';

    if ($order['service_key'] === 'sale') {
        $lines[] = 'Ускоренное оформление: ' . ($order['fast_sale'] ? 'Да' : 'Нет');
    } else {
        $lines[] = 'Количество дней: ' . $order['days'];
    }

    if (!empty($order['extra_options'])) {
        $lines[] = 'Дополнительные опции:';
        foreach ($order['extra_options'] as $option) {
            $lines[] = '- ' . $option['label'];
        }
    }

    if (!empty($order['preparations'])) {
        $lines[] = 'Предварительная подготовка:';
        foreach ($order['preparations'] as $option) {
            $lines[] = '- ' . $option['label'];
        }
    }

    $lines[] = '';
    $lines[] = 'Полная стоимость контракта: ' . $order['amounts']['total'] . ' руб.';

    return implode(PHP_EOL, $lines);
}

function voucherBuildMailHtml(array $order): string
{
    $customerName = htmlspecialchars($order['customer']['name'], ENT_QUOTES, 'UTF-8');
    $serviceLabel = htmlspecialchars($order['service']['label'], ENT_QUOTES, 'UTF-8');
    $carLabel = htmlspecialchars($order['car']['label'], ENT_QUOTES, 'UTF-8');
    $imageUrl = htmlspecialchars(voucherServiceImageUrl($order['service_key']), ENT_QUOTES, 'UTF-8');
    $total = (int)$order['amounts']['total'];

    $extraItems = '';
    foreach ($order['extra_options'] as $option) {
        $extraItems .= '<li>' . htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') . '</li>';
    }

    $prepItems = '';
    foreach ($order['preparations'] as $option) {
        $prepItems .= '<li>' . htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') . '</li>';
    }

    $daysLine = $order['service_key'] === 'sale'
        ? 'Ускоренное оформление: ' . ($order['fast_sale'] ? 'Да' : 'Нет')
        : 'Количество дней: ' . (int)$order['days'];

    return '
<html>
  <body style="font-family:Arial,sans-serif; color:#222;">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td valign="top">
          <p>Уважаемый ' . $customerName . '!</p>
          <p>Наш автосалон рад предложить Вам услугу <b>' . $serviceLabel . '</b> автомобиля <b>' . $carLabel . '</b>.</p>
          <p>' . htmlspecialchars($daysLine, ENT_QUOTES, 'UTF-8') . '</p>
          <p>Дополнительные опции:</p>
          <ul>' . $extraItems . '</ul>
          <p>Предварительная подготовка:</p>
          <ul>' . $prepItems . '</ul>
          <p><b>Полная стоимость контракта: ' . $total . ' руб.</b></p>
        </td>
        <td valign="top" align="right" style="padding-left:20px;">
          <img src="' . $imageUrl . '" alt="" style="max-width:240px; height:auto;">
        </td>
      </tr>
    </table>
  </body>
</html>';
}

function voucherWriteTextFile(array $order): array
{
    $fileName = 'basket.txt';
    $target = dirname(__DIR__) . DIRECTORY_SEPARATOR . $fileName;

    $payload = voucherBuildMailText($order) . PHP_EOL;
    $written = @file_put_contents($target, $payload);

    if ($written === false) {
        return ['ok' => false, 'message' => 'Не удалось записать файл ' . $fileName . '.'];
    }

    return ['ok' => true, 'message' => 'Файл сохранен: ' . $fileName . '.'];
}

function voucherExtractSurname(string $name): string
{
    $normalized = voucherNormalizeName($name);
    if ($normalized === '') {
        return 'client';
    }

    $parts = preg_split('/\s+/u', $normalized) ?: [];
    $surname = $parts[0] ?? 'client';
    $surname = preg_replace('/[^\p{L}\p{N}_-]+/u', '', $surname) ?: 'client';

    return $surname;
}

function voucherBuildSpreadsheet(array $order): \PhpOffice\PhpSpreadsheet\Spreadsheet
{
    $template = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'voucher_template.xlsx';

    if (is_file($template)) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
    } else {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    }

    $sheet = $spreadsheet->getActiveSheet();

    // ==================================================
    // 1. ШАПКА (Организация + Автосалон)
    // ==================================================
    $sheet->setCellValue('C1', 'Организация');
    $sheet->setCellValue('E1', 'Автосалон');
    
    // Накладная и номер в одной строке
    $sheet->mergeCells('C2:D2');
    $sheet->setCellValue('C2', 'Накладная №');
    $sheet->getStyle('C2')->getFont()->setBold(true);
    $sheet->setCellValue('E2', random_int(1000, 9999));
    $sheet->getStyle('E2')->getFont()->setBold(true);
    
    $sheet->setCellValue('I1', 'Дата:');
    $sheet->setCellValue('I2', date('d.m.Y'));

    // ==================================================
    // 2. ЛЕВАЯ ТАБЛИЦА (атрибуты, без границ, выравнивание по левому краю)
    // ==================================================
    $leftAttributes = [
        ['label' => 'Тип услуги:',        'value' => $order['service']['label']],
        ['label' => 'Базовая цена',       'value' => $order['amounts']['base']],
        ['label' => 'Машина',             'value' => $order['car']['label']],
        ['label' => 'Количество дней',    'value' => $order['days']],
        ['label' => 'Итоговая сумма для лизинга и проката:', 'value' => $order['amounts']['days_charge']],
    ];

    $row = 4;
    foreach ($leftAttributes as $attr) {
        $sheet->setCellValue('A' . $row, $attr['label']);
        $sheet->setCellValue('G' . $row, $attr['value']);
        $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        
        if ($attr['label'] === 'Итоговая сумма для лизинга и проката:') {
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('G' . $row)->getFont()->setBold(true);
        }
        $row++;
    }

    // ==================================================
    // 3. ТАБЛИЦА "Предварительная подготовка"
    // ==================================================
    $prepStartRow = $row + 1;
    
    $sheet->mergeCells('A' . $prepStartRow . ':D' . $prepStartRow);
    $sheet->setCellValue('A' . $prepStartRow, 'Предварительная подготовка');
    $sheet->getStyle('A' . $prepStartRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $prepStartRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    // Данные таблицы: Наценка за машину, Код услуги, затем доп услуги
    $prepDataStart = $prepStartRow + 1;
    
    // Строка 1: Наценка за машину
    $sheet->setCellValue('A' . $prepDataStart, '1');
    $sheet->setCellValue('B' . $prepDataStart, 'Наценка за машину');
    $sheet->mergeCells('B' . $prepDataStart . ':C' . $prepDataStart);
    $sheet->setCellValue('D' . $prepDataStart, $order['amounts']['car']);
    
    // Строка 2: Код услуги
    $sheet->setCellValue('A' . ($prepDataStart + 1), '2');
    $sheet->setCellValue('B' . ($prepDataStart + 1), 'Код услуги');
    $sheet->mergeCells('B' . ($prepDataStart + 1) . ':C' . ($prepDataStart + 1));
    $sheet->setCellValue('D' . ($prepDataStart + 1), $order['prep_code']);
    
    // Строки 3+: Дополнительные услуги (всегда 3 штуки, но с нулевой ценой если не выбраны)
    $allPreparations = [
        'fuel' => 'Бензин',
        'tires' => 'Шины',
        'washer' => 'Омыватель',
        'polish' => 'Полировка',
        'interior_cleaning' => 'Чистка салона',
        'service' => 'ТО',
        'engine_cleaning' => 'Чистка двигателя',
    ];
    
    // Собираем выбранные услуги в массив для быстрого доступа
    $selectedPrepMap = [];
    foreach ($order['preparations'] as $prep) {
        $selectedPrepMap[$prep['label']] = $prep['price'];
    }
    
    // Определяем какие услуги показывать в зависимости от типа услуги
    $serviceKey = $order['service_key'];
    $prepKeysToShow = [];
    
    if ($serviceKey === 'rental') {
        $prepKeysToShow = ['fuel', 'tires', 'washer'];
    } elseif ($serviceKey === 'sale') {
        $prepKeysToShow = ['polish', 'interior_cleaning', 'service'];
    } elseif ($serviceKey === 'leasing') {
        $prepKeysToShow = ['fuel', 'interior_cleaning', 'engine_cleaning'];
    }
    
    $currentRow = $prepDataStart + 2;
    $prepIndex = 3;
    foreach ($prepKeysToShow as $key) {
        $label = $allPreparations[$key];
        $price = isset($selectedPrepMap[$label]) ? $selectedPrepMap[$label] : 0;
        
        $sheet->setCellValue('A' . $currentRow, $prepIndex);
        $sheet->setCellValue('B' . $currentRow, $label);
        $sheet->mergeCells('B' . $currentRow . ':C' . $currentRow);
        $sheet->setCellValue('D' . $currentRow, $price);
        
        $currentRow++;
        $prepIndex++;
    }
    
    $prepTotalRow = $currentRow;
    // Итого вне таблицы (без обводки)
    $sheet->setCellValue('C' . $prepTotalRow, 'Итого:');
    $sheet->setCellValue('D' . $prepTotalRow, $order['amounts']['preparations'] + $order['amounts']['car']);

    $sheet->getStyle('A' . $prepStartRow . ':D' . ($prepTotalRow - 1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // ==================================================
    // 4. ТАБЛИЦА "Дополнительные опции"
    // ==================================================
    $rightStartCol = 'F';
    $rightStartRow = $prepStartRow;
    
    $sheet->mergeCells($rightStartCol . $rightStartRow . ':I' . $rightStartRow);
    $sheet->setCellValue($rightStartCol . $rightStartRow, 'Дополнительные опции');
    $sheet->getStyle($rightStartCol . $rightStartRow)->getFont()->setBold(true);
    $sheet->getStyle($rightStartCol . $rightStartRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // Данные таблицы: всегда 3 строки с опциями, невыбранные имеют цену 0
    $rightDataStart = $rightStartRow + 1;
    
    $allExtraOptions = [
        'leather' => 'Кожаный салон',
        'heated_seats' => 'Подогрев сидений',
        'sunroof' => 'Люк',
    ];
    
    // Собираем выбранные опции
    $selectedExtraMap = [];
    foreach ($order['extra_options'] as $option) {
        $selectedExtraMap[$option['label']] = $option['price'];
    }
    
    $currentRightRow = $rightDataStart;
    $extraIndex = 1;
    foreach ($allExtraOptions as $key => $label) {
        $price = isset($selectedExtraMap[$label]) ? $selectedExtraMap[$label] : 0;
        
        $sheet->setCellValue('F' . $currentRightRow, $extraIndex);
        $sheet->setCellValue('G' . $currentRightRow, $label);
        $sheet->mergeCells('G' . $currentRightRow . ':H' . $currentRightRow);
        $sheet->setCellValue('I' . $currentRightRow, $price);
        
        $currentRightRow++;
        $extraIndex++;
    }
    
    $rightTotalRow = $currentRightRow;
    
    // Итого вне таблицы (без обводки)
    $sheet->setCellValue('H' . $rightTotalRow, 'Итого:');
    $sheet->setCellValue('I' . $rightTotalRow, $order['amounts']['extras']);

    // Границы для всей таблицы (включая заголовок)
    $sheet->getStyle('F' . $rightStartRow . ':I' . ($rightTotalRow - 1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // ==================================================
    // 5. ОБЩАЯ СТОИМОСТЬ (строка 20)
    // ==================================================
    $sheet->setCellValue('C20', 'Общая стоимость услуг:');
    $sheet->getStyle('C20')->getFont()->setBold(true);
    $sheet->setCellValue('F20', $order['amounts']['total']);
    $sheet->getStyle('F20')->getFont()->setBold(true);
    $sheet->setCellValue('H20', 'руб.');
    $sheet->getStyle('H20')->getFont()->setBold(true);

    // ==================================================
    // 6. ЗАКАЗЧИК И МЕНЕДЖЕР
    // ==================================================
    $sheet->setCellValue('A22', 'Заказчик:');
    $sheet->setCellValue('C22', $order['customer']['name']);
    
    $sheet->setCellValue('A23', 'Телефон:');
    $sheet->setCellValue('C23', $order['customer']['phone']);
    
    $sheet->setCellValue('A24', 'Почта:');
    $sheet->setCellValue('C24', $order['customer']['email']);
    
    $sheet->setCellValue('F22', 'Менеджер:');
    $sheet->setCellValue('H22', 'Хасанов Г.У.');

    // ==================================================
    // 7. ПОДПИСЬ (строка 23 и 24)
    // ==================================================
    // Подпись в строке 23
    $sheet->setCellValue('F23', 'Подпись:');
    $sheet->mergeCells('G23:H23');
    $sheet->setCellValue('G23', '_________________');
    
    // подпись заказчика в строке 24
    $sheet->setCellValue('G24', '(подпись заказчика)');
    $sheet->getStyle('G24')->getFont()->setItalic(true);

    return $spreadsheet;
}

function voucherWriteSpreadsheet(array $order): array
{
    if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
        return ['ok' => false, 'message' => 'Библиотека PhpSpreadsheet не подключена.'];
    }

    $surname = voucherExtractSurname($order['customer']['name']);
    $date = date('d-m-Y');
    $fileName = $surname . '_' . $date . '.xlsx';
    $target = dirname(__DIR__) . DIRECTORY_SEPARATOR . $fileName;
    try {
        $spreadsheet = voucherBuildSpreadsheet($order);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($target);
    } catch (\Throwable $e) {
        return ['ok' => false, 'message' => 'Ошибка сохранения xlsx: ' . $e->getMessage()];
    }

    return ['ok' => true, 'message' => 'Файл сохранен: ' . $fileName . '.'];
}

function voucherDownloadSpreadsheet(array $order): void
{
    if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
        $result = voucherWriteTextFile($order);
        voucherSetFlash($result['message'], $result['ok'] ? 'success' : 'error');
        voucherRedirect('basket.php');
    }

    try {
        $spreadsheet = voucherBuildSpreadsheet($order);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $surname = voucherExtractSurname($order['customer']['name']);
        $fileName = $surname . '_' . date('d-m-Y') . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="voucher.xlsx"; filename*=UTF-8\'\'' . rawurlencode($fileName));
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    } catch (\Throwable $e) {
        $result = voucherWriteTextFile($order);
        voucherSetFlash('XLSX не удалось скачать, сохранено в basket.txt.', $result['ok'] ? 'success' : 'error');
        voucherRedirect('basket.php');
    }
}

function voucherSaveOrderToFile(array $order): array
{
    if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    $xlsxResult = voucherWriteSpreadsheet($order);
    if ($xlsxResult['ok']) {
        return $xlsxResult;
    }

    $txtResult = voucherWriteTextFile($order);
    if ($txtResult['ok']) {
        return ['ok' => true, 'message' => 'XLSX недоступен, сохранено в basket.txt.'];
    }

    return ['ok' => false, 'message' => $xlsxResult['message'] . ' ' . $txtResult['message']];
}

function voucherSendMail(array $order): array
{
    $to = $order['customer']['email'];
    $subject = 'Ваш заказ в автосалоне';
    $body = voucherBuildMailHtml($order);

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: no-reply@voucher.local',
    ];

    $ok = @mail($to, $subject, $body, implode("\r\n", $headers));

    if ($ok) {
        return ['ok' => true, 'message' => 'Письмо успешно отправлено.'];
    }

    return ['ok' => false, 'message' => 'Письмо не отправлено. На локальном сервере это ожидаемо без SMTP/хостинга.'];
}

function voucherRedirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}
