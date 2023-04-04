<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nationality;

class NationalityController extends Controller
{
    public function Nationality(){
        return Nationality::all();
    }
}
