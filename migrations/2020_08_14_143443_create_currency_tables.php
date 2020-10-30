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
     *
     */
    public function up()
    {
        $usersTable = config("currencies.users_table", "users");
        $usersColumn = config("currencies.currency_column", "currency_id");
        $usersHaveCurrencies = config("currencies.users_have_currencies", false);

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
            $table->boolean("active")->default(false);
            $table->decimal("rate", 12, 4, true)->default(1);
            $table->timestamps();
        });

        Schema::create('currency_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("currency_code");
            $table->string("base_currency");
            $table->decimal("rate", 12, 4, true)->default(1);
            $table->datetime("currency_at");
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

        if ($usersHaveCurrencies) {
            if (Schema::hasTable($usersTable)) {
                if (Schema::hasColumn($usersTable, $usersColumn) == false) {
                    Schema::table($usersTable, function (Blueprint $table) use ($usersColumn) {
                        $table->foreignId($usersColumn)->nullable();
                        $table->foreign($usersColumn)
                            ->references("id")
                            ->on("currencies")->nullOnDelete();
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $usersTable = config("currencies.users_table", "users");
        $usersColumn = config("currencies.currency_column", "currency_id");

        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable($usersTable)) {
            if (Schema::hasColumn($usersTable, $usersColumn)) {
                Schema::table($usersTable, function (Blueprint $table) use ($usersColumn) {
                    $table->dropColumn($usersColumn);
                });
            }
        }
        Schema::dropIfExists("currencies");
        Schema::dropIfExists("currency_history");
        Schema::enableForeignKeyConstraints();
    }
}
