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

    if($parameters['organizationId'] == 0) {
        $clients = $api->get('clients/');
        $services = $api->get('clients/services');
    } else {
        $clients = $api->get('clients/', ['organizationId' => $_GET['organization']]);
        $services = $api->get('clients/services', ['organizationId' => $_GET['organization']]);
    }
    
    console_log($clients);
    console_log($services);
    
    $services = array_filter($services, function($service) use ($parameters) {
        if($service['activeFrom'] != NULL) {
            return date($service['activeFrom']) >= date($parameters['registrationDateFrom']) &&
            date($service['activeFrom']) <= date($parameters['registrationDateTo']);
        }
    });
    
    $clientsId = [];
    foreach ($services as $service) {
        array_push($clientsId, $service['clientId']);
    }

    $clients = array_filter($clients, function($client) use ($parameters, $clientsId) {
        if($parameters['clientType'] == 0) {
            return in_array($client['id'], $clientsId);
        } else {
            return $client['clientType'] == $parameters['clientType'] && in_array($client['id'], $clientsId);
        }
    });

    $result = [
        'clients' => array_values($clients),
        'clientsCount' => count($clients),
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
