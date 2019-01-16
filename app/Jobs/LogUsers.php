<?php

namespace App\Jobs;
use Log;
use Schema;
use Storage;
use App\User;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Sheets;
use Illuminate\Bus\Queueable;
use Google_Service_Sheets_ValueRange;
use Psr\Http\Message\RequestInterface;
use Google_Service_Sheets_Spreadsheet;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LogUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;

    public function __construct($users)
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(User $users)
    {    
        $client = new Google_Client();
        $client->setApplicationName('auth create sheet');
        $client->setScopes(Google_Service_Sheets::DRIVE,
        Google_Service_Sheets::DRIVE_FILE,
        Google_Service_Sheets::DRIVE_READONLY,
        Google_Service_Sheets::SPREADSHEETS,
        Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig(base_path().'/google/key.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = base_path().'/google/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
       
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {

                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }

            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $this->CreateSpreadsheet($client, $users);   
    }   


    public function CreateSpreadsheet($client, $users) 
    {
        if($users->count() > 500) {
            return $this->limited($client, $users, $users->count());
        }

        $service = new Google_Service_Sheets($client);
        $title = 'app_users-export-'.Carbon::now('America/Mexico_City')->toW3cString();

        $spreadsheet = new Google_Service_Sheets_Spreadsheet([
            'properties' => [
                'title' => $title
            ]
        ]);
        $spreadsheet = $service->spreadsheets->create($spreadsheet, [
            'fields' => 'spreadsheetId'
        ]);

        //printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);

        /* ****** */
        $options = array('valueInputOption' => 'RAW');
        $head = [
            'id', 'name', 'email', 'verified', 'admin', 'created_at', 'updated_at', 'age'
        ];

        $arrShests = [];
        foreach ($users->cursor() as $app_user) {
            $coll = collect($app_user);
            $flattened = $coll->flatten();
            array_push($arrShests, $flattened);
        }

        array_unshift($arrShests, $head);

        $body = new Google_Service_Sheets_ValueRange(['values' => $arrShests]);
        $spreadsheetId = $spreadsheet->spreadsheetId;
        $result = $service->spreadsheets_values->update($spreadsheetId, 'A1:X5000000', $body, $options);
        $url = 'https://docs.google.com/spreadsheets/d/'.$result->spreadsheetId;
        print($url);
        return $url;
        
    }

    public function limited($client, $users, $count) {
        $max = 4000;
        $contador = 2;

        for ($contador = 2; $max < ($count / $contador); $contador++)

        $arrShests = [];
        foreach ($users->cursor() as $app_user) {
            $coll = collect($app_user);
            $flattened = $coll->flatten();
            array_push($arrShests, $flattened);
        }

        $split = collect($arrShests)->split($contador)->toArray();
        $this->createSheets($client, $split);
    }

    public function createSheets($client, $split) {

        $page = 1;
        $sheetIds = [];
        foreach($split as $num){   
            
            $service = new Google_Service_Sheets($client);
            $title = 'app_users-export-'.Carbon::now('America/Mexico_City')->toW3cString().' - V.'.$page;

            $spreadsheet = new Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => $title
                ]
            ]);
            $spreadsheet = $service->spreadsheets->create($spreadsheet, [
                'fields' => 'spreadsheetId'
            ]);

            array_push($sheetIds, $spreadsheet->spreadsheetId);    
            $page++;
        }
        $options = array('valueInputOption' => 'RAW');

        foreach($sheetIds as $key => $sheet){  
            $body = new Google_Service_Sheets_ValueRange(['values' => $split[$key]]);
            $result = $service->spreadsheets_values->update($sheet, 'A1:X5000000', $body, $options); 
        }
        return $sheetIds;
    }   
}