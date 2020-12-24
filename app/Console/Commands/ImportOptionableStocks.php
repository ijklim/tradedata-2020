<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportOptionableStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradedata:import_optionable_stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect optionable stock symbols from csv files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Processing...');
        $this->line(\App\Http\Controllers\StockController::importOptionableStocks());
    }
}
