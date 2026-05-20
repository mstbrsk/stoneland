<?php

namespace App\Livewire\Transport;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Exceptions\ToastException;

class Repeater extends Component
{

    public bool $isEdit = false;
    public bool $hasReceipt = false;
    public bool $createFirstLineOnInit = false;

    public Collection|array|null $items = [];
    public ?Collection $products;

    public int $selectedLine = 0;

    public float $totalAmount = 0;
    public float $subTotalAmount = 0;
    public int $totalQuantity = 0;
    public $taxSummary = [];

    public bool $showVariantModal = false;
    public $variants = [];
    public $variantQuantities = [];

    public function mount(): void
    {
        if ($this->createFirstLineOnInit && !$this->isEdit) {
            $this->items[] = $this->resetValues();
        }

        if ($this->isEdit) {
            collect($this->items)->map(function (PurchaseItem $purchaseItem, int $key) {
                $this->items[$key] = $purchaseItem;
                $this->items[$key]['formatted_price'] = number_format($purchaseItem->unit_price, 0, ',', '.');
            });

            $this->items = collect($this->items)->toArray();

            $this->calculate();
        }

        $this->products = Product::get()->map(fn(Product $product) => [
            'name' => $product->stock_code,
            'id' => $product->id,
        ]);
    }


    public function resetValues(): array
    {
        return [
            'product_id' => '',
            'unit_price' => null,
            'qty' => null,
            'vat_rate' => 10,
            'notes' => '',

            'formatted_price' => '',
            'vat_line_total' => 0,
            'line_total' => 0,
        ];
    }

    public function updatedItems($value, $name): void
    {
        if (str_contains($name, 'product_id') && $value) {
            $product = Product::find($value);

            $index = explode('.', $name)[0];
            $this->selectedLine = $index;

            $this->items[$index]['vat_rate'] = (float)($product ? $product->tax_rate : 0);

            $this->variants = ProductVariant::where('product_id', $value)->get();
            $this->variantQuantities = array_fill(0, count($this->variants), '');
            $this->showVariantModal = true;
        }

        $this->calculate();
    }

    public function saveVariantQuantities(): void
    {
        foreach ($this->variants as $index => $variant) {
            if (empty($this->variantQuantities[$index])) {
                continue;
            }

            $this->items[$this->selectedLine]['variants'][$variant->id] = (int)$this->variantQuantities[$index];
        }

        $this->items[$this->selectedLine]['qty'] = collect($this->variantQuantities)->sum(fn($value) => !empty($value) ? $value : 0);

        $this->showVariantModal = false;

        $this->calculate();
    }

    public function calculate(): void
    {
        $subTotalAmount = 0;
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($this->items as $index => $item) {
            $item['unit_price'] = (float)$item['unit_price'];
            $item['qty'] = (int)$item['qty'];
            $item['vat_rate'] = (float)$item['vat_rate'];

            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            $lineTotal = $linePrice + $vatAmount;

            $this->items[$index]['line_total'] = $lineTotal;
            $this->items[$index]['vat_line_total'] = $vatAmount;

            $subTotalAmount += $linePrice;
            $totalAmount += $lineTotal;
            $totalQuantity += $this->items[$index]['qty'];
        }

        $this->subTotalAmount = $subTotalAmount;
        $this->totalAmount = $totalAmount;
        $this->totalQuantity = $totalQuantity;

        $this->dispatch('raise-selected-products', $this->items);

        $this->calculateTaxSummary();
    }

    // Vergi özetini hesaplama
    public function calculateTaxSummary(): void
    {
        $this->taxSummary = [];

        foreach ($this->items as $item) {
            if (!isset($item['unit_price']) || !isset($item['vat_rate'])) {
                continue;
            }

            $item['unit_price'] = (float)$item['unit_price'];
            $item['vat_rate'] = (float)$item['vat_rate'];
            $item['qty'] = (int)$item['qty'];

            $taxAmount = $item['qty'] * $item['unit_price'] * ($item['vat_rate'] / 100);

            if (isset($this->taxSummary[$item['vat_rate']])) {
                $this->taxSummary[$item['vat_rate']] += $taxAmount;
            } else {
                $this->taxSummary[$item['vat_rate']] = $taxAmount;
            }
        }
    }

    /**
     * @throws ToastException
     */
    public function addItem(): void
    {
        foreach ($this->items as $item) {
            if (empty($item['product_id'])) {
                throw ToastException::error('Ürün seçmeden alt satıra geçmeyin!');
            }
        }

        $this->items[] = $this->resetValues();
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculate(); // Recalculate totals after removing item
    }

    public function editItem($index)
    {
        $product_id = $this->items[$index]['product_id'];

        if ($product_id) {
            $this->selectedLine = $index;

            $this->variants = ProductVariant::where('product_id', $product_id)->get();
            if (isset($this->items[$index]['variants'])) {
                $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);

                $this->showVariantModal = true;
            }

        }
    }







    public function render()
    {
        return view('livewire.transport.repeater');
    }
}
