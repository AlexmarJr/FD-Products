<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', Auth::id())->get();

        $totalProducts = $products->count();

        $statusCounts = $products->groupBy('status')->map->count()->toArray();
        $statusCounts = array_merge([
            'ativo' => 0,
            'pausado' => 0,
        ], $statusCounts);

        $topSuppliers = $products
            ->filter(function ($product) {
                return filled($product->supplier);
            })
            ->groupBy('supplier')
            ->map(fn ($items, $supplier) => [
                'name' => $supplier,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(20)
            ->values();

        $totalCost = $products->sum(function($p) { return $p->cost_price * $p->quantity; });
        $totalSale = $products->sum(function($p) { return $p->sale_price * $p->quantity; });
        $estimatedProfit = $totalSale - $totalCost;

        return response()->json([
            'total_products' => $totalProducts,
            'status_counts' => $statusCounts,
            'total_cost_value' => $totalCost,
            'total_sale_value' => $totalSale,
            'estimated_profit' => $estimatedProfit,
            'top_suppliers' => $topSuppliers,
        ]);
    }
}
