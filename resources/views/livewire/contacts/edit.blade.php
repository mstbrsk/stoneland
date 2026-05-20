<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;
    use \Livewire\WithFileUploads;

    public \App\Models\Contact $contact;

    public string $selectedTab = 'general';

    #[Rule('required')]
    public string $code;

    #[Rule('required')]
    public string $name;

    #[Rule('sometimes')]
    public ?string $user_id;

    #[Rule('sometimes')]
    public ?string $group_id = null;

    #[Rule('required')]
    public int $company_type;

    #[Rule(rule: 'required', message: 'Fatura adresini giriniz!!')]
    public ?string $address;

    #[Rule('sometimes')]
    public ?string $second_address;

    #[Rule('sometimes')]
    public ?int $country = null;

    #[Rule('sometimes')]
    public ?int $city_id = null;

    #[Rule('sometimes')]
    public ?int $district = null;

    #[Rule('sometimes')]
    public ?int $tax_administration = null;

    #[Rule(rule: 'required_with:tax_administration', message: 'Vergi dairesine bağlı olarak bu alan zorunludur')]
    public ?string $tax_number;

    #[Rule('sometimes')]
    public ?string $phone;

    #[Rule('sometimes')]
    public ?string $accounting_phone;

    #[Rule('sometimes')]
    public ?string $mobile;

    #[Rule('sometimes')]
    public ?string $email;

    #[Rule('required', message: ' bu alan zorunludur')]
    public ?string $post_code;

    #[Rule('sometimes')]
    public ?string $website;

    #[Rule('sometimes')]
    public ?string $language;

    #[Rule('sometimes')]
    public ?array $tickets = [];

    #[Rule('required')]
    public bool $is_same;

    #[Rule('required')]
    public bool $is_supplier;

    #[Rule('sometimes')]
    public ?string $payment_condition_id;

    #[Rule('sometimes')]
    public ?string $exchange_id;

    #[Rule('sometimes')]
    public ?string $price_list_id;

    #[Rule('sometimes')]
    public ?string $shipping_type_id;

    #[Rule('sometimes')]
    public ?string $pos_campaign_id;

    #[Rule('sometimes')]
    public ?string $financial_condition_id;

    #[Rule('sometimes')]
    public ?string $currency_id;


    #[Rule('sometimes')]
    public ?string $notes;

    #[\Livewire\Attributes\Rule('nullable|max:5000')]
    public $photo = null;

    #[Rule('required')]
    public string $updated_by;

    public array $cities = [];
    public array $districts = [];

    public function mount(): void
    {
        $this->fill($this->contact);

        if ($this->country) {
            $this->updatedCountry($this->country);
        }

        if ($this->city_id) {
            $this->updatedCityId($this->city_id);
        }
    }

    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        if ($data['is_same']) {
            $data['second_address'] = $data['address'];
        }

        $this->contact->update($data);

        if ($this->photo && !str_starts_with($this->photo, '/storage')) {
            $url = $this->photo->store('contacts', 'public');
            $this->contact->update(['photo' => "/storage/$url"]);
        }

        $this->success('Cari bilgileri güncellendi.', redirectTo: '/contacts');
    }

    public function updatedCountry($value): void
    {
        $this->cities = \App\Models\City::where('country_id', $value)->get()->toArray();
    }

    public function updatedCityId($value): void
    {
        $this->districts = \App\Models\District::where('city_id', $value)->get()->toArray();
    }

    public function with(): array
    {
        return [
            'companyTypes' => \App\Enums\CompanyType::listForMaryUI(),
            'ticketList' => \App\Models\Ticket::all(),
            'users' => \App\Models\User::all(),
            'countries' => \App\Models\Country::all(),
            'taxOffices' => \App\Models\TaxOffice::all(),
            'tagList' => \App\Models\Tag::all(),

            'groups' => \App\Models\ContactGroup::all(),
            'countries' => \App\Models\Country::all(),

            'stats' => [
                'sales' => 0,
                'purchases' => 0,
                'proposals' => 0,
                'debits' => 0,
            ],
        ];
    }
}; ?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <x-header title="Cari Kart" separator class="mb-6"/>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Üst Bilgi Alanı - Daha Klasik Tasarım -->
            <div class="p-6 border-b">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <div class="flex-shrink-0">
                            <img src="{{ $contact->photo ?: '/empty-user.jpg' }}" alt="Cari Fotoğrafı"
                                 class="h-20 w-20 rounded-full object-cover border border-gray-200">
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ $contact->name }}</h1>
                            <p class="text-gray-600">Cari Kodu: {{ $contact->code }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50 border-b">
                <x-stat title="Sipariş" :value="$stats['sales']" icon="o-arrow-trending-up"/>
                <x-stat title="Satın Alım" :value="$stats['purchases']" icon="o-arrow-trending-down"/>
                <x-stat title="Teklif" :value="$stats['proposals']" icon="o-envelope"/>
                <x-stat title="Borç" :value="$stats['debits']" icon="o-banknotes"/>
            </div>

            <!-- Ana İçerik -->
            <div class="p-6">
                <!-- Sekme Menüsü -->
                <x-tabs wire:model="selectedTab">
                    <x-tab name="general" label="Genel Bilgiler" icon="o-user">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="md:col-span-2">
                                <x-file label="Fotoğraf" wire:model="photo"
                                        hint="Sadece jpg, jpeg, png, bmp formatları desteklenir (max: 5MB)">
                                    <div class="mt-2 flex items-center">
                    <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                            <img src="{{ $contact->photo ?: '/empty-user.jpg' }}" alt="Mevcut fotoğraf"
                                 class="h-full w-full object-cover">
                    </span>
                                        <button type="button"
                                                class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Fotoğraf Seç
                                        </button>
                                    </div>
                                </x-file>
                            </div>

                            <x-input label="Cari Kodu" wire:model="code" readonly/>
                            <x-input label="Adı" wire:model="name" required/>
                            <x-choices-offline single label="Şirket Türü" :options="$companyTypes" wire:model="company_type" required/>
                            <x-toggle label="Tedarikçi Mi?" wire:model="is_supplier"/>
                            <x-choices-offline single label="Satış Temsilcisi" :options="$users" wire:model="user_id" searchable/>
                            <x-choices-offline single label="Grubu" :options="$groups" wire:model="group_id" searchable/>
                        </div>
                    </x-tab>

                    <x-tab name="contact" label="İletişim Bilgileri" icon="o-phone">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <x-input label="Telefon" wire:model="phone"/>
                            <x-input label="Muhasebe Telefon" wire:model="accounting_phone"/>
                            <x-input label="Mobil" wire:model="mobile"/>
                            <x-input label="E-Posta" wire:model="email" type="email"/>
                            <x-input label="Web Sitesi" wire:model="website"/>
                            <x-input label="Dil" wire:model="language"/>
                        </div>
                    </x-tab>

                    <x-tab name="address" label="Adres Bilgileri" icon="o-map-pin">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class=" col-span-2">
                                <x-checkbox label="Sevkiyat ve Fatura Adresi Aynı" wire:model="is_same"/>
                            </div>

                            <x-choices-offline single label="Ülke" :options="$countries" wire:model.live="country"
                                               searchable/>

                            <x-choices-offline label="Şehir" single :options="$cities" wire:model.live="city_id"
                                               searchable/>

                            <x-choices-offline label="İlçe" single :options="$districts" wire:model="district"
                                               searchable/>

                            <div x-show="!$wire.is_same" x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-90"
                                 x-transition:enter-end="opacity-100 transform scale-100">

                                <x-textarea label="Sevkiyat Adresi" wire:model="second_address"/>

                            </div>

                            <x-input label="Posta Kodu" wire:model="post_code" required/>
                            <x-textarea label="Fatura Adresi" wire:model="address"/>
                        </div>
                    </x-tab>

                    <x-tab name="finance" label="Finansal Bilgiler" icon="o-currency-dollar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <x-choices-offline label="Vergi Dairesi" :options="$taxOffices"
                                               single
                                               wire:model="tax_administration"
                                               searchable required/>
                            <x-input label="Vergi Numarası" wire:model="tax_number" required/>
                        </div>
                    </x-tab>

                    <x-tab name="additional" label="Ek Bilgiler" icon="o-information-circle">
                        <div class="mt-4">
                            <x-choices-offline label="Etiketler" :options="$tagList" wire:model="tickets" multiple
                                               searchable/>
                            <x-textarea rows="5" label="Notlar" wire:model="notes"/>
                        </div>
                    </x-tab>
                </x-tabs>
            </div>

            <!-- Eylem Butonları -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                <x-button label="İptal" link="/contacts" class="btn-secondary"/>
                <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"
                          wire:click="save"/>
            </div>
        </div>
    </div>
</div>
