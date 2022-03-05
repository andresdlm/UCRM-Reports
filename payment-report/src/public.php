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
        'organizationId' => (int) $trimNonEmpty((string) $_GET['organization']),
        'paymentMethodId' => $trimNonEmpty((string) $_GET['payment-method']),
        'createdDateFrom' => $trimNonEmpty((string) $_GET['since']),
        'createdDateTo' => $trimNonEmpty((string) $_GET['until']),
    ];

    $clients = $api->get('clients/');
    $payments = $api->get('payments/');
    $paymentMethods = $api->get('payment-methods/');
    
    console_log($clients);
    console_log($payments);
    console_log($paymentMethods);

    if($parameters['paymentMethodId'] == '0') {
        if($parameters['organizationId'] != 0) {
            $paymentFiltered = array_filter($payments, function($payment) use ($parameters, $api) {
                if(date($payment['createdDate']) >= date($parameters['createdDateFrom']) && date($payment['createdDate']) <= date($parameters['createdDateTo'])) {
                    $client = $api->get('clients/' . $payment['clientId']);
                    return $client['organizationId'] == $parameters['organizationId'];
                }
            });
        } else {
            $paymentFiltered = array_filter($payments, function($payment) use ($parameters) {
                return date($payment['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($payment['createdDate']) <= date($parameters['createdDateTo']);
            });
        }
    } else {
        if($parameters['organizationId'] != 0) {
            $paymentFiltered = array_filter($payments, function($payment) use ($parameters, $api) {
                if(date($payment['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($payment['createdDate']) <= date($parameters['createdDateTo']) &&
                $payment['methodId'] == $parameters['paymentMethodId']) {
                    $client = $api->get('clients/' . $payment['clientId']);
                    return $client['organizationId'] == $parameters['organizationId'];
                }
            });
        } else {
            $paymentFiltered = array_filter($payments, function($payment) use ($parameters) {
                return date($payment['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($payment['createdDate']) <= date($parameters['createdDateTo']) &&
                $payment['methodId'] == $parameters['paymentMethodId'];
            });
        }
    }

    $paymentsMap = [];
    $cantidadRecibida = 0;
    foreach ($paymentFiltered as $payment) {
        $cantidadRecibida = $cantidadRecibida + $payment['amount'];
        $payment['methodId'] = 'hola';
    }

    $result = [
        'payments' => array_values($paymentFiltered),
        'cantidadPagos' => count($paymentFiltered),
        'cantidadRecibida' => $cantidadRecibida,
        'domain' => $_SERVER['HTTP_HOST'],
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
