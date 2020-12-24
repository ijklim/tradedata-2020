<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockOptionController extends Controller
{
    use \App\Http\Controllers\Traits\Controller;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(\App\StockOption $items)
    {
        $this->items = $items;
        $this->className = get_class($items);
        $this->folderName = $this->className::getFolderName();
        // First field is the unique field
        $this->uniqueFieldName = '';
    }

    /**
     * Get rules for adding a new record or updating a record.
     *
     * @param  Stock  $item
     * @return string
     */
    private function getRules(\App\StockOption $item = null) {
        $rules = [
            'symbol' => 'required',
            'date' => 'required|date',
            'option_data' => 'required',
        ];

        return $rules;
    }

    /**
     * Insert a newly created resource in storage.
     * Different from store() as this method would not return to a user screen.
     *
     * @param  array  $data
     * @return boolean
     */
    public function insert($data)
    {
        try {
            $request = request();
            $request->merge($data);
            $request->merge($this->getFormattedInputs($request));
            $validatedFields = $this->validate($request, $this->getRules());
            $this->className::create($validatedFields);
            return true;
        } catch (\Exception $e) {
            // If insert fail, just return false
            echo $e;
            return false;
        }
    }

    /**
     * Retrieve random stock option analysis from db
     *
     * @return Array
     */
    public static function apiGetRandomOptionAnalysis()
    {
        // Pass false to getStocks for testing only
        $stocks = static::getStocks();
        if ($stocks->count() === 0) $items = [];
        else {
            $stock = $stocks->take(1)->get()->first();
            $items = static::getOptionAnalysis($stock);
        }

        return view(
            'api.json',
            compact('items')
        );
    }


    /**
     * Retrieve stocks with updated option data
     * $recent indicates stock option data has been updated within the last 15 minutes
     * 
     * @param  Boolean  $recent
     * @return \App\Stock
     */
    public static function getStocks($recent = true)
    {
        $timeThreshold = (new \DateTime())->modify('-15 minutes')->format('Y-m-d H:i:s');
        return \App\Stock
                ::inRandomOrder()
                ->where('optionable', true)
                ->where('updated_at', $recent ? '>' : '<', $timeThreshold); // TODO: change to >
    }

    /**
     * Default settings
     * 
     * @return Array
     */
    public static function getAnalysisSettings()
    {
        return [
            'maxDaysLeft' => 365,
            'minDaysLeft' => 6,
            'strikeCount' => 8
        ];
    }

    /**
     * Perform analysis on option_data in the database of a particular stock
     * 
     * @return Array
     */
    public static function getOptionAnalysis(\App\Stock $stock)
    {
        $analysisSettings = static::getAnalysisSettings();
        $option_data = json_decode($stock->option_data);

        $extractOptionData = function($expDateMap, $analysisSettings)
        {
            $result = [];
            foreach ($expDateMap as $key => $value):
                // One particular date, e.g. key = 2018-03-26:2
                list($date, $daysLeft) = explode(':', $key);
                if ($daysLeft < $analysisSettings['minDaysLeft']) continue;
                if ($daysLeft > $analysisSettings['maxDaysLeft']) continue;

                // For each strike price, e.g. 159.0, 159.5, 160.0...
                foreach($value as $strikeKey => $strikeValue):
                    // Skip in the money
                    if (abs($strikeValue[0]->inTheMoney)) continue;
                    // Skip Weeklies
                    if ($strikeValue[0]->expirationType !== 'R') continue;
                    // Skip no bid or ask price, i.e. low demand
                    if ($strikeValue[0]->ask == 0 || $strikeValue[0]->bid == 0) continue;

                    $result[] = [
                        'bidAskGap' => round($strikeValue[0]->ask - $strikeValue[0]->bid, 2),
                        'date' => $date,
                        'daysLeft' => $daysLeft,
                        'description' => $strikeValue[0]->description,
                        'mark' => round(($strikeValue[0]->ask + $strikeValue[0]->bid) / 2, 2),
                        'quoteTime' => $strikeValue[0]->quoteTimeInLong,
                        'strikePrice' => $strikeValue[0]->strikePrice,
                        'theta' => round($strikeValue[0]->theta, 3)
                    ];
                endforeach;
            endforeach;

            return $result;
        };


        $result = [
            'symbol' => $stock->symbol,
            'name' => $stock->name,
            'price' => $option_data->underlyingPrice,
            'calls' => $extractOptionData($option_data->callExpDateMap, $analysisSettings),
            'puts' => $extractOptionData($option_data->putExpDateMap, $analysisSettings),
        ];

        return $result ?? [];
    }

    
    /**
     * Collect random stock option data from TD Ameritrade and save data to db
     * 
     * @return Boolean
     */
    public static function updateRandomData()
    {
        $stocks = static::getStocks(false);
        if ($stocks->count() === 0) return false;

        $stock = $stocks
                    ->inRandomOrder()
                    ->take(1)
                    ->get()
                    ->first();
        
        echo "\n" . 'Collect random option data from TDA: ' . $stock->symbol . '(' . $stock->name . ')...';
        $data = \App\Http\Controllers\DataSources\TDAmeritradeController::collectOptionData($stock->symbol, static::getAnalysisSettings()['strikeCount']);
        static::saveOptionData($stock, $data);
        return true;
    }

    /**
     * Save option data in table `stocks` and `stock_options`
     * 
     * @return void
     */
    private static function saveOptionData(\App\Stock $stock, $option_data)
    {
        // Store latest data in `stocks.option_data` for live access
        $stock->option_data = json_encode($option_data);
        $stock->save();

        /**
         * Retrieve quote date in format YYYY-MM-DD from json data retrieved from TDA
         */
        $getQuoteDate = function($option_data)
        {
            // Retrieve call array
            $source = $option_data['callExpDateMap'];
            // Dig 3 layers deep
            for ($i = 1; $i <= 3; $i++) $source = array_values($source)[0];
            $quoteTimeInLong = $source['quoteTimeInLong'];
            return gmdate("Y-m-d", substr($quoteTimeInLong, 0, 10));
        };

        // Store historical data in `stock_options`, one entry per day
        $quoteDate = $getQuoteDate($option_data);

        // Data for that day has already been collected
        if ($stock->stockOptions()->where('date', $quoteDate)->count() > 0) return;

        // Save data
        $data = [
            'symbol' => $stock->symbol,
            'date' => $quoteDate,
            'option_data' => $stock->option_data,
        ];
        (new \App\Http\Controllers\StockOptionController(
            new \App\StockOption()
        ))->insert($data);
    }

    


    /**
     * (Obsolete) Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view(
            $this->folderName . '.index'
        );
    }

    /**
     * (Obsolete) Get all stock symbols that are optionable
     */
    public static function getOptionableStockSymbols()
    {
        $items = \App\Stock::where('optionable', 1)
                    ->get()
                    ->pluck('symbol');
        return view(
            'api.json',
            compact('items')
        );
    }



}
