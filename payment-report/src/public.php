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
    array_key_exists('organization', $_GET)
    && is_string($_GET['organization'])
    && array_key_exists('since', $_GET)
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

    $organization = $api->get('organizations/' . $_GET['organization']);
    $clients = $api->get('clients/');
    $payments = $api->get('payments/');
    
    console_log($clients);
    console_log($payments);

    $paymentsMap = [];
    $cantidadPagos = 0;
    $cantidadRecibida = 0;
    foreach ($payments as $payment) {
        if (date($payment['createdDate']) >= date($parameters['createdDateFrom']) && 
        date($payment['createdDate']) <= date($parameters['createdDateTo'])) {
            $client = $api->get('clients/' . $payment['clientId']);

            if ($client['organizationId'] == $organization['id']) {
                $cantidadPagos++;
                $cantidadRecibida = $cantidadRecibida + $payment['amount'];
                
                $paymentsMap[$payment['id']] = [
                    'id' => $payment['id'],
                    'createdDate' => $payment['createdDate'],
                    'clientId' => $payment['clientId'],
                    'clientName' => $client['firstName'] . ' ' . $client['lastName'],
                    'companyName' => $client['companyName'],
                    'amount' => $payment['amount'],
                    'methodId' => $payment['methodId'],
                ];
            }
        }
    }

    $result = [
        'payments' => array_values($paymentsMap),
        'cantidadPagos' => $cantidadPagos,
        'cantidadRecibida' => $cantidadRecibida,
    ];

}

// Render form.
$organizations = $api->get('organizations');

$optionsManager = UcrmOptionsManager::create();

$renderer->render(
    __DIR__ . '/templates/form.php',
    [
        'organizations' => $organizations,
        'ucrmPublicUrl' => $optionsManager->loadOptions()->ucrmPublicUrl,
        'result' => $result ?? [],
    ]
);
