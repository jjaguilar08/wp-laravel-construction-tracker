<?php

namespace App\Http\Controllers;

use App\Services\ConstructionLogService;

class ConstructionLogController extends Controller
{
    public function index(ConstructionLogService $service)
    {
        return $service->getLogs();
    }
}
