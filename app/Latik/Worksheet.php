<?php

namespace Latik;

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ListEntry;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

class Worksheet
{
    protected $scopes = [
            'https://spreadsheets.google.com/feeds',
            'https://www.googleapis.com/auth/drive',
            ];
    public $cellFeed;
    public $listFeed;

    public function __construct($config)
    {
        extract($config);

        $privateKey = file_get_contents($privateKeyPath, true);
        $client = new \Google_Client();
        $client->setApplicationName($googleApplicationName);
        $client->setScopes($this->scopes);
        $assertion = new \Google_Auth_AssertionCredentials($serviceAccountName, $this->scopes, $privateKey);
        $client->setAssertionCredentials($assertion);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($assertion);
        }
        $token = json_decode($client->getAccessToken(), true);
        $serviceRequest = new DefaultServiceRequest($token['access_token']);
        ServiceRequestFactory::setInstance($serviceRequest);
        $service = new SpreadsheetService();
        $spreadsheetFeed = $service->getSpreadsheets();
        $spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);
        $worksheetFeed = $spreadsheet->getWorksheets();
        $worksheet = $worksheetFeed->getByTitle($worksheetTitle);
        $this->cellFeed = $worksheet->getCellFeed();
        $this->listFeed = $worksheet->getListFeed();
    }

    public function orderByCategory()
    {
        $data = [];
        foreach ($this->listFeed->getEntries() as $entry) {
            $cell = $entry->getValues();

            if (!empty($cell['category'])) {
                $data[$cell['category']][] = $cell;
            } else {
                $data['uncategory'][] = $cell;
            }
        }

        return $data;
    }

    public function categories()
    {
        return array_keys($this->orderByCategory());
    }

    public function all()
    {
        /* @var \Google\Spreadsheet\ListEntry $entry */
        return array_map(function (ListEntry $entry) {
            return $entry->getValues();
        }, $this->listFeed->getEntries());
    }

    public function editCell($x, $y, $value)
    {
        return $this->cellFeed->editCell($x, $y, $value);
    }

    public function insertRow(array $row)
    {
        return $this->listFeed->insert($row);
    }
}
