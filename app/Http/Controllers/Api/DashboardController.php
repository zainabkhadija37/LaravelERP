<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        return [
            'summary' => $this->dashboard->summary(),
            'top_selling_products' => $this->dashboard->topSellingProducts(),
        ];
    }
}
