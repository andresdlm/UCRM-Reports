<?php

declare(strict_types=1);

use App\Service\TemplateRenderer;
use Ubnt\UcrmPluginSdk\Security\PermissionNames;
use Ubnt\UcrmPluginSdk\Service\UcrmApi;
use Ubnt\UcrmPluginSdk\Service\UcrmOptionsManager;
use Ubnt\UcrmPluginSdk\Service\UcrmSecurity;

chdir(__DIR__);

require __DIR__ . '/vendor/autoload.php';

// Retrieve API connection.
$api = UcrmApi::create();

// Ensure that user is logged in and has permission to view the information.
$security = UcrmSecurity::create();
$user = $security->getUser();
if (! $user || $user->isClient || ! $user->hasViewPermission(PermissionNames::BILLING_INVOICES)) {
    \App\Http::forbidden();
}

// Console.log function
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

// Retrieve renderer.
$renderer = new TemplateRenderer();

// Process submitted form.
if (
    array_key_exists('since', $_GET)
    && is_string($_GET['since'])
    && array_key_exists('until', $_GET)
    && is_string($_GET['until'])
) {
    $trimNonEmpty = static function (string $value): ?string {
        $value = trim($value);

        return $value === '' ? null : $value;
    };

    $parameters = [
        'createdDateFrom' => $trimNonEmpty((string) $_GET['since']),
        'createdDateTo' => $trimNonEmpty((string) $_GET['until']),
    ];

    $paymentMethods = $api->get('payment-methods');
    $payments = $api->get('payments');

    console_log($paymentMethods);
    console_log($payments);
    
    $paymentsMet = [];

    foreach($paymentMethods as $paymentMethod){
        $paymentsMet[$paymentMethod['id']] = [
            'name' => $paymentMethod['name'],
            'amount' => 0,
            'count' => 0,
        ];
    }

    $totalAmount = 0;
    $totalCount = 0;
    foreach($payments as $payment) {
        if(date($payment['createdDate']) >= date($parameters['createdDateFrom']) && 
        date($payment['createdDate']) <= date($parameters['createdDateTo'])) {
            $paymentsMet[$payment['methodId']]['amount'] = $paymentsMet[$payment['methodId']]['amount'] + $payment['amount'];
            $totalAmount = $totalAmount + $payment['amount'];
            $paymentsMet[$payment['methodId']]['count'] = $paymentsMet[$payment['methodId']]['count'] + 1;
            $totalCount = $totalCount + 1;
        }
    }

    $result = [
        'paymentMethods' => array_values($paymentsMet),
        'totalAmount' => $totalAmount,
        'totalCount' => $totalCount,
    ];

}

// Render form.
$organizations = $api->get('organizations');
$paymentMethods = $api->get('payment-methods');

$optionsManager = UcrmOptionsManager::create();

$renderer->render(
    __DIR__ . '/templates/form.php',
    [
        'organizations' => $organizations,
        'paymentMethods' => $paymentMethods,
        'ucrmPublicUrl' => $optionsManager->loadOptions()->ucrmPublicUrl,
        'result' => $result ?? [],
    ]
);
