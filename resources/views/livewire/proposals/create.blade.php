<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $crm_lead_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $delivery_address = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $invoice_address = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $contact_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public bool $has_contact = false;

    #[\Livewire\Attributes\Rule('sometimes')]
    public array $tags = [];
    /**************************************/

    #[\Livewire\Attributes\Rule('required')]
    public string $proposal_no;

    #[\Livewire\Attributes\Rule('required')]
    public int $cargo_type = \App\Enums\Proposal\CargoType::US->value;

    #[\Livewire\Attributes\Rule('sometimes')]
    public string $cargo_provider = '';

    #[\Livewire\Attributes\Rule('required')]
    public ?string $currency_id;

    #[\Livewire\Attributes\Rule('required')]
    public string $deadline_at;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $price_list_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?bool $is_renewable = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $payment_condition_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $notes = null;

    #[\Livewire\Attributes\Rule('sometimes')]
    public \Illuminate\Support\Collection $library;

    #[\Livewire\Attributes\Rule('required')]
    public int $status = 1;

    #[\Livewire\Attributes\Rule('required')]
    public string $created_by;

    #[\Livewire\Attributes\Rule('required')]
    public string $updated_by;

    /*****************************************************************/
    #[\Livewire\Attributes\Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $selectedProducts = null;

    public string $currency = '';

    /*****************************************************************/

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    #[On('raise-sub-total')]
    public function setSubTotal($subTotal)
    {
        $this->sub_total = $subTotal;
    }

    #[On('raise-total')]
    public function setTotal($total)
    {
        $this->total = $total;
    }

    #[On('raise-vat-rate')]
    public function setVatRate($totalVatAmount)
    {
        $this->totalVatAmount = $totalVatAmount;
    }

    #[On('raise-discount-rate')]
    public function setDiscountRate($totalDiscountAmount)
    {
        $this->totalDiscountAmount = $totalDiscountAmount;
        // tedst 1
    }


    public function mount()
    {
        $this->fill([
            'library' => collect(),
            'proposal_no' => generate_proposal_no(),
            'status' => \App\Enums\Proposal\ProposalStatus::DRAFT->value
        ]);
        // test2
    }

    public function save(): void
    {
        $items = $this->selectedProducts;

        if (empty($items)) {
            throw \Mary\Exceptions\ToastException::error('Lütfen ürün girişi yapınız!');
        }

        foreach ($items as $item) {
            if (empty($item['line_total'])) {
                throw \Mary\Exceptions\ToastException::error('Eksik alanları tamamlayın!');
            }
        }

        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $validated = $this->validate();

        $data = collect($validated)->except(['selectedProducts', 'images']);

        if ($this->has_contact) {
            $data['contact_id'] = $this->contact_id;
            $data['crm_lead_id'] = null;
            $data['delivery_address'] = null;
            $data['invoice_address'] = null;
        } else {
            $data['contact_id'] = null;
            $data['delivery_address_id'] = null;
            $data['invoice_address_id'] = null;
            $data['crm_lead_id'] = $this->crm_lead_id;
        }

        $itemList = [];

        $proposalId = Str::uuid();
        $data['id'] = $proposalId;

        $data['selected_items'] = $items;

        $data['sub_total'] = collect($items)->sum(fn(array $item) => $item['unit_price'] * $item['qty']);

        $data['total'] = collect($items)->sum(function (array $item) {
            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            return $linePrice + $vatAmount;
        });

        $data['quantity'] = collect($items)->sum('qty');

        $proposal = \App\Models\Proposal::create(to_case($data->toArray()));

        $this->currency = \App\Models\Currency::find($this->currency_id)?->name;

        foreach ($items as $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid(),
                'proposal_id' => $proposalId,

                'product_id' => $item['product_id'],
                'notes' => $item['notes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'discount_rate' => $item['discount_rate'],

                'vat_line_total' => $item['vat_line_total'],
                'line_total' => $item['line_total'],
            ];
        }

        \App\Models\ProposalProduct::insert($itemList);

        $this->syncMedia(model: $proposal, files: 'images', storage_subpath: 'proposals');

        $this->reset();

        $this->success('Teklif oluşturuldu.', redirectTo: '/proposals');

        log_action(message: 'Teklif oluşturuldu', relationType: 'Proposal', relationId: $proposal->id);
    }

    public function with(): array
    {
        return [
            'priceList' => \App\Models\PriceList::get(),

            'paymentConditions' => \App\Models\PaymentCondition::get(),

            'currencies' => \App\Models\Currency::get(),

            'contacts' => \App\Models\Contact::get(),

            'cargoProviders' => config('sap.cargo_providers'),

            'crmLeads' => \App\Models\CrmLead::latest()->get()->map(fn(\App\Models\CrmLead $lead) => [
                'id' => $lead->id,
                'name' => $lead->info(),
            ])->toArray()
        ];
    }
};
?>

<div>
    <x-header title="Yeni Teklif Oluştur" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-2 gap-6">
            <div class="col-span-1 gap-3 grid">
                <x-input wire:model="proposal_no" label="Teklif No" readonly/>

                <x-toggle wire:model="has_contact" label="Mevcut müşteri"/>

                <div x-show="$wire.has_contact===true">
                    <x-choices-offline searchable single label="Müşteri"
                                       wire:model.live="contact_id" :options="$contacts"
                                       style="height: 45px"/>
                </div>

                <div x-show="$wire.has_contact===false">
                    <x-choices-offline searchable single label="Fırsat Müşterisi"
                                       wire:model.live="crm_lead_id" :options="$crmLeads"
                                       style="height: 45px"/>
                </div>

                <x-radio label="Ödeme Yöntemi" :options="\App\Enums\Proposal\CargoType::listForMaryUI()"
                         wire:model="cargo_type"/>

                <x-choices-offline icon="o-rocket-launch" style="height: 45px" searchable label="Kargo"
                                   wire:model="cargo_provider" :options="$cargoProviders" single/>

                <x-select searchable placeholder="Seçiniz" single label="Para Birimi"
                          icon="o-credit-card"
                          :options="$currencies" wire:model="currency_id"
                          @change="$dispatch('currency-changed', [$(`#${$event.target.id} :selected`).text()])"
                          required style="height: 45px"/>

                <x-tags label="Etiket" wire:model="tags" icon="o-tag" hint="Eklemek için enter tuşuna basın"/>

                <x-image-library
                    crop-title-text="Görüntüyü Biçimlendir"
                    add-files-text="Görüntü Yükle"
                    change-text="Değiştir"
                    remove-text="Kaldır"
                    crop-text="Biçimlendir"
                    crop-cancel-text="Vazgeç"
                    crop-save-text="Kaydet"
                    wire:model="images"
                    wire:library="library"
                    :preview="$library"
                    label="Foto"
                    hint="Maks. 3MB"/>
            </div>

            <div class="col-span-1 gap-3 grid">
                <x-datetime icon="o-check" label="Geçerlilik Tarihi" wire:model="deadline_at" required type="date"/>

                <x-choices-offline searchable single :options="$priceList" label="Fiyat Listesi"
                                   icon="o-banknotes"
                                   wire:model="price_list_id" style="height: 45px"/>

                <x-choices-offline searchable single :options="$paymentConditions" label="Ödeme Koşulları"
                                   icon="o-banknotes"
                                   wire:model="payment_condition_id" style="height: 45px"/>

                <x-toggle label="Yenileme" wire:model="is_renewable"/>

                <x-textarea rows="5" label="Notlar" wire:model="notes"/>
            </div>

            <div class="col-span-2">
                <hr class="my-5"/>

                <livewire:proposal.proposal-repeater/>

            </div>
        </div>

        <x-slot:actions>

            <x-button label="Vazgeç" link="/sales"/>

            <x-button label="Kaydet" icon="o-paper-airplane" wire:click="save" spinner class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
