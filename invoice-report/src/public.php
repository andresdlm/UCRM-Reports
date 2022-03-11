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
        'status' => (int) $trimNonEmpty((string) $_GET['status']),
        'clientType' => (int) $trimNonEmpty((string) $_GET['client-type']),
    ];

    if($organizationId == 0) {
        $invoices = $api->get('invoices/');
    } else {
        $invoices = $api->get('invoices/', ['organizationId' => $_GET['organization']]);
    }
    
    console_log($invoices);

    if($parameters['status'] == 0) {
        // Facturas pagadas y sin pagar
        $invoices = array_filter($invoices, function($invoice) use ($parameters) {
            return date($invoice['dueDate']) >= date($parameters['createdDateFrom']) && 
            date($invoice['dueDate']) <= date($parameters['createdDateTo']);
        });
    } else if($parameters['status'] == 1) {
        // Facturas pagadas
        $invoices = array_filter($invoices, function($invoice) use ($parameters) {
            return $invoice['amountToPay'] == 0 &&
            date($invoice['dueDate']) >= date($parameters['createdDateFrom']) && 
            date($invoice['dueDate']) <= date($parameters['createdDateTo']);
        });
    } else if($parameters['status'] == 2) {
        // Facturas sin pagar
        $invoices = array_filter($invoices, function($invoice) use ($parameters) {
            return $invoice['amountToPay'] != 0 &&
            date($invoice['dueDate']) >= date($parameters['createdDateFrom']) && 
            date($invoice['dueDate']) <= date($parameters['createdDateTo']);
        });
    }

    if($parameters['clientType'] == 1) {
        $invoices = array_filter($invoices, function($invoice) {
            return $invoice['clientCompanyName'] == NULL;
        });
    } else if ($parameters['clientType'] == 2) {
        $invoices = array_filter($invoices, function($invoice) {
            return $invoice['clientCompanyName'] != NULL;
        });
    }

    $cantidadImpuestos = 0;
    $cantidadSinImpuestos = 0;
    $cantidadTotal = 0;
    $cantidadTotalSinPagar = 0;

    foreach ($invoices as $invoice) {
        $cantidadSinImpuestos = $cantidadSinImpuestos + $invoice['totalUntaxed'];
        $cantidadImpuestos = $cantidadImpuestos + $invoice['totalTaxAmount'];
        $cantidadTotal = $cantidadTotal + $invoice['total'];
        $cantidadTotalSinPagar = $cantidadTotalSinPagar + $invoice['amountToPay'];
    }

    $result = [
        'invoices' => array_values($invoices),
        'cantidadFacturas' => count($invoices),
        'cantidadSinImpuestos' => $cantidadSinImpuestos,
        'cantidadImpuestos' => $cantidadImpuestos,
        'cantidadTotal' => $cantidadTotal,
        'cantidadTotalSinPagar' => $cantidadTotalSinPagar,
        'domain' => $_SERVER['HTTP_HOST'],
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
