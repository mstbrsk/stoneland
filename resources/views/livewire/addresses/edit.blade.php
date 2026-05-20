<?php


use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Contact;

new class extends Component {
    use Toast;

    public \App\Models\Address $addressModel;

    #[Rule('required')]
    public bool $is_my_address;

    #[Rule('required', message: 'Zorunlu alan')]
    public string $name;

    #[Rule('required', message: 'Zorunlu alan')]
    public string $address;

    #[Rule('sometimes')]
    public string|null $contact_id = null;

    #[Rule('required', message: 'Zorunlu alan')]
    public ?string $contact_name;

    #[Rule('sometimes')]
    public ?string $mobile;


    #[Rule('required', message: 'Zorunlu alan')]
    public ?int $type = null;

    #[Rule('required', message: 'Zorunlu alan')]
    public int $country = 1;

    #[Rule('sometimes')]
    public int $city_id = 0;

    #[Rule('sometimes')]
    public string $district;

    public array $cities = [];
    public array $districts = [];

    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {

        $this->fill($this->addressModel);
        $this->loadCities();

        $this->loadDistricts();


    }

    public function updated($city_id)
    {
        $this->loadDistricts();
    }
    public function loadCities()
    {
        $this->cities = \App\Models\City::where('country_id', '1')->get()->toArray();
    }

    public function loadDistricts()
    {
        $this->districts = \App\Models\District::where('city_id', $this->city_id)->get()->toArray();
    }


    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();




       if($data['is_my_address']){
           $data['contact_id'] = null;
       }

        $this->addressModel->update($data);

        $this->success('Adres başarıyla güncellendi.', redirectTo: '/addresses');
    }

    public function with(): array
    {
        return [
            'countries' => \App\Models\Country::all(),
            'cities' => \App\Models\City::all(),
            'contacts' => Contact::all(),
            'types' => \App\Enums\Address\AddressType::listForMaryUI(),

        ];
    }


}; ?>

<div class="min-h-screen bg-gray-100 flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Düzenle</h1>
        </div>
    </header>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-form wire:submit="save" class="p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-8 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-header title="Adres" subtitle="Adres bilgilerini giriniz" size="text-2xl"/>
                        </div>

                        <div class="space-y-6">
                            <x-toggle label="Berka'ya ait bir adres mi?" wire:model="is_my_address"/>

                            <div x-show="!$wire.is_my_address">
                                <x-choices label="Firma" wire:model="contact_id" :options="$contacts" single class="w-full"/>
                            </div>

                            <x-select searchable :options="$countries" label="Ülke" wire:model.live="country" class="w-full"/>

                            <x-select searchable label="Şehir" wire:model.live="city_id" :options="$cities" class="w-full"/>

                            <x-select searchable label="İlçe" wire:model.live="district" :options="$districts" class="w-full"/>
                        </div>

                        <div class="space-y-6">
                            <x-input label="İlgili Kişi" wire:model="contact_name" required class="w-full"/>


                            <x-input label="Cep Telefonu" wire:model="mobile" class="w-full" />

                            <x-input label="Adres Adı" wire:model="name" required class="w-full"/>

                            <x-textarea label="Açık Adres" wire:model="address" rows="5" required class="w-full"/>

                            <x-select placeholder="Seçiniz" label="Adres Tipi" wire:model="type" :options="$types" single required class="w-full"/>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <x-button label="İptal" link="/warehouses" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"/>
                        <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="bg-blue-600 text-white hover:bg-blue-700"/>
                    </div>
                </x-form>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">&copy; 2024 Şirketiniz. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</div>
