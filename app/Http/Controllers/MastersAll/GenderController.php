<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use App\Models\Gender;

class GenderController extends Controller
{
    public function getGenderList()
    {
        $gender = Gender::all();
        return response()->json($gender);
    }
}
