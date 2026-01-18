<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\DashboardServiceInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {
    }

    /**
     * GET /api/dashboard
     * Tổng hợp dashboard (optional)
     */
    public function index(Request $request)
    {
        logger()->info($request->all());
        $year = $request->input('year', now()->year);

        return response()->json([
            'cards' => $this->dashboardService->getCards($year),
            'charts' => $this->dashboardService->getCharts($year),
            'tables' => $this->dashboardService->getTables($year),
        ]);
    }

    /**
     * GET /api/dashboard/cards
     * Dữ liệu cho các card (Revenue, Orders, Users, Reviews)
     */
    public function cards(Request $request)
    {
        $year = $request->input('year', now()->year);

        return response()->json(
            $this->dashboardService->getCards($year)
        );
    }

    /**
     * GET /api/dashboard/charts
     * Dữ liệu cho chart
     */
    public function charts()
    {
        return response()->json(
            $this->dashboardService->getCharts()
        );
    }

    /**
     * GET /api/dashboard/tables
     * Dữ liệu cho table (Top products, customers, recent activity)
     */
    public function tables()
    {
        return response()->json(
            $this->dashboardService->getTables()
        );
    }
}
