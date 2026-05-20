<?php

use App\Models\Sample;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;


new class extends Component {

    use Toast;
    use WithFileUploads;
    use Mary\Traits\WithMediaSync;

    #[Rule('sometimes')]
    public ?string $contact_id = null;

    #[Rule('required')]
    public string $warehouse_id = '';

    #[Rule('sometimes')]
    public ?string $contact_name = null;

    #[Rule('required')]
    public string $invoice_no;

    #[Rule('required')]
    public bool $has_contact = false;

    #[Rule('sometimes')]
    public \Illuminate\Support\Collection $library;

    #[Rule(['images.*' => 'image|max:1024'])]
    public array $images = [];

    #[Rule('sometimes')]
    public ?array $selectedProducts = null;

    #[On('raise-selected-products')]
    public function setSelectedProducts(?array $products = null)
    {
        $this->selectedProducts = $products;
    }

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;


    public function mount()
    {
        $this->fill([
            'library' => collect(),
        ]);
    }


    public function save(): void
    {
        $hasValidQty = collect($this->selectedProducts)->every(fn(array $array) => $array['qty'] > 0);

        if (!$hasValidQty) {
            throw \Mary\Exceptions\ToastException::error('Numune miktarı belirtilmedi!');
        }

        $me = auth('web')->id();

        $sample = Sample::create([
            'sample_no' => generate_sample_no(),
            'has_contact' => $this->has_contact,
            'contact_id' => $this->contact_id,
            'warehouse_id' => $this->warehouse_id,
            'contact_name' => $this->contact_name,
            'invoice_no' => $this->invoice_no,
            'library' => $this->library,
            'created_by' => $me,
            'updated_by' => $me,
            'data' => $this->selectedProducts,
        ]);

        log_action(message: 'Numune listesi oluşturuldu', relationType: 'Sample', relationId: $sample->id);

        $this->success('Numune listesi oluşturuldu', redirectTo: '/samples');

        $this->syncMedia(model: $sample, files: 'images', storage_subpath: 'samples');

        $this->success('Numune oluşturuldu', redirectTo: '/samples');
    }


    public function with(): array
    {
        return [
            'warehouses' => \App\Models\Warehouse::all(),

            'contacts' => \App\Models\Contact::all(),

            'productList' => \App\Models\Product::all()->map(fn(\App\Models\Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
            ])->toArray(),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-lg">
    <x-header title="Numuneler" class="text-3xl font-bold mb-6 text-gray-800"/>

    <x-form wire:submit="save" class="space-y-8">
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Özellikleri</h2>

                <div class="mb-4">
                    <x-toggle wire:model="has_contact" label="Mevcut müşteri" class="mb-4"/>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div x-show="$wire.has_contact" x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100">
                        <x-choices-offline
                            searchable
                            single
                            label="Müşteri"
                            wire:model.live="contact_id"
                            :options="$contacts"
                            class="w-full"
                            style="height: 44px"
                        />
                    </div>

                    <div x-show="!$wire.has_contact" x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100">
                        <x-input
                            label="Müşteri"
                            wire:model="contact_name"
                            icon="o-user"
                            placeholder="Müşteri adını girin"
                        />
                    </div>

                    <x-choices-offline
                        label="Depo"
                        single
                        searchable
                        :options="$warehouses"
                        wire:model="warehouse_id"
                        class="w-full"
                        style="height: 44px"
                    />
                </div>
            </div>

            <x-input
                label="İrsaliye No"
                wire:model="invoice_no"
                required
                placeholder="İrsaliye numarasını girin"
                class="w-full md:w-1/2"
            />

            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Fotoğraf Yükleme</h3>
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
                    hint="Maks. 3MB"
                    class="w-full"
                />
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Numune Detayları</h3>
            <livewire:sample.sample-repeater/>
        </div>

        <div class="flex justify-end space-x-4 mt-8">
            <x-button
                label="İptal"
                link="/sample"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800"
            />

            <x-button
                label="Kaydet"
                icon="o-paper-airplane"
                spinner="save"
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white"
            />
        </div>
    </x-form>
</div>
