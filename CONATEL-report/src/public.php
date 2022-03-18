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

// Process.
if (
    array_key_exists('until', $_GET)
    && is_string($_GET['until'])
) {
    $trimNonEmpty = static function (string $value): ?string {
        $value = trim($value);

        return $value === '' ? null : $value;
    };

    $parameters = [
        'registrationDateTo' => $trimNonEmpty((string) $_GET['until']),
    ];
    $services = $api->get('clients/services');
    $servicePlans = $api->get('service-plans');

    console_log($services);
    console_log($servicePlans);

    $internetPlans = [];
    $generalPlans = [];
    $countInternetServices = 0;
    $countGeneralServices = 0;

    foreach($servicePlans as $servicePlan) {
        if($servicePlan['servicePlanType'] == 'Internet'){
            $internetPlans[$servicePlan['id']] = [
                'id' => $servicePlan['id'],
                'name' => $servicePlan['name'],
                'invoiceLabel' => $servicePlan['invoiceLabel'],
                'servicePlanType' => $servicePlan['servicePlanType'],
                'organizationId' => $servicePlan['organizationId'],
                'downloadSpeed' => $servicePlan['downloadSpeed'],
                'countServices' => 0,
            ];
        } else {
            $generalPlans[$servicePlan['id']] = [
                'id' => $servicePlan['id'],
                'name' => $servicePlan['name'],
                'invoiceLabel' => $servicePlan['invoiceLabel'],
                'servicePlanType' => $servicePlan['servicePlanType'],
                'organizationId' => $servicePlan['organizationId'],
                'downloadSpeed' => $servicePlan['downloadSpeed'],
                'countServices' => 0,
            ];
        }
    }

    foreach($services as $service) {
        if($service['activeFrom'] != NULL && date($service['activeFrom']) <= date($parameters['registrationDateTo'])) {
            if($service['servicePlanType'] == 'Internet') {
                $internetPlans[$service['servicePlanId']]['countServices'] = $internetPlans[$service['servicePlanId']]['countServices'] + 1;
                $countInternetServices = $countInternetServices + 1;
            } else {
                $generalPlans[$service['servicePlanId']]['countServices'] = $generalPlans[$service['servicePlanId']]['countServices'] + 1;
                $countGeneralServices = $countGeneralServices + 1;
            }
        }
    }

    $result = [
        'internetPlans' => array_values($internetPlans),
        'generalPlans' => array_values($generalPlans),
        'countInternetServices' => $countInternetServices,
        'countGeneralServices' => $countGeneralServices,
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
