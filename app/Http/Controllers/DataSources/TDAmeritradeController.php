<?php

namespace App\Http\Controllers\DataSources;

class TDAmeritradeController extends \App\Http\Controllers\Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Retrieve data from TD Ameritrade
     * Doc: https://developer.tdameritrade.com/option-chains/apis/get/marketdata/chains
     * Expiration types: S – Short or Weekly, R – Regular, Q – Quarterly, M – End of month
     * 
     * @return Array
     */
    public static function collectOptionData($symbol, $strikeCount = 4)
    {
        $dt =  new \DateTime();
        $format__date = "Y-m-d";
        $apiUrl = 'https://api.tdameritrade.com/v1/marketdata/chains?' .
                  'apikey=' . config('app.key_tda') .
                  '&' .
                  'symbol=' . $symbol .
                  '&' .
                  'strikeCount=' . $strikeCount .
                  '&' .
                  'range=' . 'NTM' .
                  '&' .
                  'optionType=' . 'S' .
                  '&' .
                  'fromDate=' . $dt->format($format__date) .
                  '&' .
                  'toDate=' . $dt->modify('1 year')->format($format__date) .
                  '';
        $options = [
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => 0
            ]
        ];

        // Send request to remote api and retrieve json data
        $client = new \GuzzleHttp\Client();
        $request = new \GuzzleHttp\Psr7\Request('GET', $apiUrl);
        $promise = $client->sendAsync($request, $options)->then(function ($response) {
            return json_decode($response->getBody(), true);
        });

        return $promise->wait();
    }
}
