<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("currencies", function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country_code');
            $table->string("country_name");
            $table->string("currency_code");
            $table->string("currency_name");
            $table->string("direction", 2);
            $table->boolean("space");
            $table->string("symbol", 12);
            $table->integer("decimals");
            $table->decimal("rate", 12, 4, true)->default(1);
            $table->timestamps();
        });

        $currencies = json_decode(
            file_get_contents(
                base_path("database/migrations/currencies.json")
            )
        );

        foreach ($currencies as $currency) {
            DB::table("currencies")->insert([
                "country_code" => $currency->countryCode,
                "country_name" => $currency->countryName,
                "currency_code" => $currency->currencyCode,
                "currency_name" => $currency->currencyName,
                "direction" => $currency->direction ?? "R",
                "space" => $currency->space,
                "symbol" => $currency->symbol,
                "decimals" => $currency->decimals,
                "rate" => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("currencies");
    }
}
