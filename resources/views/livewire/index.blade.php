<?php

use App\Models\ProductTransaction;
use App\Models\Proposal;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shipment;
use App\Models\Product;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {
    public $totalPurchases;
    public $totalSales;
    public $totalProposals;
    public $totalShipments;
    public $totalTransactions;
    public $recentPurchases;
    public $recentSales;
    public $topSellingProducts;
    public $productInventory;
    public $salesByCategory;
    public $monthlyRevenue;
    public $averageOrderValue;

    public $salesTrend;
    public $purchaseTrend;
    public $inventoryStatus;
    public $topCustomers;
    public $revenueByProductCategory;

    public function mount(): void
    {
        $this->loadGeneralStats();
        $this->loadRecentTransactions();
        $this->loadTopSellingProducts();
        $this->loadProductInventory();
        // $this->loadSalesByCategory();
        $this->loadMonthlyRevenue();
        $this->loadAverageOrderValue();
    }

    private function loadGeneralStats(): void
    {
        $this->totalPurchases = Purchase::count();
        $this->totalSales = Sale::count();
        $this->totalProposals = Proposal::count();
        $this->totalShipments = Shipment::count();
        $this->totalTransactions = ProductTransaction::count();
    }

    private function loadRecentTransactions(): void
    {
        $this->recentPurchases = Purchase::with('supplier')->latest()->take(5)->get();
        $this->recentSales = Sale::with('contact')->latest()->take(5)->get();
    }

    private function loadTopSellingProducts(): void
    {
        $this->topSellingProducts = ProductTransaction::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('type', 'sale')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->take(5)
            ->get();
    }

    private function loadProductInventory(): void
    {
        $this->productInventory = Product::select('name', 'stock_code')
            ->orderBy('stock_code', 'desc')
            ->take(10)
            ->get();
    }

    private function loadSalesByCategory(): void
    {
        $this->salesByCategory = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(sales.total) as total_sales'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();
    }

    private function loadMonthlyRevenue(): void
    {
        $this->monthlyRevenue = Sale::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');
    }

    private function loadAverageOrderValue(): void
    {
        $this->averageOrderValue = Sale::avg('total');
    }

    public function with(): array
    {
        return [
            'recentPurchaseHeaders' => [
                ['key' => 'purchase_no', 'label' => 'Purchase No'],
                ['key' => 'supplier.name', 'label' => 'Supplier'],
                ['key' => 'total', 'label' => 'Total'],
                ['key' => 'created_at', 'label' => 'Date'],
            ],
            'recentSaleHeaders' => [
                ['key' => 'sales_no', 'label' => 'Sale No'],
                ['key' => 'contact.name', 'label' => 'Customer'],
                ['key' => 'total', 'label' => 'Total'],
                ['key' => 'created_at', 'label' => 'Date'],
            ],
        ];
    }
} ?>


<?php
// PHP kodu aynı kalacak
?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-8">
    <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 mb-12 text-center">
        ERP Dashboard</h1>

    <!-- Ana Metrikler -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8 mb-12">
        @php
            $gradients = [
                'from-blue-400 to-blue-600',
                'from-green-400 to-green-600',
                'from-yellow-400 to-yellow-600',
                'from-pink-400 to-pink-600',
                'from-purple-400 to-purple-600'
            ];
            $metrics = [
                ['title' => 'Toplam Alım', 'value' => $totalPurchases, 'icon' => 'shopping-cart'],
                ['title' => 'Toplam Satış', 'value' => $totalSales, 'icon' => 'cash-register'],
                ['title' => 'Toplam Teklif', 'value' => $totalProposals, 'icon' => 'file-contract'],
                ['title' => 'Toplam Sevkiayt', 'value' => $totalShipments, 'icon' => 'truck'],
                ['title' => 'Toplam Hareket', 'value' => $totalTransactions, 'icon' => 'exchange-alt'],
            ];
        @endphp

        @foreach($metrics as $index => $metric)
            <div class="card bg-white shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{$metric['title']}}</p>
                            <p class="text-3xl font-bold mt-1 bg-gradient-to-r {{$gradients[$index]}} bg-clip-text text-transparent">{{$metric['value']}}</p>
                        </div>
                        <div class="bg-gradient-to-br {{$gradients[$index]}} p-3 rounded-full">
                            <i class="fas fa-{{$metric['icon']}} text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Ek İstatistikler -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="card bg-white shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Aylık Ciro</h3>
                <p class="text-4xl font-bold text-blue-600">${{ number_format($monthlyRevenue, 2) }}</p>
                <div class="mt-4 text-sm text-gray-500">
                   {{-- <i class="fas fa-chart-line mr-2"></i>12% increase from last month--}}
                </div>
            </div>
        </div>
        <div class="card bg-white shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Ortalama Sipariş Değeri</h3>
                <p class="text-4xl font-bold text-green-600">${{ number_format($averageOrderValue, 2) }}</p>
                <div class="mt-4 text-sm text-gray-500">
                  {{--  <i class="fas fa-arrow-up mr-2"></i>5% increase from last week--}}
                </div>
            </div>
        </div>
    </div>

    <!-- Tablolar -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        @php
            $tableData = [
                [
                    'title' => 'Son Satın Alımlar',
                    'headers' => $recentPurchaseHeaders,
                    'data' => $recentPurchases,
                    'color' => 'blue'
                ],
                [
                    'title' => 'Son Satışlar',
                    'headers' => $recentSaleHeaders,
                    'data' => $recentSales,
                    'color' => 'green'
                ]
            ];
        @endphp

        @foreach($tableData as $table)
            <div class="card bg-white shadow-xl overflow-hidden">
                <div class="card-body p-0">
                    <div class="px-6 py-4 bg-{{$table['color']}}-600">
                        <h3 class="text-xl font-semibold text-white">{{$table['title']}}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                            <tr class="bg-gray-50">
                                @foreach($table['headers'] as $header)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $header['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($table['data'] as $row)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    @foreach($table['headers'] as $header)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($header['key'] === 'total')
                                                ${{ number_format($row->{$header['key']}, 2) }}
                                            @elseif($header['key'] === 'created_at')
                                                {{ $row->{$header['key']}->format('Y-m-d') }}
                                            @else
                                                {{ data_get($row, $header['key']) }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Ek İstatistikler -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @php
            $additionalStats = [
                [
                    'title' => 'Top Selling Products',
                    'headers' => [['label' => 'Product'], ['label' => 'Total Quantity']],
                    'data' => $topSellingProducts,
                    'color' => 'yellow'
                ],
                [
                    'title' => 'Product Inventory',
                    'headers' => [['label' => 'Product'], ['label' => 'Stock']],
                    'data' => $productInventory,
                    'color' => 'pink'
                ]
            ];
        @endphp

        @foreach($additionalStats as $stat)
            <div class="card bg-white shadow-xl overflow-hidden">
                <div class="card-body p-0">
                    <div class="px-6 py-4 bg-{{$stat['color']}}-600">
                        <h3 class="text-xl font-semibold text-white">{{$stat['title']}}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                            <tr class="bg-gray-50">
                                @foreach($stat['headers'] as $header)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $header['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($stat['data'] as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    @if($stat['title'] === 'Top Selling Products')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->total_quantity }}</td>
                                    @else
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->stock_code }}</td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
