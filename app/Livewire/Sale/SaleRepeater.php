<?php

namespace App\Livewire\Sale;

use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeItem;
use App\Models\ProductVariant;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Mary\Traits\Toast;
use Str;

class SaleRepeater extends Component
{
    use Toast;

    public bool $isEdit = false;
    public bool $hasReceipt = false;
    public bool $createFirstLineOnInit = true;

    public ?string $contact_id = null;
    public ?PriceList $priceList = null;

    public Collection|array|null $items = [];
    public ?Collection $products;

    public int $selectedLine = 0;

    public string $currencyText = '';


    public float $totalAmount = 0;
    public float $subTotalAmount = 0;
    public int $totalQuantity = 0;
    public float $totalDiscountAmount = 0;
    public $taxSummary = [];

    public bool $showVariantModal = false;
    public $variants = [];
    public $variantQuantities = [];

    public ?Product $selectedProduct = null;

    public array $groupValues = [];

    public array $colors = [];
    public array $sizes = [];


    public function mount(): void
    {
        if ($this->createFirstLineOnInit && !$this->isEdit) {
            $this->items[] = $this->resetValues();
        }


        if ($this->isEdit) {
            collect($this->items)->map(function (SaleItem $saleItem, int $key) {
                $this->items[$key] = $saleItem;
                $this->items[$key]['formatted_price'] = number_format($saleItem->unit_price, 0, ',', '.');
            });

            $this->items = collect($this->items)->toArray();

            $this->calculate();
        }

        $this->products = Product::orderBy('name')->get()->map(fn(Product $product) => [
            'name' => $product->fullName(),
            'id' => $product->id,
        ]);
    }

    /* #[On('contact-changed')]
     public function contactChangedFromParent(string $value)
     {
         $this->contact_id = $value;

         if (!is_null($this->priceList)) {
             $this->calculate();
         }
     }*/

    #[On('price-list-changed')]
    public function priceListChangedFromParent(?string $value)
    {
        if (is_null($value)) {
            return;
        }

        $this->priceList = PriceList::find($value);

        $this->calculate();
    }

    public function resetValues(): array
    {
        return [
            'product_id' => '',
            'unit_price' => null,
            'qty' => null,
            'vat_rate' => 10,
            'notes' => '',
            'discount_rate' => 0,
            'receipt' => false,

            'formatted_price' => '',
            'vat_line_total' => 0,
            'line_total' => 0,
        ];
    }

    #[On('currency-changed')]
    public function currencyChanged(mixed $value = null): void
    {
        $this->currencyText = $value;

        $this->updatedItems('currency', $this->currencyText);
    }

    /**
     * @throws ToastException
     */
    public function updatedItems($value, $name): void
    {
        if (str_contains($name, 'product_id') && $value) {

            if (!Str::isUuid($value)) {
                // Geçersiz UUID durumu için uygun işlem yapılabilir
                $this->addError('product_id', 'Geçersiz UUID formatı.');
                return;
            }


            $this->selectedProduct = Product::find($value);

            $index = explode('.', $name)[0];

            $this->selectedLine = $index;

            $this->items[$index]['vat_rate'] = (float)($this->selectedProduct ? $this->selectedProduct->tax_rate : 0);

            $this->variants = ProductVariant::where('product_id', $value)->get();
            $this->variantQuantities = array_fill(0, count($this->variants), '');
            $this->showVariantModal = true;


            /*  if ($this->variants) {
                  $productAttributeIds = $this->variants->pluck('attribute_items')->flatten()->unique()->values();

                  $productAttributeIds = json_decode($productAttributeIds, true);

                  dd($productAttributeIds);

                  $productAttributeItems = ProductAttributeItem::whereIn('id', $productAttributeIds)->get();

                  dd($productAttributeItems);

                  $groups = $productAttributeItems->groupBy('product_attribute_id');

                  if ($groups->count() > 2) {
                      throw ToastException::error('Nitelik sayısı maksimum iki olabilir!');
                  }

                  $this->groupValues = [];

                  $groups->map(function (Collection $productAttributeItems, string $productAttributeId) {
                      $name = ProductAttribute::select('id', 'name')->where('id', $productAttributeId)->value('name');

                      foreach ($productAttributeItems->toArray() as $productAttributeItem) {
                          $this->groupValues[] = [
                              'name' => $name,
                              'product_attribute_id' => $productAttributeItem['product_attribute_id'],
                              'product_attribute_item_id' => $productAttributeItem['id'],
                              'value' => $productAttributeItem['value'],
                          ];
                      }
                  });

                  $this->colors = collect($this->groupValues)->where('name', 'Renk')->values()->all();
                  $this->sizes = collect($this->groupValues)->where('name', 'Beden')->values()->all();
              }*/
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
        $totalDiscountAmount = 0;

        foreach ($this->items as $index => $item) {
            $item['unit_price'] = (float)$item['unit_price'];

            if ($this->priceList) {
                $item['unit_price'] = PriceList::calculate($this->priceList, $item['unit_price']);
            }

            $item['qty'] = (int)$item['qty'];
            $item['vat_rate'] = (float)$item['vat_rate'];

            $linePrice = $item['unit_price'] * $item['qty'];


            $discountAmount = $linePrice * ((float)$item['discount_rate'] / 100);


            if (!empty($discountAmount)) {
                //$vatAmount = $linePrice * ($item['vat_rate'] / 100);
                $vatAmount = ($linePrice - $discountAmount) * ($item['vat_rate'] / 100);
            } else {
                $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            }


            $lineTotal = $linePrice;
            $subLinePrice = $linePrice - $discountAmount;

            $this->items[$index]['line_total'] = $lineTotal;
            $this->items[$index]['vat_line_total'] = $vatAmount;
            $this->items[$index]['formatted_price'] = $item['unit_price'];

            $subTotalAmount += $subLinePrice;
            $totalDiscountAmount += $discountAmount;
            $totalAmount += $lineTotal + $vatAmount - $discountAmount;
            $totalQuantity += $this->items[$index]['qty'];
        }
        $this->totalDiscountAmount = $totalDiscountAmount;
        $this->subTotalAmount = $subTotalAmount;
        $this->totalAmount = $totalAmount;
        $this->totalQuantity = $totalQuantity;

        $this->dispatch('raise-selected-products', $this->items);

        $this->dispatch('raise-discount-rate', $this->totalDiscountAmount);

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
            $linePrice = $item['unit_price'] * $item['qty'];
            $discountAmount = $linePrice * ((float)$item['discount_rate'] / 100);
            if (!empty($discountAmount)) {
                $taxAmount = ($linePrice - $discountAmount) * ($item['vat_rate'] / 100);
            } else {
                $taxAmount = $item['qty'] * $item['unit_price'] * ($item['vat_rate'] / 100);
            }

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
        if (empty($this->currencyText)) {
            throw ToastException::error('Önce para birimi seçiniz!');
        }

        foreach ($this->items as $item) {
            if (empty($item['line_total'])) {
                throw ToastException::error('Eksik alanları tamamlayın!');
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

            if (!empty($this->items[$index]['variants'])) {
                $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);
            }


            $this->showVariantModal = true;
        }
    }

    public function search(string $value = ''): void
    {
        $this->products = Product::query()
            ->whereLike(['name', 'stock_code'], $value)
            ->take(5)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.sales.repeater');
    }
}
