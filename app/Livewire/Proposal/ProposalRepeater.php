<?php

namespace App\Livewire\Proposal;

use App\Models\Product;
use App\Models\ProposalProduct;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Str;

class ProposalRepeater extends Component
{
    public bool $isEdit = false;

    public Collection|array|null $items = [];
    public ?Collection $products;

    public int $selectedLine = 0;
    public bool $createFirstLineOnInit = false;

    public string $currencyText = '';



    public float $totalAmount = 0;
    public float $subTotalAmount = 0;
    public float $totalVatAmount = 0;
    public float $totalDiscountAmount=0;
    public int $totalQuantity = 0;
    public $taxSummary = [];

    public function mount(): void
    {
        if (!$this->isEdit && $this->createFirstLineOnInit) {
            $this->items[] = $this->resetValues();
        }

        if ($this->isEdit) {
            collect($this->items)->map(function (ProposalProduct $proposalProduct, int $key) {
                $this->items[$key] = $proposalProduct;
                $this->items[$key]['formatted_price'] = number_format($proposalProduct->unit_price, 0, ',', '.');
            });

            $this->items = collect($this->items)->toArray();

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
            'discount_rate' => 0,

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

    public function updatedItems($value, $name): void
    {
        if (str_contains($name, 'product_id')) {

            if (!Str::isUuid($value))  {
                // Geçersiz UUID durumu için uygun işlem yapılabilir
                $this->addError('product_id', 'Geçersiz UUID formatı.');
                return;
            }



            $index = explode('.', $name)[0];

            $this->selectedLine = $index;

            $product = Product::find($value);

            $this->items[$index]['vat_rate'] = $product ? $product->tax_rate : 0;
            $this->items[$index]['notes'] = $product ? $product->name : '';
        }

        $this->calculate();
    }

    public function calculate(): void
    {
        $subTotalAmount =0;
        $totalAmount =0;
        $totalQuantity =0;
        $totalVatAmount=0;
        $totalDiscountAmount=0;


        foreach ($this->items as $index => $item) {


            $item['unit_price'] = (float)$item['unit_price'];
            $item['qty'] = (int)$item['qty'];
            $item['vat_rate'] = (float)$item['vat_rate'];



          /*  $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            $lineTotal = $linePrice + $vatAmount;*/

            $linePrice = $item['unit_price'] * $item['qty'];

           // $discountAmount=$linePrice * ($item['discount_rate'] / 100);
            $discountAmount = $linePrice * ((float)$item['discount_rate'] / 100);


            if (!empty($discountAmount)) {
                //$vatAmount = $linePrice * ($item['vat_rate'] / 100);
                $vatAmount =  ($linePrice-$discountAmount) * ($item['vat_rate'] / 100);
            } else{
                $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            }


            $lineTotal = $linePrice ;
            $subLinePrice = $linePrice - $discountAmount;

            $this->items[$index]['line_total'] = $lineTotal;
            $this->items[$index]['vat_line_total'] = $vatAmount;



            $subTotalAmount += $subLinePrice;
            $totalAmount += $lineTotal + $vatAmount-$discountAmount;
            $totalDiscountAmount+=$discountAmount;
            $totalVatAmount += $vatAmount;
            $totalQuantity += $this->items[$index]['qty'];

        }

        $this->totalDiscountAmount=$totalDiscountAmount;
        $this->totalVatAmount = $totalVatAmount;
        $this->subTotalAmount = $subTotalAmount;
       $this->totalAmount = $totalAmount;

        $this->totalQuantity = $totalQuantity;

        $this->dispatch('raise-selected-products', $this->items);
       $this->dispatch('raise-sub-total', $this->subTotalAmount);

       $this->dispatch('raise-total', $this->totalAmount);

        $this->dispatch('raise-vat-rate', $this->totalVatAmount);

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

            $taxAmount = $item['unit_price'] * ($item['vat_rate'] / 100);

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

        $this->items[] = $this->resetValues();
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculate(); // Recalculate totals after removing item
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
        return view('livewire.proposals.repeater');
    }
}
