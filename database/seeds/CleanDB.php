<?php

use Illuminate\Database\Seeder;

class CleanDB extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::setDefaultConnection('testing');

        \DB::statement("SET foreign_key_checks = 0");

        // Truncate all tables, except migrations
        $tables = \DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            if ($table->{'Tables_in_'.env('DB_DATABASE_TEST')} !== 'migrations')
                \DB::table($table->{'Tables_in_'.env('DB_DATABASE_TEST')})->truncate();
        }

        \DB::statement("SET foreign_key_checks = 1");
    }
}
