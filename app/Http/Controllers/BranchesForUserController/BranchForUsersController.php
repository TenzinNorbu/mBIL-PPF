<?php

namespace App\Http\Controllers\BranchesForUserController;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Exception;

class BranchForUsersController extends Controller
{
    public function index()
    {
       try{ $branches = Branch::all();
        return response()->json($branches);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
