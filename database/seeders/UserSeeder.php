<?php

namespace Database\Seeders;

use Flynsarmy\CsvSeeder\CsvSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends CsvSeeder
{

    public function __construct()
    {
        $this->table = 'users';
        $this->filename = base_path() . '/database/seeders/import.csv';
        $this->offset_rows = 1;
        $this->timestamps = true;
        $this->mapping = [
            //0 => 'id',
            4 => 'name',
            5 => 'surname',
            6 => 'gender',
            10 => 'city',
            12 => 'country',
            13 => 'affiliation',
            14 => 'disciplinary',
            20 => 'email',
            24 => 'password',
            33 => 'email_verified_at'
        ];
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        // Recommended when importing larger CSVs
        //DB::disableQueryLog();

        parent::run();
    }


}
