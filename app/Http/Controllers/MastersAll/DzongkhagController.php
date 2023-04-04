<?php

namespace App\Http\Controllers\MastersAll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dzongkhag;

class DzongkhagController extends Controller
{
    public function DzongkhagList(){
        return Dzongkhag::all();
    }
}
