<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Schema;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function restart()
    {
        Schema::disableForeignKeyConstraints();

        $tableNames = [
            'refresh_tokens', 'users', 'posts',
            'categories'
        ];

        foreach ($tableNames as $name) {
            DB::table($name)->truncate();
        }
        
        Schema::enableForeignKeyConstraints();

        $seeder = new DatabaseSeeder;

        $seeder->run();
    }
}
