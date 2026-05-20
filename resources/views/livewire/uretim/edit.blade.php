<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public \App\Models\Warehouse $warehouse;

    public \App\Enums\Proposal\ProposalStatus $statusAsEnum;



    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $short_name = '';


    #[Rule('required')]
    public string $updated_by;

    public function mount(): void
    {
        $this->fill($this->warehouse);
    }




    public function save(): void
    {
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $attribute = $this->warehouse->update($data);

        $this->success('Depolar  güncdellendi.', redirectTo: '/uretim');


    }



}; ?>

<div>
    <x-header title="Personal Günceller" subtitle="Your home address" separator >
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

                        <x-menu-item title="Sil" icon="o-trash" @click="$wire.showDeleteModal = true"/>
                    @endif

                    @if ($statusAsEnum===\App\Enums\Proposal\ProposalStatus::APPROVED)
                        <x-menu-item title="Gönder" icon="o-envelope" @click="$wire.showSentModal = true"/>
                    @endif
                    <x-menu-separator/>

                    <x-menu-item title="İşlem Geçmişi" icon="o-film" wire:click="$set('showHistoryDrawer',true)"/>

                    <x-menu-item title="Mesaj Geçmişi" icon="o-envelope" @click="messageHistoryModal.showModal()"/>
                </x-dropdown>
            </div>
        </x-slot:actions>

    </x-header>
    <x-form wire:submit="save">

        <div class="lg:grid grid-cols-5 ">
            <div class="col-span-1">
                <x-header title="Üretim" subtitle="Üretim bilgilerini giriniz" size="text-2xl"/>

            </div>



            <div class="col-span-3 grid gap-4">
                <x-input label="Depo Adı" wire:model="name" class="block w-full p-4 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
                <x-input label="Kısa Ad" wire:model="short_name" class="block w-full p-4 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel"  link="/uretim" />
            <x-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>

</div>
