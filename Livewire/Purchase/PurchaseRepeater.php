<?php

namespace App\Livewire\Purchase;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Str;

class PurchaseRepeater extends Component
{
    public bool $isEdit = false;
    public bool $hasReceipt = false;
    public bool $createFirstLineOnInit = false;

    public Collection|array|null $items = [];
    public ?Collection $products;

    public int $selectedLine = 0;



    public string $currencyText = '';


    public float $totalAmount = 0;
    public float $subTotalAmount = 0;
    public int $totalQuantity = 0;
    public $taxSummary = [];

    public bool $showVariantModal = false;
    public $variants = [];
    public $variantQuantities = [];

    public $quantities =[];



    public $colors = [];
    public $sizes = [];
    public $variantMatrix = [];

    public $subItems = [];

    public function mount(): void
    {
        if ($this->createFirstLineOnInit && !$this->isEdit) {
            $this->items[] = $this->resetValues();
        }

        /*if ($this->isEdit) {
              collect($this->items)->map(function (PurchaseItem $purchaseItem, int $key) {
                  $this->items[$key] = $purchaseItem;
                  $this->items[$key]['formatted_price'] = number_format($purchaseItem->unit_price, 0, ',', '.');
              });

              $this->items = collect($this->items)->toArray();

              $this->calculate();


          }*/


        if ($this->isEdit) {
            $this->items = collect($this->items)->map(function (PurchaseItem $purchaseItem, int $key) {
                $item = $purchaseItem->toArray();
                $item['formatted_price'] = number_format($purchaseItem->unit_price, 0, ',', '.');

                // Populate colors and sizes for each item
                $product = Product::find($purchaseItem->product_id);
                if ($product) {
                    $variants = ProductVariant::where('product_id', $product->id)->get();
                    foreach ($variants as $variant) {
                        $variantName = $variant->getVariantName(' ', false, false);
                        $attributes = explode(' ', $variantName);

                        if (count($attributes) >= 2) {
                            $color = $attributes[0];
                            $size = $attributes[1];
                            if (!in_array($color, $this->colors)) {
                                $this->colors[] = $color;
                            }
                            if (!in_array($size, $this->sizes)) {
                                $this->sizes[] = $size;
                            }
                            $this->variantMatrix[$color][$size] = $variant;
                        } else{



                        }
                    }
                }

                return $item;
            })->toArray();

            $this->calculate();
        }

        $this->products = Product::orderBy('name')->get()->map(fn(Product $product) => [
            'name' => $product->fullName(),
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

    #[On('currency-changed')]
    public function currencyChanged(mixed $value = null): void
    {
        $this->currencyText = $value;

        $this->updatedItems('currency', $this->currencyText);
    }


    public function updated()
    {


    }

    public function resetVariantState(): void
    {
        $this->variants = [];

        $this->colors = [];
        $this->sizes = [];
        $this->variantMatrix = [];
        $this->variantQuantities = [];
    }



    public function updatedItems($value, $name): void
    {
        if (str_contains($name, 'product_id') && $value) {

            if (!Str::isUuid($value))  {
                // Geçersiz UUID durumu için uygun işlem yapılabilir
                $this->addError('product_id', 'Geçersiz UUID formatı.');
                return;
            }


            $product = Product::find($value);

            $index = explode('.', $name)[0];
            $this->selectedLine = $index;

            $this->items[$index]['vat_rate'] = (float)($product ? $product->tax_rate : 0);
            $this->resetVariantState();

            $this->variants = ProductVariant::where('product_id', $value)->get();
            $this->setupVariantMatrix();
            $this->variantQuantities = array_fill(0, count($this->variants), '');


            $this->showVariantModal = true;

            $existingQuantities = $this->variantQuantities;
            $this->variantQuantities = [];

            $this->colors = [];
            $this->sizes = [];
            $this->variantMatrix = [];



            foreach ($this->variants as $variant) {
                $variantName = $variant->getVariantName(' ', false, false);
                $attributes = explode(' ', $variantName);


                if (count($attributes) >= 2) {
                    $color = $attributes[0]; // İlk değer renk
                    $size = $attributes[1];  // İkinci değer beden


                    if (!in_array($color, $this->colors)) {
                        $this->colors[] = $color;
                    }
                    if (!in_array($size, $this->sizes)) {
                        $this->sizes[] = $size;
                    }

                    $this->variantMatrix[$color][$size] = $variant;
                }



            }

        }



        $this->calculate();
    }




    /* public function sumVariantMatrix()
     {
         $totalQuantities = [];

         foreach ($this->variantMatrix as $color => $sizes) {
             foreach ($sizes as $size => $variant) {
                 $variantId = $variant->id;
                 if (isset($this->variantQuantities[$variantId]) && is_numeric($this->variantQuantities[$variantId])) {
                     $quantity = (int)$this->variantQuantities[$variantId];
                     if (!isset($totalQuantities[$color])) {
                         $totalQuantities[$color] = 0;
                     }
                     $totalQuantities[$color] += $quantity;

                 }
             }
         }

        return $totalQuantities;

     }*/


    public function sumVariantMatrix()
    {


        $totalQuantities = [];
        $grandTotal = 0;

        foreach ($this->variantMatrix as $color => $sizes) {
            foreach ($sizes as $size => $variant) {
                $variantId = $variant->id;
                if (isset($this->variantQuantities[$variantId]) && is_numeric($this->variantQuantities[$variantId])) {
                    $quantity = (int)$this->variantQuantities[$variantId];
                    if (!isset($totalQuantities[$color])) {
                        $totalQuantities[$color] = 0;
                    }
                    $totalQuantities[$color] += $quantity;
                    $grandTotal += $quantity;
                }
            }
        }

        return ['byColor' => $totalQuantities, 'total' => $grandTotal];
    }

    public function sumSingleVariantMatrix()
    {
        foreach ($this->variants as $index => $variant) {
            if (empty($this->variantQuantities[$index])) {
                continue;
            }

            $this->items[$this->selectedLine]['variants'][$variant->id] = (int)$this->variantQuantities[$index];

        }

        $this->items[$this->selectedLine]['qty'] = array_sum($this->variantQuantities);

        $this->showVariantModal = false;

        $this->calculate();
    }


    // yeni kod


    /*  public function saveVariantQuantities(): void
      {

          $totalQuantity = 0;
          foreach ($this->variants as $variant) {
              $variantId = $variant->id;
              if (isset($this->variantQuantities[$variantId]) && is_numeric($this->variantQuantities[$variantId])) {

                  $quantity = (int)$this->variantQuantities[$variantId];
                  $this->items[$this->selectedLine]['variants'][$variantId] = $quantity;
                  $totalQuantity += $quantity;

              }
          }

          $this->items[$this->selectedLine]['qty'] = $totalQuantity;


          $this->showVariantModal = false;

          $this->calculate();
      }*/

    public function saveVariantQuantities(): void
    {

        if (count($this->colors) > 0 && count($this->sizes) > 0) {

            $totals = $this->sumVariantMatrix();
        } else {
            $totals = $this->sumSingleVariantMatrix('size');
        }



        foreach ($this->variants as $variant) {
            $variantId = $variant->id;
            if (isset($this->variantQuantities[$variantId]) && is_numeric($this->variantQuantities[$variantId])) {
                $quantity = (int)$this->variantQuantities[$variantId];
                $this->items[$this->selectedLine]['variants'][$variantId] = $quantity;

            }
        }

        if (count($this->colors) > 0 && count($this->sizes) > 0) {
            $this->items[$this->selectedLine]['qty'] = $totals['total'];
        }


        $this->totalQuantity = $this->calculateTotalQuantity();

        $this->showVariantModal = false;
        $this->calculate();
    }

    private function calculateTotalQuantity()
    {
        return collect($this->items)->sum('qty');
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
            $lineTotal = $linePrice ;

            $this->items[$index]['line_total'] = $lineTotal;
            $this->items[$index]['vat_line_total'] = $vatAmount;



            $subTotalAmount += $linePrice;
            $totalAmount += $lineTotal + $vatAmount;
            $totalQuantity += $this->items[$index]['qty'];

        }

        $this->subTotalAmount = $subTotalAmount;
        $this->totalAmount = $totalAmount;
        $this->totalQuantity = $totalQuantity;

        $this->dispatch('raise-selected-products', $this->items);



        $this->calculateTaxSummary();

        $this->totalQuantity = $this->calculateTotalQuantity();
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

    /* public function editItem($index)
     {
         $product_id = $this->items[$index]['product_id'];

         if ($product_id) {
             $this->selectedLine = $index;

             $this->variants = ProductVariant::where('product_id', $product_id)->get();

             $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);

             $this->showVariantModal = true;
         }
     }*/

   /* public function editItem($index)
    {
        $product_id = $this->items[$index]['product_id'];

        if (count($this->colors) > 0 && count($this->sizes) > 0) {
            if ($product_id) {
                $this->selectedLine = $index;
                $this->resetVariantState();
                $this->variants = ProductVariant::where('product_id', $product_id)->get();
                $this->setupVariantMatrix();

                $this->variantQuantities = [];
                foreach ($this->variants as $variant) {
                    $variantId = $variant->id;
                    $this->variantQuantities[$variantId] = $this->items[$index]['variants'][$variantId] ?? 0;
                }

                // Variant matrisini, renkleri ve bedenleri sıfırla
                $this->variantMatrix = [];
                $this->colors = [];
                $this->sizes = [];

                // Varyant matrisini, renkleri ve bedenleri doldur
                foreach ($this->variants as $variant) {
                    $variantName = $variant->getVariantName(' ', false, false);
                    $attributes = explode(' ', $variantName);

                    if (count($attributes) >= 2) {
                        $color = $attributes[0];
                        $size = $attributes[1];

                        if (!in_array($color, $this->colors)) {
                            $this->colors[] = $color;
                        }
                        if (!in_array($size, $this->sizes)) {
                            $this->sizes[] = $size;
                        }

                        $this->variantMatrix[$color][$size] = $variant;
                    }
                }



                $this->showVariantModal = true;
            }} else {

            $this->setupVariantMatrix();

            $this->selectedLine = $index;

            $this->variants = ProductVariant::where('product_id', $product_id)->get();

            if (!empty($this->items[$index]['variants'])) {
                $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);
            }


            $this->showVariantModal = true;


        }

    }*/

    public function editItem($index)
    {
        $product_id = $this->items[$index]['product_id'];
        if ($product_id) {
            $this->selectedLine = $index;
            $this->resetVariantState();
            $this->variants = ProductVariant::where('product_id', $product_id)->get();

            if ($this->variants->isEmpty()) {
                // Eğer hiçbir varyant yoksa, hatalı bir durum olabilir
                return;
            }

            $this->setupVariantMatrix();

            $this->variantQuantities = [];

            if (count($this->colors) > 0 && count($this->sizes) > 0) {
                // Çok varyantlı ürünler için
                foreach ($this->variants as $variant) {
                    $variantId = $variant->id;
                    $this->variantQuantities[$variantId] = $this->items[$index]['variants'][$variantId] ?? 0;
                }
            } else {
                // Tek varyantlı ürünler için

                $this->selectedLine = $index;

                $this->variants = ProductVariant::where('product_id', $product_id)->get();

                if (!empty($this->items[$index]['variants'])) {
                    $this->variantQuantities = array_values($this->items[$index]['variants']) ?: array_fill(0, $this->variants->count(), 0);
                }

            }

            $this->showVariantModal = true;
        }
    }

    private function setupVariantMatrix()
    {
        foreach ($this->variants as $variant) {
            $variantName = $variant->getVariantName(' ', false, false);
            $attributes = explode(' ', $variantName);

            if (count($attributes) >= 2) {
                $color = $attributes[0];
                $size = $attributes[1];

                if (!in_array($color, $this->colors)) {
                    $this->colors[] = $color;
                }
                if (!in_array($size, $this->sizes)) {
                    $this->sizes[] = $size;
                }

                $this->variantMatrix[$color][$size] = $variant;
            } else {
                // Tek varyantlı ürünler için
                $this->variantMatrix['default'][$attributes[0]] = $variant;
            }
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


        return view('livewire.purchases.repeater', [
            'colors' => $this->colors,
            'sizes' => $this->sizes,
            'variantMatrix' => $this->variantMatrix,
            'totalQuantity' => $this->totalQuantity,
            'items' => $this->items,
            'subItems' => $this->subItems,

        ]);
    }
}
