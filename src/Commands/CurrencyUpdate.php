<?php

namespace Paksuco\Currency\Commands;

use Illuminate\Console\Command;
use Paksuco\Currency\Facades\Currency;

class CurrencyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update
    {--date=null : Date for getting historical data, formatted as YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the currency rates listed in paksuco/currencies';

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
     * @return int
     */
    public function handle()
    {
        $date = $this->option("date");
        $date = $date == "null" ? null : $date;
        Currency::updateRates($date);
    }
}
