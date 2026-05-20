<?php

use App\Models\Contact;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use WithFileUploads;

    public string $selectedTab = 'general';

    #[Rule('required')]
    public string $code;

    #[Rule('required')]
    public string $name;

    #[Rule('sometimes')]
    public ?string $user_id=null;

    #[Rule('sometimes')]
    public ?string $group_id = null;

    #[Rule('required')]
    public int $company_type;

    #[Rule('sometimes')]
    public string $address;

    #[Rule('sometimes')]
    public string $second_address;

    #[Rule('sometimes')]
    public ?int $country;

    #[Rule('sometimes')]
    public int $city_id = 0;

    #[Rule('sometimes')]
    public string $district;

    #[Rule('sometimes')]
    public ?int $tax_administration = null;

    #[Rule(rule: 'required_with:tax_administration', message: 'Vergi dairesine bağlı olarak bu alan zorunludur')]
    public string $tax_number;

    #[Rule('sometimes')]
    public string $phone;

    #[Rule('sometimes')]
    public string $accounting_phone;

    #[Rule('sometimes')]
    public string $mobile;

    #[Rule('sometimes')]
    public string $email;

    #[Rule('sometimes')]
    public string $website;

    #[Rule('sometimes')]
    public string $language;

    #[Rule('sometimes')]
    public array $tickets = [];

    #[Rule('required')]
    public bool $is_supplier = false;

    #[Rule('sometimes')]
    public string $payment_condition_id;

    #[Rule('sometimes')]
    public string $exchange_id;

    #[Rule('sometimes')]
    public string $price_list_id;

    #[Rule('sometimes')]
    public string $shipping_type_id;

    #[Rule('sometimes')]
    public string $pos_campaign_id;

    #[Rule('sometimes')]
    public string $financial_condition_id;

    #[Rule('sometimes')]
    public string $currency_id;

    #[Rule('sometimes|mimes:jpg,jpeg,png,gif,bmp')]
    public $photo = '';

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public array $cities = [];
    public array $districts = [];

    //public array $taxOffices = [];

    public \Illuminate\Support\Collection $users;

    public function mount()
    {

        $this->searchUser();

        $this->loadDistricts();

    }





    /*  public function updated($name, $value)
      {

          if ($name === 'country') {
              $this->loadCities();
          }

          if ($name === 'city_id') {
              $this->loadDistricts();
          }
      }*/

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





    public function searchUser(string $value = '')
    {
        $selectedOption = \App\Models\User::where('id', $this->user_id)->get();

        $this->users = \App\Models\User::query()
            ->where('name', 'like', "%$value%")
            ->take(10)
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);
    }

    public function save(): void
    {



        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $this->code = generate_contact_code();

        $data = $this->validate();

        $contact = Contact::create($data);

        if ($contact->second_address) {
            \App\Models\Address::create([
                'contact_id' => $contact->id,
                'is_my_address' => false,
                'name' => 'Sevkiyat Adresi',
                'type' => \App\Enums\Address\AddressType::DELIVERY,
                'address' => $contact->second_address,
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id()
            ]);
        }

        if ($contact->address) {
            \App\Models\Address::create([
                'contact_id' => $contact->id,
                'is_my_address' => false,
                'name' => 'Fatura Adresi',
                'type' => \App\Enums\Address\AddressType::INVOICE,
                'address' => $contact->address,
                'created_by' => auth('web')->id(),
                'updated_by' => auth('web')->id()
            ]);
        }

        if ($this->photo) {
            $url = $this->photo->store('contacts', 'public');
            $contact->update(['photo' => "/storage/$url"]);
        }

        log_action(message: 'Cari oluşturuldu', relationType: 'Contact', relationId: $contact->id);

        $this->success('Cari başarıyla oluşturuldu.', redirectTo: '/contacts');
    }


    public function with(): array
    {

        return [
            'companyTypes' => \App\Enums\CompanyType::listForMaryUI(),
            'ticketList' => \App\Models\Ticket::all(),
            'users' => \App\Models\User::all(),
            'countries' => \App\Models\Country::all(),
            'sehir' => \App\Models\City::all(),
            'taxOffices' => \App\Models\TaxOffice::all(),
            'tagList' => \App\Models\Tag::all(),
            'groups' => \App\Models\ContactGroup::all(),
        ];
    }
};
?>

<style>


    @media (max-width: 768px) {
        .inline-flex > svg.inline {
            display: none !important;

        }
        .inline-flex  {
            font-size: 12px;

        }
    }
</style>


<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-2 sm:p-4 md:p-6">
        <x-header title="Yeni Cari Ekle" separator class="mb-4 md:mb-6"/>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <x-form wire:submit="save">
                <div class="lg:grid lg:grid-cols-5 ">
                    <div class="col-span-1 p-4 lg:p-6 border-r">
                        <x-header title="Cari" subtitle="Cari bilgilerini giriniz" size="text-xl md:text-2xl"/>

                        <x-file label="Görsel" wire:model="photo" hint="Sadece jpg,jpeg,png,bmp">
                            <img src="{{ $photo ? $photo->temporaryUrl() : '/empty-user.jpg' }}" class="h-32 w-32 md:h-40 md:w-40 object-cover rounded-lg mx-auto"/>
                        </x-file>
                    </div>

                    <div class="lg:col-span-4 p-4 lg:p-6">
                        <!-- İstatistikler -->
                        <div class="p-4 md:p-8 bg-base-100 border grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5 mb-4 md:mb-6">
                            <x-stat title="Sipariş"     title="Sales"
                                    description="This month"
                                    value="22.124"

                                    tooltip-bottom="There"
                                    icon="o-arrow-trending-up"/>

                            <x-stat title="Satın Alım"
                                    title="Lost"
                                    description="This month"
                                    value="34"

                                    icon="o-arrow-trending-down"/>

                            <x-stat title="Teklif" value="0" icon="o-envelope"/>
                            <x-stat title="Borç" value="0" icon="o-banknotes"/>
                        </div>

                        <!-- Sekme Menüsü -->
                        <x-tabs wire:model="selectedTab"    label-div-class="grid  gap-6 sm:gap-0 grid-cols-5">
                            <x-tab name="general" label="Genel Bilgiler" icon="o-user" class="border border-dashed ">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                                    <x-radio label="Şirket Türü"   :options="$companyTypes" wire:model="company_type" required/>
                                    <x-input label="Adı" wire:model="name"  required/>
                                    <x-toggle label="Tedarikçi Mi?" wire:model="is_supplier"/>
                                    <x-choices search-function="searchUser" label="Satış Temsilcisi" style="height: 45px"
                                               wire:model="user_id" :options="$users" searchable single/>
                                    <x-choices-offline style="height: 45px" single searchable
                                                       :options="$groups"
                                                       label="Grubu"
                                                       wire:model="group_id"/>
                                </div>
                            </x-tab>

                            <x-tab name="contact" label="İletişim Bilgileri" icon="o-phone">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                                    <x-input label="Telefon" wire:model="phone"/>
                                    <x-input label="Muhasebe Telefon" wire:model="accounting_phone"/>
                                    <x-input label="Mobil" wire:model="mobile"/>
                                    <x-input label="E-Posta" wire:model="email"/>
                                    <x-input label="Web Sitesi" wire:model="website"/>
                                    <x-input label="Dil" wire:model="language"/>
                                </div>
                            </x-tab>

                            <x-tab name="address" label="Adres Bilgileri" icon="o-map-pin">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                                    <x-select style="height: 45px" single searchable
                                              :options="$countries"
                                              label="Ülke"
                                              wire:model.live="country"/>




                                    <x-select style="height: 45px" single searchable
                                              :options="$sehir"
                                              placeholder="Seçiniz"
                                              label="Şehir"
                                              wire:model.live="city_id"
                                    />

                                    <x-select searchable style="height: 45px" single label="İlçe" wire:model.live="district"
                                              :options="$districts"/>

                                    <x-textarea label="Sevkiyat Adresi" wire:model="second_address"/>
                                    <x-textarea label="Fatura Adresi" wire:model="address"/>
                                </div>
                            </x-tab>

                            <x-tab name="financial" label="Finansal Bilgiler" icon="o-currency-dollar">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                                    <x-choices-offline searchable style="height: 45px" single label="Vergi Dairesi"
                                                       no-result-text="Öğe bulunamadı"
                                                       wire:model="tax_administration"
                                                       :options="$taxOffices"/>
                                    <x-input label="Vergi Numarası" wire:model="tax_number"/>
                                </div>
                            </x-tab>

                            <x-tab name="additional" label="Ek Bilgiler" icon="o-information-circle">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                                    <x-choices label="Etiketler" wire:model="tickets" :options="$tagList" style="height: 45px"/>
                                </div>
                            </x-tab>
                        </x-tabs>
                    </div>
                </div>

                <!-- Eylem Butonları -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                    <x-button label="İptal" link="/contacts"/>
                    <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
                </div>
            </x-form>
        </div>




    </div>
</div>
