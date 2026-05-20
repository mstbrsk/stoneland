<?php

use App\Models\Sample;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;
    use WithFileUploads;
    use Mary\Traits\WithMediaSync;

    public Sample $sample;

    #[Rule('sometimes')]
    public string|null $contact_id = null;

    #[Rule('required')]
    public string $warehouse_id = '';

    #[Rule('sometimes')]
    public ?string $contact_name;

    #[Rule('required')]
    public string $invoice_no;

    #[Rule('required')]
    public bool $has_contact = false;

    #[Rule('sometimes')]
    public ?\Illuminate\Support\Collection $library;

    #[Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[Rule('required')]
    public ?string $updated_by;

    public ?array $selectedProducts = null;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showShippingModal = false;
    public bool $showMessageLogsDrawer = false;
    public bool $showHistoryDrawer = false;

    public ?array $returnedItems = [];

    public int $totalQty = 0;
    public int $returnedTotalQty = 0;

    #[\Livewire\Attributes\On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    public function mount(): void
    {
        $sample = Sample::firstWhere('id', $this->sample->id);
        $sample->makeHidden('library');
        $this->fill($sample);

        $this->contact_id = $sample->contact_id;

        $this->library = $this->sample->library ?? collect();

        $this->selectedProducts = $sample->data;

        $this->totalQty = collect($sample->data)->sum('qty');

        $this->returnedTotalQty = collect($sample->return_data)->where('returned', true)->count();

        foreach ($sample->return_data ?? [] as $item) {
            $this->returnedItems[$item['variant_id']][$item['order']] = $item;
        }
    }

    public function save(): void
    {
        $hasValidQty = collect($this->selectedProducts)->every(fn(array $array) => $array['qty'] > 0);

        if (!$hasValidQty) {
            throw \Mary\Exceptions\ToastException::error('Numune miktarı belirtilmedi!');
        }

        $me = auth('web')->id();
        $status = $this->sample->status;

        if ($status->shipped() || $status->someOfReturned()) {
            $this->handleReturnedSamples();
        } else {
            $this->updateSampleDetails($me);
        }

        $this->syncMedia(model: $this->sample, files: 'images', storage_subpath: 'samples');
        $this->success('Numune bilgisi güncellendi', redirectTo: '/samples');
    }

    private function handleReturnedSamples(): void
    {
        $dataList = $this->prepareReturnedSamplesData();
        $status = $this->determineNewStatus($dataList);

        $this->sample->update([
            'return_data' => $dataList,
            'status' => $status,
        ]);

        log_action(message: 'Numune geri alım listesi güncellendi', relationType: 'Sample', relationId: $this->sample->id);

        $this->success('Numune geri alım listesi güncellendi', redirectTo: '/samples');
    }

    private function prepareReturnedSamplesData(): array
    {
        $dataList = [];

        foreach ($this->selectedProducts as $product) {
            foreach ($product['variants'] as $variantId => $qty) {
                foreach (range(0, $qty - 1) as $order) {
                    $detail = $this->returnedItems[$variantId][$order] ?? [];

                    $returned = $detail['returned'] ?? false;
                    $returnedAt = $detail['returned_at'] ?? '';
                    $note = $detail['note'] ?? '';

                    $this->validateReturnData($returned, $returnedAt);

                    $dataList[] = [
                        'variant_id' => $variantId,
                        'returned' => $returned,
                        'order' => $order,
                        'returned_at' => $returnedAt,
                        'note' => $note,
                    ];
                }
            }
        }

        return $dataList;
    }

    private function validateReturnData(bool $returned, string $returnedAt): void
    {
        if ($returned && empty($returnedAt)) {
            throw \Mary\Exceptions\ToastException::error('Geri alış tarihi belirtilmedi!');
        }

        if (!$returned && !empty($returnedAt)) {
            throw \Mary\Exceptions\ToastException::error('Geri aldığınızı belirtin!');
        }
    }

    private function determineNewStatus(array $dataList): \App\Enums\Sample\SampleStatus
    {
        $allReturned = collect($dataList)->every(fn(array $array) => $array['returned']);
        $someReturned = collect($dataList)->contains(fn(array $array) => $array['returned']);

        if ($allReturned) {
            return \App\Enums\Sample\SampleStatus::ALL_RETURNED;
        } elseif ($someReturned) {
            return \App\Enums\Sample\SampleStatus::SOME_OF_RETURNED;
        } else {
            return \App\Enums\Sample\SampleStatus::SHIPPED;
        }
    }

    private function updateSampleDetails(string $userId): void
    {
        $this->sample->update([
            'has_contact' => $this->has_contact,
            'contact_id' => $this->contact_id,
            'warehouse_id' => $this->warehouse_id,
            'contact_name' => $this->contact_name,
            'invoice_no' => $this->invoice_no,
            'library' => $this->library,
            'updated_by' => $userId,
            'data' => $this->selectedProducts,
        ]);

        log_action(message: 'Numune listesi düzenlendi', relationType: 'Sample', relationId: $this->sample->id);
    }

    public function approve()
    {
        $this->sample->update([
            'status' => \App\Enums\Sample\SampleStatus::APPROVED,
        ]);

        log_action(message: 'Numune listesi onaylandı', relationType: 'Sample', relationId: $this->sample->id);

        $this->success('Numune listesi onaylandı', redirectTo: '/samples');
    }

    public function reject()
    {
        $this->sample->update([
            'status' => \App\Enums\Sample\SampleStatus::REJECTED,
        ]);

        log_action(message: 'Numune listesi reddedildi', relationType: 'Sample', relationId: $this->sample->id);

        $this->success('Numune listesi reddedildi', redirectTo: '/samples');
    }

    public function ship()
    {
        foreach ($this->selectedProducts as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);

            foreach ($item['variants'] as $variantId => $qty) {
                \App\Models\ProductTransaction::create([
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'quantity' => $qty,
                    'type' => \App\Enums\StockProcessType::OUT,
                    'relation_type' => \App\Enums\ProductStock\RelationType::SAMPLE,
                    'relation_id' => $this->sample->id,
                    'created_by' => auth('web')->id(),
                    'warehouse_id' => $product->warehouse_id,
                    'notes' => "{$this->sample->getContactName()} firmasına numune gönderimi"
                ]);

                $toInventory = \App\Models\Inventory::create([
                    'warehouse_id' => $product->warehouse_id,
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'created_by' => auth('web')->id(),
                    'updated_by' => auth('web')->id(),
                ]);

                $toInventory->decrement('quantity', $qty);
            }
        }

        $this->sample->update([
            'status' => \App\Enums\Sample\SampleStatus::SHIPPED,
            'shipped_at' => now(),
            'shipped_by' => auth('web')->id(),
        ]);

        log_action(message: 'Numuneler gönderildi olarak düzenlendi', relationType: 'Sample', relationId: $this->sample->id);

        $this->success('Numuneler gönderildi olarak düzenlendi', redirectTo: "/samples/{$this->sample->id}/edit");
    }

    public function with(): array
    {
        return [
            'warehouses' => \App\Models\Warehouse::all(),
            'contacts' => \App\Models\Contact::all(),
        ];
    }
}; ?>

<div class="bg-gray-100 min-h-screen">
    <livewire:action-log key="{{ Str::random() }}" relation-type="Sample" :relation-id="$sample->id"
                         :show-history-drawer="$showHistoryDrawer"/>
    <livewire:message-log id="messageHistoryModal" redirect-to='{{ "/samples/{$sample->id}/edit" }}'
                          relation-type="Sample" :relation-id="$sample->id"/>

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-blue-600">Monte</span>
                    <span class="ml-4 text-xl text-gray-600">Numune Yönetimi</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                    <span class="text-sm text-gray-600">{{ now()->format('d.m.Y') }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Page Header -->
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <div class="flex justify-between items-center flex-wrap sm:flex-nowrap">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-3" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Numune Düzenleme</h1>
                            <p class="mt-1 text-sm text-gray-600"><span
                                    class="font-semibold">{{ $sample->sample_no }}</span>
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mt-4 sm:mt-0">
                        {!! $sample->status->textWithBadge() !!}
                        <x-button icon="o-clock" wire:click.prevent="$set('showHistoryDrawer',true)"
                                  class="btn-outline"/>
                        <x-button icon="o-envelope" @click="messageHistoryModal.showModal()"
                                  class="btn-outline"/>
                        @if ($sample->status->pending())
                            <x-button icon="o-check" wire:click.prevent="$set('showApproveModal', true)"
                                      class="btn-success">Onayla
                            </x-button>
                            <x-button icon="o-x-mark" wire:click.prevent="$set('showRejectModal', true)"
                                      class="btn-error">Reddet
                            </x-button>
                        @endif
                        @if ($sample->status->isApproved())
                            <x-button icon="o-paper-airplane" wire:click="$set('showShippingModal', true)"
                                      class="btn-info">Gönder
                            </x-button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="px-4 py-5 sm:p-6">
                @if ($sample->status->isApproved())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6" role="alert">
                        <p class="font-bold">Onaylandı</p>
                        <p>Numune gönderimi {{ $sample->updated_at->format('d.m.Y H:i') }} tarihinde onaylandı.</p>
                        <p class="mt-2">Değişiklik yapabilmeniz için gönder butonuna basmalısınız.</p>
                    </div>
                @endif

                <x-form wire:submit="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Müşteri Bilgileri -->
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Müşteri Bilgileri</h2>
                            @if ($sample->status->pending())
                                <x-toggle lg wire:model="has_contact" label="Mevcut müşteri"/>
                                <div x-show="$wire.has_contact" class="mt-4">
                                    <x-select label="Müşteri" wire:model.live="contact_id" :options="$contacts"/>
                                </div>
                                <div x-show="!$wire.has_contact" class="mt-4">
                                    <x-input label="Müşteri Adı" wire:model="contact_name"
                                             placeholder="Müşteri adını girin"/>
                                </div>
                            @else
                                <p class="text-gray-700"><strong>Müşteri:</strong>
                                    {{ $has_contact ? $contacts->firstWhere('id', $contact_id)->name ?? 'Seçilmemiş' : $contact_name }}
                                </p>
                                <p class="text-gray-700 mt-2"><strong>Gönderim Tarihi:</strong>
                                    {{ $sample->status->shipped() ? $sample->shipped_at->format('d-m-Y') : 'Henüz gönderilmedi' }}
                                </p>
                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <x-stat title="Gelen" value="{{ $returnedTotalQty }}" icon="o-arrow-down-tray"/>
                                    <x-stat title="Giden" value="{{ $totalQty }}" icon="o-clipboard-document-list"/>
                                </div>
                            @endif
                        </div>

                        <!-- Depo ve İrsaliye Bilgileri -->
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Depo ve İrsaliye Bilgileri</h2>
                            @if ($sample->status->pending())
                                <x-select label="Depo" wire:model="warehouse_id" :options="$warehouses" class="mb-4"/>
                                <x-input label="İrsaliye No" wire:model="invoice_no"
                                         placeholder="İrsaliye numarasını girin" required/>
                            @else
                                <p class="text-gray-700"><strong>Depo:</strong>
                                    {{ $warehouses->firstWhere('id', $warehouse_id)->name ?? 'Seçilmemiş' }}
                                </p>
                                <p class="text-gray-700 mt-2"><strong>İrsaliye No:</strong> {{ $invoice_no }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($sample->status->pending())
                        <!-- Fotoğraf Yükleme -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Fotoğraf Yükleme</h2>
                            <x-image-library wire:model="images" multiple label="Fotoğraflar" help="Maksimum 3MB"/>
                        </div>

                        <!-- Numune Detayları -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Numune Detayları</h2>
                            <livewire:sample.sample-repeater :is-edit="true" :items="$selectedProducts"/>
                        </div>
                    @endif

                    @if ($sample->status->shipped() || $sample->status->someOfReturned() || $sample->status->allReturned() || $sample->status->isApproved())
                        <!-- Gönderilen Ürünler ve Dönüş Durumu -->
                        <div class="mt-8 bg-white p-6 rounded-xl shadow-lg" x-data="xSample">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Gönderilen Ürünler ve Dönüş Durumu</h2>
                            @foreach($selectedProducts as $product)
                                <div class="mb-8 bg-gray-50 rounded-lg p-4">
                                    <h3 class="text-xl font-semibold text-blue-600 mb-4">{{ \App\Models\Product::find($product['product_id'])->name }}</h3>
                                    <div class="overflow-x-auto">
                                        <table class="w-full border-collapse"
                                               x-bind:class="{ 'opacity-50 pointer-events-none': {{ $sample->status->shipped() || $sample->status->someOfReturned() ? 'false' : 'true' }} }">
                                            <thead>
                                            <tr class="bg-gray-100">
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Geri Geldi Mi?</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Geri Alış Tarihi</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Not</th>
                                            </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($product['variants'] as $variantId => $quantity)
                                                @foreach(range(0,$quantity-1) as $q)
                                                    <tr
                                                        @if ($returnedItems[$variantId][$q]['returned'] ?? false)
                                                            class="bg-green-50 transition duration-300"
                                                        @endif
                                                        x-bind:class="{ 'opacity-50 pointer-events-none': {{ $returnedItems[$variantId][$q]['returned'] ?? '' ? 'true' : 'false' }} }"
                                                    >
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {{ $q+1 . '. ' . \App\Models\ProductVariant::find($variantId)->getVariantName() }}
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            <x-toggle
                                                                x-on:change="updateStatus('{{ $variantId }}')"
                                                                wire:model="returnedItems.{{ $variantId }}.{{ $q }}.returned"
                                                                class="w-11 h-6"
                                                            />
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            <x-input
                                                                type="date"
                                                                wire:model="returnedItems.{{ $variantId }}.{{ $q }}.returned_at"
                                                                class="border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                                            />
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            <x-input
                                                                wire:model="returnedItems.{{ $variantId }}.{{ $q }}.note"
                                                                class="border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                                                placeholder="Not ekleyin..."
                                                            />
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="my-5"></div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <x-button
                            label="İptal"
                            link="/sample"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800"
                        />

                        <x-button
                            label="Kaydet"
                            icon="o-paper-airplane"
                            wire:click="save"
                            spinner
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white"
                        />
                    </div>
                </x-form>
            </div>
        </div>
    </main>

    <!-- Modals -->
    <x-modal wire:model="showApproveModal" title="Numune Onaylama">
        <p>Bu numune gönderimi onaylanacak. Emin misiniz?</p>
        <x-slot:actions>
            <x-button flat label="İptal" x-on:click="close"/>
            <x-button primary label="Onayla" wire:click="approve"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showRejectModal" title="Numune Reddetme">
        <p>Bu numune gönderimi reddedilecek. Emin misiniz?</p>
        <x-slot:actions>
            <x-button flat label="İptal" x-on:click="close"/>
            <x-button negative label="Reddet" wire:click="reject"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showShippingModal" title="Numune Gönderimi">
        <p>Bu numuneler gönderildi olarak düzenlenip stoktan düşülecek. Onaylıyor musunuz?</p>
        <x-slot:actions>
            <x-button flat label="İptal" x-on:click="close"/>
            <x-button primary label="Onayla" wire:click="ship"/>
        </x-slot:actions>
    </x-modal>
</div>

@script
<script>
    Alpine.data('xSample', () => ({
        updateStatus: function (variantId) {
            const status = $wire.returnedItems[variantId].returned;

            if (!status) {
                $wire.returnedItems[variantId].returned_at = ''
            }
        }
    }));
</script>
@endscript
