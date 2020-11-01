<?php

namespace Paksuco\Currency\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Paksuco\Currency\Models\Currency;
use Paksuco\Currency\Models\CurrencyHistory;
use Paksuco\Settings\Facades\Settings;

class CurrencyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update
    {--date=? : Date for getting historical data, formatted as YYYY-MM-DD}';

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
        // set API Endpoint and API key
        $date = $this->option("date");
        $endpoint = 'latest';
        if($date) $endpoint = $date;
        $access_key = Settings::get('fixer_api_key', "");

        if ($access_key == "") {
            return 0;
        }

        // Initialize CURL:
        $ch = curl_init('http://data.fixer.io/api/' . $endpoint . '?access_key=' . $access_key . '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        if ($exchangeRates && $exchangeRates["success"]) {
            $currencies = $exchangeRates["rates"];
            foreach ($currencies as $key => $value) {
                $currency = Currency::where("currency_code", "=", $key)->first();
                if ($currency instanceof Currency) {
                    $currency->rate = $value;
                    $currency->save();
                    if($currency->active){
                        CurrencyHistory::create([
                            "base_currency" => $exchangeRates["base"],
                            "currency_code" => $key,
                            "rate" => $value,
                            "currency_at" => $date ? Carbon::createFromFormat("Y-m-d", $date)->startOfDay() : Carbon::createFromTimestamp($exchangeRates["timestamp"])
                        ]);
                    }
                }
            }
        }

        return 0;
    }
}
