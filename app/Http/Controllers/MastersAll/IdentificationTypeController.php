<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Identificationtype;

class IdentificationTypeController extends Controller
{
    public function IdentificationType(){
        return Identificationtype::all();
    }
}
