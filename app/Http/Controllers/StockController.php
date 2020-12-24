<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    use \App\Http\Controllers\Traits\Controller;

    /**
     * Create a new controller instance.
     *
     * @param  Stock  $items
     * @return void
     */
    public function __construct(Stock $items)
    {
        $this->items = $items;
        $this->className = get_class($items);
        $this->folderName = $this->className::getFolderName();
        // First field is the unique field
        $this->uniqueFieldName = 'symbol';
    }

    /**
     * Get rules for adding a new record or updating a record.
     *
     * @param  Stock  $item
     * @return string
     */
    private function getRules(Stock $item = null) {
        $uniqueFieldName = $this->uniqueFieldName;

        // Construct 'unique' rule
        if (isset($item)) {
            // .update rule
            $uniqueRule = Rule::unique($this->className::getTableName(), $uniqueFieldName)
                            ->ignore($item->$uniqueFieldName, $uniqueFieldName);
        } else {
            // .store rule
            $uniqueRule = Rule::unique($this->className::getTableName(), $uniqueFieldName);
        }

        $rules = [
            $uniqueFieldName => ['bail', 'required', $uniqueRule, 'max:5'],
            'name' => 'required|min:5',
            'data_source_id' => 'nullable|integer|min:0',
            'optionable' => 'boolean'
        ];

        return $rules;
    }

    /**
     * Display the specified resource.
     *
     * @param  Stock  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Stock $stock)
    {
        return view(
            $this->folderName . '.show',
            $this->getViewParameters(['item' => $stock])
        );
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
            return false;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function edit(Stock $stock)
    {
        return view(
            $this->folderName . '.change',
            $this->getViewParameters(['mode' => 'edit', 'item' => $stock])
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stock $stock)
    {
        $request->merge($this->getFormattedInputs($request));
        $validatedFields = $this->validate($request, $this->getRules($stock));

        try {
            $stock->update($validatedFields);
            // Show update info
            return redirect()
                    ->route($this->folderName . '.index')
                    ->with('success', $request->input($this->uniqueFieldName) . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->input($this->uniqueFieldName));
            // Back to edit page
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock $stock)
    {
        $status = $stock->delete();
        if ($status) {
            // Back to index page
            return redirect()->route($this->folderName . '.index')->with('success', $stock->getKeyValue() . ' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $stock->getKeyValue() . ' cannot be deleted, please try again.');
        }
    }

    public static function collectData(Stock $stock, $startDate = null, $endDate = null)
    {
        $defaultDate = '1960-01-01';
        $date_format_url = 'Ymd';
        $date_format_display = 'M j, Y';

        // Determine start and end date
        $maxDate = new \DateTime($stock->stockPrices()->max('date') ?? $defaultDate);
        $yesterday = (new \DateTime())->sub(new \DateInterval('P1D'));

        if ($yesterday > $maxDate) {
            $noOfDays = 50;

            // Compute start date
            $maxDate->add(new \DateInterval('P1D'));
            $startDate = $maxDate->format($date_format_url);

            // Compute end date
            $maxDate->add(new \DateInterval('P' . $noOfDays . 'D'));
            $endDate = $maxDate->format($date_format_url);
            // Send request to remote api and retrieve json data
            $dataSourceController = new \App\Http\Controllers\DataSourceController($stock->dataSource()->first());
            return $dataSourceController->collectData($stock->dataSource()->first(), $stock, $startDate, $endDate);
        } else {
            // Latest price data available up to yesterday
            // If maxDate = yesterday then there is nothing to collect
            return 'Latest data for ' . $stock->symbol . ' from ' .
                   $maxDate->format($date_format_display) .
                   ' has already been collected';
        }
    }

    /**
     * Locate csv files prefix with 'optionable--' in import folder and add new symbols to table `stocks`
     * Ref: http://php.net/manual/en/function.fgetcsv.php
     * Ref: http://php.net/manual/en/function.fopen.php
     */
    public static function importOptionableStocks()
    {
        $acceptablePrefix = 'optionable--';
        $importFolder = '.' . config('app.import_folder');
        $scanResults = scandir($importFolder);
        $newFilePrefix = (new \DateTime())->format("Ymd") . '__';

        function isNotStock($symbol) {
            return strlen($symbol) > 4;
        }

        function isNewStock($symbol) {
            return !(\App\Stock::find($symbol));
        }

        foreach ($scanResults as $scanResult):
            if ($scanResult === 'optionable--example.csv') continue;
            if (!preg_match('/(^optionable--)(.)+(.csv$)/', $scanResult)) continue;

            // Attempt to open csv file
            if (($handle = fopen($importFolder . '/' . $scanResult, "r")) === FALSE) continue;

            $A = ord('A');
            $columnSymbol = ord('A') - $A;
            $columnName = ord('C') - $A;
            $stockController = new \App\Http\Controllers\StockController(
                new \App\Stock()
            );

            // Iterate through each row in file
            while (($row = fgetcsv($handle)) !== FALSE) {
                $symbol = $row[$columnSymbol];
                if (isNotStock($symbol) || !isNewStock($symbol)) continue;

                $data = [
                    'symbol' => $symbol,
                    'name' => $row[$columnName],
                    'data_source_id' => "3",
                    'optionable' => true,
                ];
                $stockController->insert($data);
                echo "New stock added: $symbol\n";
            }

            while (is_resource($handle)) fclose($handle);

            rename(
                $importFolder . '/' . $scanResult,
                $importFolder . '/' . $newFilePrefix . $scanResult
            );
        endforeach;
    }
}
