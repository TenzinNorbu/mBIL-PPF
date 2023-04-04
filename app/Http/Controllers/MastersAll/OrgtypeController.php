<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use App\Models\Orgtype;

class OrgtypeController extends Controller
{
    public function index()
    {
        $orgType = Orgtype::all();
        return $orgType;
    }
}
