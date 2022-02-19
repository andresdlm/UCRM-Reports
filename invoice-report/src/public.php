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

    $organizationId = (int) $_GET['organization'];
    $organizationName = "";

    if($organizationId != 0) {
        $organization = $api->get('organizations/' . $organizationId);
        $organizationName = $organization['name'];
    }

    $parameters = [
        'createdDateFrom' => $trimNonEmpty((string) $_GET['since']),
        'createdDateTo' => $trimNonEmpty((string) $_GET['until']),
        'organizationName' => $organizationName,
        'status' => (int) $trimNonEmpty((string) $_GET['status']),
    ];

    $invoices = $api->get('invoices/');

    if($organizationId != 0) {
        if($parameters['status'] == 0) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return $invoice['organizationName'] == $parameters['organizationName'] &&
                date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        } else if($parameters['status'] == 1) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return $invoice['organizationName'] == $parameters['organizationName'] &&
                $invoice['amountToPay'] == 0 &&
                date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        } else if($parameters['status'] == 2) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return $invoice['organizationName'] == $parameters['organizationName'] &&
                $invoice['amountToPay'] != 0 &&
                date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        } 
    } else {
        if($parameters['status'] == 0) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        } else if($parameters['status'] == 1) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return $invoice['amountToPay'] == 0 &&
                date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        } else if($parameters['status'] == 2) {
            $invoicesFiltered = array_filter($invoices, function($invoice) use ($parameters) {
                return $invoice['amountToPay'] != 0 &&
                date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
                date($invoice['createdDate']) <= date($parameters['createdDateTo']);
            });
        }
    }

    $cantidadImpuestos = 0;
    $cantidadSinImpuestos = 0;
    $cantidadTotal = 0;
    $cantidadTotalSinPagar = 0;

    foreach ($invoicesFiltered as $invoice) {
        $cantidadSinImpuestos = $cantidadSinImpuestos + $invoice['totalUntaxed'];
        $cantidadImpuestos = $cantidadImpuestos + $invoice['totalTaxAmount'];
        $cantidadTotal = $cantidadTotal + $invoice['total'];
        $cantidadTotalSinPagar = $cantidadTotalSinPagar + $invoice['amountToPay'];
    }

    $result = [
        'invoices' => array_values($invoicesFiltered),
        'cantidadFacturas' => count($invoicesFiltered),
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
