<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $pesananBelumDikirim = Order::where('status', '!=', 'completed')->count();
        $totalPenjualan = Order::where('status', 'completed')->sum('total');
        $totalPenjualan30HariTerakhir = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total');

        return [
            Card::make('30 Hari Terakhir', 'Rp' . number_format($totalPenjualan30HariTerakhir, 0, ',', '.'))
                ->description('Total penjualan dalam 30 hari terakhir')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            Card::make('Total Penjualan', 'Rp' . number_format($totalPenjualan, 0, ',', '.'))
                ->description('Pendapatan dari pesanan yang telah selesai')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            Card::make('Pesanan Belum Dikirim', $pesananBelumDikirim)
                ->description('Pesanan yang belum dikirim')
                ->color('danger')
                ->icon('heroicon-o-inbox'),
        ];
    }
}
