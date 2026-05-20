<?php

use App\Enums\Address\AddressType;
use App\Models\Address;
use App\Models\Contact;
use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use WithFileUploads;

    #[Rule('required')]
    public bool $is_my_address = false;

    #[Rule('required', message: 'Zorunlu alan')]
    public string $name;

    #[Rule('sometimes')]
    public string|null $contact_id = null;



    #[Rule('required', message: 'Zorunlu alan')]
    public ?int $type = null;

    #[Rule('required', message: 'Zorunlu alan')]
    public string $address;

    #[Rule('required', message: 'Zorunlu alan')]
    public int $country = 1;

    #[Rule('sometimes')]
    public int $city_id = 0;

    #[Rule('sometimes')]
    public string $district;

    #[Rule('required', message: 'Zorunlu alan')]
    public string $contact_name;

    #[Rule('sometimes')]
    public string $mobile;


    public array $cities = [];
    public array $districts = [];

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;



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


        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();



       Address::create($data);


        $this->success('Adres başarıyla oluşturuldu.', redirectTo: '/addresses');
    }



    public function with(): array
    {

        return [
            'countries' => \App\Models\Country::all(),
            'sehir' => \App\Models\City::all(),
            //'cities' => \App\Models\City::all(),

            'types' => AddressType::listForMaryUI(),
            'contacts' => Contact::all(),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-100 flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Yeni Adres Ekle</h1>
        </div>
    </header>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form wire:submit="save" class="p-6 space-y-8">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Berka'ya ait bir adres mi?</span>
                                <x-toggle wire:model="is_my_address" />
                            </div>
                        </div>

                        <div  x-show="!$wire.is_my_address" class="sm:col-span-6">
                            <x-choices label="Firma" wire:model="contact_id" :options="$contacts" single class="w-full"  />
                        </div>



                        <div class="sm:col-span-3">
                            <x-select label="Ülke" wire:model.live="country" :options="$countries" searchable class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-select label="Şehir" wire:model.live="city_id" :options="$sehir" searchable placeholder="Seçiniz" class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-select label="İlçe" wire:model.live="district" :options="$districts" searchable class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input label="İlgili Kişi" wire:model="contact_name" required class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input label="Cep Telefonu" wire:model="mobile" class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-input label="Adres Adı" wire:model="name" required class="w-full" />
                        </div>

                        <div class="sm:col-span-6">
                            <x-textarea label="Açık Adres" wire:model="address" rows="4" required class="w-full" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-select label="Adres Tipi" wire:model="type" :options="$types" placeholder="Seçiniz" required class="w-full" />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button label="İptal" link="/warehouses" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50" />
                        <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="bg-blue-600 text-white hover:bg-blue-700" />
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">&copy; 2024 Şirketiniz. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</div>
