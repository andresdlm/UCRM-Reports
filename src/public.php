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
        'organizationId' => $trimNonEmpty((string) $_GET['organization']),
        'registrationDateFrom' => $trimNonEmpty((string) $_GET['since']),
        'registrationDateTo' => $trimNonEmpty((string) $_GET['until']),
    ];
    $parameters = array_filter($parameters);

    $organization = $api->get('organizations/' . $_GET['organization']);
    $clients = $api->get('clients/', $parameters);
    
    console_log($clients);

    $clientsMap = [];
    $cantidadClientes = 0;
    foreach ($clients as $client) {
        if (date($client['registrationDate']) >= date($parameters['registrationDateFrom']) && date($client['registrationDate']) <= date($parameters['registrationDateTo'])) {
            $cantidadClientes++;
            if ($client['clientType'] == 1) {
                $clientsMap[$client['id']] = [
                    'firstName' => $client['firstName'],
                    'lastName' => $client['lastName'],
                    'registrationDate' => $client['registrationDate'],
                    'clientType' => 'Residencial'
                ];
            } else if ($client['clientType'] == 2) {
                $clientsMap[$client['id']] = [
                    'firstName' => $client['companyName'],
                    'lastName' => '',
                    'registrationDate' => $client['registrationDate'],
                    'clientType' => 'Empresarial'
                ];
            }
        }
    }

    $result = [
        'clients' => array_values($clientsMap),
        'cantidadClientes' => $cantidadClientes,
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
