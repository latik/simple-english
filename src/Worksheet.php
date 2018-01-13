<?php

namespace App;

use Cake\Chronos\Chronos;
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

    public function __construct(
      $privateKeyPath,
      $googleApplicationName,
      $serviceAccountName,
      $spreadsheetTitle,
      $worksheetTitle
    ) {
        $privateKey = file_get_contents(__DIR__.$privateKeyPath, true);
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

    private function editCell($x, $y, $value)
    {
        return $this->cellFeed->editCell($x, $y, $value);
    }

    private function insertRow(array $row): void
    {
        $this->listFeed->insert($row);
    }

    public function storeRow(array $post): void
    {
        $this->editCell(1, 4, 'time');
        $i = 0;
        foreach (array_keys($post) as $key) {
            $this->editCell(1, $i++, $key);
        }
        $row = array_merge($post, ['time' => Chronos::now()]);
        $this->insertRow($row);
    }
}
