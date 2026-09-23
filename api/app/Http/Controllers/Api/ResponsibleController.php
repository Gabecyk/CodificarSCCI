<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Responsible\ListResponsibleAction;

class ResponsibleController extends Controller
{
    public function index(ListResponsibleAction $listResponsibleAction)
    {
        $responsibles = $listResponsibleAction->execute();

        return response()->json($responsibles);
    }
}