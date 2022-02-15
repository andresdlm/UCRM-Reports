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
    $invoices = $api->get('invoices/');
    
    console_log($invoices);
    console_log($organization);

    $invoiceMap = [];
    $cantidadFacturas = 0;
    $cantidadImpuestos = 0;
    $cantidadSinImpuestos = 0;
    $cantidadTotal = 0;

    foreach ($invoices as $invoice) {
        if (date($invoice['createdDate']) >= date($parameters['createdDateFrom']) && 
        date($invoice['createdDate']) <= date($parameters['createdDateTo'])) {

            if ($invoice['organizationName'] == $organization['name']) {
                $cantidadFacturas++;
                $cantidadSinImpuestos = $cantidadSinImpuestos + $invoice['totalUntaxed'];
                $cantidadImpuestos = $cantidadImpuestos + $invoice['totalTaxAmount'];
                $cantidadTotal = $cantidadTotal + $invoice['total'];
                
                $invoiceMap[$invoice['id']] = [
                    'id' => $invoice['id'],
                    'createdDate' => $invoice['createdDate'],
                    'clientId' => $invoice['clientId'],
                    'clientName' => $invoice['clientFirstName'] . ' ' . $invoice['clientLastName'],
                    'companyName' => $invoice['clientCompanyName'],
                    'total' => $invoice['total'],
                    'totalTaxAmount' => $invoice['totalTaxAmount'],
                    'totalUntaxed' => $invoice['totalUntaxed'],
                ];
            }
        }
    }

    $result = [
        'invoices' => array_values($invoiceMap),
        'cantidadFacturas' => $cantidadFacturas,
        'cantidadSinImpuestos' => $cantidadSinImpuestos,
        'cantidadImpuestos' => $cantidadImpuestos,
        'cantidadTotal' => $cantidadTotal,
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
