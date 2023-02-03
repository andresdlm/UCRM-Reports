<?php

declare(strict_types=1);

namespace App\Service;

use League\Csv\Writer;
use SplTempFileObject;

class CsvGenerator
{
    /**
     * @var string[]
     */
    private $stateMap;

    /**
     * @var string[]
     */
    private $countryMap;

    public function __construct(array $countries, array $states)
    {
        $this->countryMap = $this->mapCountries($countries);
        $this->stateMap = $this->mapStates($states);
    }

    public function generate(string $filename, array $invoices): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        $csv->insertOne($this->getHeaderLine());

        foreach ($invoices as $invoice) {
            $csv->insertOne($this->getInvoiceLine($invoice));
        }

        $csv->output($filename);
    }

    private function getHeaderLine(): array
    {
        return [
            'Id',
            'First Name',
            'Last Name',
            'Company Name',
            'City',
            'Client Type',
            'Note',
            'Username',
            'Phone',
            'Address',
            'Is Active',
            'Is Archived',
            'Is Lead',
            'Organization Id',
            'Organization Name',
            'Registration Date',
        ];
    }

    private function getInvoiceLine(array $client): array
    {
        return [
            $client['id'],
            $client['firstName'],
            $client['lastName'],
            $client['companyName'],
            $client['city'],
            $client['clientType'],
            $client['note'],
            $client['username'],
            $client['contacts'][0]['phone'],
            $client['fullAddress'],
            $client['isActive'],
            $client['isArchived'],
            $client['isLead'],
            $client['organizationId'],
            $client['organizationName'],
            $client['registrationDate'],
        ];
    }

    private function mapCountries(array $countries): array
    {
        $map = [];

        foreach ($countries as $country) {
            $map[$country['id']] = $country['name'];
        }

        return $map;
    }

    private function mapStates(array $states): array
    {
        $map = [];

        foreach ($states as $state) {
            $map[$state['id']] = $state['name'];
        }

        return $map;
    }
}
