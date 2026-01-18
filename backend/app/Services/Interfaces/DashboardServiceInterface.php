<?php

namespace App\Services\Interfaces;

interface DashboardServiceInterface
{
    public function getCards(int $year = null): array;
    public function getCharts(int $year = null): array;
    public function getTables(int $year = null): array;
}
