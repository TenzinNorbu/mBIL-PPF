<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sectortype;

class SectorTypeController extends Controller
{
    public function SectorType(){
        return Sectortype::get();
    }
}
