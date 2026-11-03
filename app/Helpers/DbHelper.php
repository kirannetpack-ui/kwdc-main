<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DbHelper
{
    public static function getMonthFunction($column)
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            return "strftime('%m', {$column})";
        } elseif ($driver === 'pgsql') {
            return "EXTRACT(MONTH FROM {$column})";
        } else {
            return "MONTH({$column})";
        }
    }
    
    public static function getYearFunction($column)
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            return "strftime('%Y', {$column})";
        } elseif ($driver === 'pgsql') {
            return "EXTRACT(YEAR FROM {$column})";
        } else {
            return "YEAR({$column})";
        }
    }

}
