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
        'clientType' => (int) $trimNonEmpty((string) $_GET['clientType']),
        'registrationDateFrom' => $trimNonEmpty((string) $_GET['since']),
        'registrationDateTo' => $trimNonEmpty((string) $_GET['until']),
    ];

    $clients = $api->get('clients/');

    $clientsFiltered = array_filter($clients, function($client) use ($parameters) {
        if($parameters['organizationId'] == 0) {
            if($parameters['clientType'] == 0) {
                return date($client['registrationDate']) >= date($parameters['registrationDateFrom']) && 
                date($client['registrationDate']) <= date($parameters['registrationDateTo']) &&
                $client['isLead'] == FALSE;
            } else {
                return $client['clientType'] == $parameters['clientType'] && 
                date($client['registrationDate']) >= date($parameters['registrationDateFrom']) && 
                date($client['registrationDate']) <= date($parameters['registrationDateTo']) &&
                $client['isLead'] == FALSE;
            }
        }
        else {
            if($parameters['clientType'] == 0) {
                return $client['organizationId'] == $parameters['organizationId'] && 
                date($client['registrationDate']) >= date($parameters['registrationDateFrom']) && 
                date($client['registrationDate']) <= date($parameters['registrationDateTo']) &&
                $client['isLead'] == FALSE;
            } else {
                return $client['organizationId'] == $parameters['organizationId'] && 
                $client['clientType'] == $parameters['clientType'] &&
                date($client['registrationDate']) >= date($parameters['registrationDateFrom']) && 
                date($client['registrationDate']) <= date($parameters['registrationDateTo']) &&
                $client['isLead'] == FALSE;
            }
        }
    });

    console_log($clientsFiltered);

    $result = [
        'clients' => array_values($clientsFiltered),
        'clientsCount' => count($clientsFiltered),
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
