<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Month;

class MonthController extends Controller
{
    public function Month(){
        return Month::get(['id', 'month_name']);
    }
}
