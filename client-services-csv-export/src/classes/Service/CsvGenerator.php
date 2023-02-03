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
            'id',
            'client_id',
            'service_plan_id',
            'has_individual_price',
            'individual_price',
        ];
    }

    private function getInvoiceLine(array $client): array
    {
        return [
            $client['id'],
            $client['clientId'],
            $client['servicePlanId'],
            $client['hasIndividualPrice'],
            $client['totalPrice'],
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
