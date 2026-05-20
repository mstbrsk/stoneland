<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;
    use Mary\Traits\WithMediaSync;

    public \App\Models\Proposal $proposal;
    public \Illuminate\Support\Collection $items;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $crm_lead_id;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $delivery_address = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $invoice_address = '';


    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $contact_id = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public bool $has_contact = false;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?array $tags = [];
    /**************************************/

    #[\Livewire\Attributes\Rule('required')]
    public string $proposal_no;

    #[\Livewire\Attributes\Rule('required')]
    public ?int $cargo_type;

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $cargo_provider;

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

    /*****************************************************************/

    public \App\Enums\Proposal\ProposalStatus $statusAsEnum;

    public string $currency = '';

    public bool $showHistoryDrawer = false;

    public bool $showMessageLogsDrawer = false;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showConvertModal = false;
    public bool $showDeleteModal = false;
    public bool $showPrintModal = false;

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
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
    }


    public function mount()
    {
        $proposal = \App\Models\Proposal::findOrFail($this->proposal->id);
        $proposal->makeHidden('library');

        $this->fill($proposal);


        $this->deadline_at = date('Y-m-d', $proposal->deadline_at->getTimestamp());

        $this->items = $proposal->products;


        $this->library = $this->proposal->library;
        $this->statusAsEnum = $this->proposal->status;

        $this->currency = \App\Models\Currency::findOrFail($this->currency_id)->name;
    }

    public function archive()
    {
        $this->proposal->update([
            'status' => \App\Enums\Proposal\ProposalStatus::ARCHIVE
        ]);

        log_action(message: 'Teklif arşive alındı', relationType: 'Proposal', relationId: $this->proposal->id);

        $this->success('Teklif arşive alındı.', redirectTo: '/proposals');
    }

    public function reject()
    {
        if ($this->statusAsEnum !== \App\Enums\Proposal\ProposalStatus::DRAFT) {
            throw \Mary\Exceptions\ToastException::error('Bu teklif ret için uygun değil!');
        }

        $this->proposal->makeArchive();

        log_action(message: 'Teklif reddedildi', relationType: 'Proposal', relationId: $this->proposal->id);

        log_action(message: 'Teklif arşive alındı', relationType: 'Proposal', relationId: $this->proposal->id);

        $this->success('Teklif reddedildi.', redirectTo: '/proposals');
    }

    public function convertToSale()
    {
        if ($this->statusAsEnum !== \App\Enums\Proposal\ProposalStatus::DRAFT) {
            throw \Mary\Exceptions\ToastException::error('Sadece taslak teklifler işleme alınabilir!');
        }

        if (!$this->proposal->contact_id) {
            throw \Mary\Exceptions\ToastException::error('Cari hesabı bulunamadı!');
        }

        $this->proposal->update([
            'status' => \App\Enums\Proposal\ProposalStatus::CONVERTED_TO_SALE
        ]);

        $sale = \App\Models\Sale::create([
            'sales_no' => $this->proposal->proposal_no,
            'contact_id' => $this->proposal->contact_id,
            'currency_id' => $this->proposal->currency_id,
            'delivery_address_id' => $this->proposal->delivery_address_id,
            'invoice_address_id' => $this->proposal->invoice_address_id,
            'deadline_at' => $this->proposal->deadline_at,
            'price_list_id' => $this->proposal->price_list_id,
            'is_renewable' => $this->proposal->is_renewable,
            'payment_condition_id' => $this->proposal->payment_condition_id,
            'quantity' => $this->proposal->quantity,
            'sub_total' => $this->proposal->sub_total,
            'total' => $this->proposal->total,
            'notes' => $this->proposal->notes,
            'status' => \App\Enums\Sale\SaleStatus::WAS_PROPOSAL,
            'created_by' => auth('web')->id(),
            'updated_by' => auth('web')->id(),
            'was_proposal' => true,
            'proposal_id' => $this->proposal->id
        ]);

        foreach ($this->proposal->products->toArray() as $key => $item) {
            if (empty($item['product_id'])) {
                $this->error('Ürün seçiniz!');
                return;
            }

            $itemList[] = [
                'id' => Str::uuid(),
                'sale_id' => $sale->id,

                'product_id' => $item['product_id'],
                'notes' => $item['notes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'discount_rate' => $item['discount_rate'],
                'vat_line_total' => $item['vat_line_total'],
                'line_total' => $item['line_total'],
                'selected_variants' => null,
            ];
        }

        \App\Models\SaleItem::insert($itemList);


        $this->proposal->makeArchive();

        log_action(message: 'Teklif arşive alındı', relationType: 'Proposal', relationId: $this->proposal->id);

        log_action(message: 'Satış tekliften oluşturuldu', relationType: 'Sale', relationId: $sale->id);

        log_action(message: 'Teklif satışa çevrildi', relationType: 'Proposal', relationId: $this->proposal->id);

        $this->success('Teklif satışa çevrildi.', redirectTo: "/sales/{$sale->id}/edit");
    }

    public function showMessageLogsDrawer()
    {
        $this->showMessageLogsDrawer = true;
    }

    public function save(): void
    {
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
            $data['delivery_address'] = $this->delivery_address;
            $data['invoice_address'] = $this->invoice_address;
        }

        $itemList = [];
        $proposalId = $this->proposal->id;
        $data['id'] = $proposalId;

        $items = $this->selectedProducts;

        if (empty($items)) {
            throw \Mary\Exceptions\ToastException::error('Lütfen ürün girişi yapınız!');
        }

        foreach ($items as $item) {
            if (empty($item['line_total'])) {
                throw \Mary\Exceptions\ToastException::error('Eksik alanları tamamlayın!');
            }
        }

        $this->currency = \App\Models\Currency::findOrFail($this->currency_id)->name;


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

        $data['selected_items'] = $items;

        $data['sub_total'] = collect($itemList)->sum(fn(array $item) => $item['unit_price'] * $item['qty']);

        $data['total'] = collect($itemList)->sum(function (array $item) {
            $linePrice = $item['unit_price'] * $item['qty'];
            $vatAmount = $linePrice * ($item['vat_rate'] / 100);
            return $linePrice + $vatAmount;
        });

        $data['quantity'] = collect($itemList)->sum('qty');

        $this->proposal->update(
            to_case($data->toArray())
        );

        \App\Models\ProposalProduct::where('proposal_id', $this->proposal->id)->delete();

        \App\Models\ProposalProduct::insert($itemList);

        $this->syncMedia(model: $this->proposal, files: 'images', storage_subpath: 'proposals');

        $this->success('Teklif düzenlendi.', redirectTo: '/proposals');

        log_action(message: 'Teklif düzenlendi', relationType: 'Proposal', relationId: $this->proposal->id);

        $this->reset();
    }

    public function with(): array
    {
        return [
            'priceList' => \App\Models\PriceList::get(),

            'paymentConditions' => \App\Models\PaymentCondition::get(),

            'currencies' => \App\Models\Currency::get(),

            'contacts' => \App\Models\Contact::get(),

            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray(),

            'cargoProviders' => config('sap.cargo_providers'),

            'crmLeads' => \App\Models\CrmLead::latest()->get()->map(fn(\App\Models\CrmLead $lead) => [
                'id' => $lead->id,
                'name' => "{$lead->contact_name} - {$lead->contacted_person} ({$lead->contacted_at->format('d-m-Y H:i')})",
            ])->toArray()
        ];
    }

    public function print(int $type)
    {
        to_route('proposals.print', ['id' => $this->proposal->id, 'type' => $type]);
    }
};
?>

<div>
    <x-modal wire:model="showPrintModal" title="Teklif">
        <div>Teklif yazdırma seçeneği?</div>
        <x-slot:actions>
            <x-button label="Fiyatlı" wire:click="print(1)"/>
            <x-button label="Fiyatsız" wire:click="print(2)" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showApproveModal" title="Teklif">
        <div>Bu teklif onaylanıp, satışa çevrilecek.Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showApproveModal = false"/>
            <x-button label="Evet" wire:click="convertToSale" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showRejectModal" title="Teklif">
        <div>Bu teklif reddedilecek.Emin misiniz?</div>
        <x-slot:actions>
            <x-button label="Hayır" @click="$wire.showRejectModal = false"/>
            <x-button label="Evet" wire:click="reject" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <livewire:action-log key="{{ Str::random() }}" relation-type="Proposal" :relation-id="$proposal->id"
                         :show-history-drawer="$showHistoryDrawer"/>

    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/proposals/{$proposal->id}/edit" }}'
                          relation-type="Sale"
                          :relation-id="$proposal->id"/>

    <x-header title="Teklif" subtitle="Düzenle">
        <x-slot:middle class="!justify-end">
            <div>
                <x-steps wire:model="status">
                    @foreach(\App\Enums\Proposal\ProposalStatus::localize() as $key=> $status)
                        <x-step step="{{ $key+1 }}" text="{{ $status }}"/>
                    @endforeach
                </x-steps>
            </div>
        </x-slot:middle>


        <x-slot:actions>
            <div>
                <x-dropdown label="İşlemler" class="btn-outline">
                    <x-menu-item title="Yazdır" icon="o-printer" @click="$wire.showPrintModal = true"/>

                    @if ($statusAsEnum===\App\Enums\Proposal\ProposalStatus::DRAFT)
                        <x-menu-item title="Onayla" icon="o-check" @click="$wire.showApproveModal = true"/>

                        <x-menu-item title="Reddet" icon="o-x-circle" @click="$wire.showRejectModal = true"/>
                    @endif

                    @if ($statusAsEnum->canMakeArchive())
                        <x-menu-item title="Arşive Al" icon="o-envelope" wire:click="archive"
                                     wire:confirm="Emin misiniz?"/>
                    @endif
                    <x-menu-separator/>

                    <x-menu-item title="İşlem Geçmişi" icon="o-film" wire:click="$set('showHistoryDrawer',true)"/>

                    <x-menu-item title="Mesaj Geçmişi" icon="o-envelope" @click="messageHistoryModal.showModal()"/>
                </x-dropdown>
            </div>
        </x-slot:actions>
    </x-header>

    <x-alert title="{{ $statusAsEnum->text() }}"
             class="alert-warning"
             icon="o-exclamation-triangle"/>

    <br>

    <x-form wire:submit="save" :inert="!$statusAsEnum->isDraft()">
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

                <x-choices-offline style="height: 45px" searchable label="Kargo" wire:model="cargo_provider"
                                   :options="$cargoProviders" single/>

                <x-select searchable placeholder="Seçiniz" single label="Para Birimi"
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
                <x-datetime label="Geçerlilik Tarihi" wire:model="deadline_at" required type="date"/>

                <x-choices-offline searchable single :options="$priceList" label="Fiyat Listesi"
                                   wire:model="price_list_id" style="height: 45px"/>

                <x-choices-offline searchable single :options="$paymentConditions" label="Ödeme Koşulları"
                                   wire:model="payment_condition_id" style="height: 45px"/>

                <x-toggle label="Yenileme" wire:model="is_renewable"/>

                <x-textarea rows="5" label="Notlar" wire:model="notes"/>
            </div>

            <!-- Ürün Listesi -->
            <div class="col-span-2">
                <x-card title="Ürün Listesi" class="mt-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                            <tr class="bg-gray-50">
                                <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b"></th>
                                <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Ürün Adı</th>
                                <th class="py-2 px-3 text-xs font-medium text-gray-600 border-b">Toplam Satış</th>
                            </tr>
                            </thead>
                            <tbody class="text-sm">


                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b">
                                        <span class="text-blue-500 hover:text-blue-700">
                                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                </td>
                                <td class="py-2 px-3 border-b"></td>
                                <td class="py-2 px-3 border-b"></td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td colspan="3" class="py-2 px-3 border-b">
                                    <table class="w-full text-left">
                                        <thead>
                                        <tr>
                                            <th class="py-1 px-2 text-xs font-medium text-gray-500">Varyant</th>
                                            <th class="py-1 px-2 text-xs font-medium text-gray-500">Satış
                                                Miktarı
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($items as $proposalItem)
                                            <tr>
                                                <td class="py-1 px-2">{{ $proposalItem->product->name }}</td>
                                                <td class="py-1 px-2">{{$proposalItem->qty }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>


            <div class="col-span-2">
                <hr class="my-5"/>

                <livewire:proposal.proposal-repeater :items="$items" :is-edit="true" :currency-text="$currency"/>

            </div>
        </div>

        <x-slot:actions>

            <x-button label="Vazgeç" link="/sales"/>

            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
