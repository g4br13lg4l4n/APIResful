<?php

namespace App\Jobs;
use Log;
use Schema;
use Storage;
use App\User;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Sheets;
use Psr\Http\Message\RequestInterface;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_ValueRange;
use Illuminate\Bus\Queueable;
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

        $head = [
            'id', 'name', 'email', 'verified', 'admin', 'created_at', 'updated_at', 'age'
        ];

        $options = array('valueInputOption' => 'RAW');
        $allUsers = $users->get()->toArray();

        $array = array_flatten($allUsers);
        $sheets = array_chunk($array, 8);

        array_unshift($sheets, $head);

        $body = new Google_Service_Sheets_ValueRange(['values' => $sheets]);

        $spreadsheetId = $spreadsheet->spreadsheetId;
        $result = $service->spreadsheets_values->update($spreadsheetId, 'A1:I1000000', $body, $options);
        $url = 'https://docs.google.com/spreadsheets/d/'.$result->spreadsheetId;
        print($url);
        return $url;
        
    }
}