<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectOptionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradedata:collect_option_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect stock option data from an external source';

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
        // Attempt to collect option data for a random stock
        // Schedule task runs every minute
        $noOfRequests = 0;
        $startTime = time();
        $maxTime = 50;
        $status = true;
        $this->line('Initiating collection of option data...');
        while ((($startTime + $maxTime) > time()) && $status) {
            $status = \App\Http\Controllers\StockOptionController::updateRandomData();
            $noOfRequests++;
        }
        $this->line("\nSent $noOfRequests requests");
    }
}
