<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mpdels\Maritalstat;

class MaritalStatusController extends Controller
{
    public function Status(){
        return Maritalstat::all();
    }
}
