<?php

use App\Models\Address;
use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use WithFileUploads;

    #[Rule('required')]
    public string $proposal_no = '';

    #[Rule('required')]
    public string $contact_name = '';

    #[Rule('required')]
    public string $contacted_person = '';

    #[Rule('required')]
    public string $contacted_at = '';

    #[Rule('required')]
    public string $notes = '';

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public function mount()
    {
        $this->fill([
            'proposal_no' => generate_proposal_no(),
        ]);
    }

    public function save(): void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $data = $this->validate();

        $id = \Illuminate\Support\Str::uuid();

        $data['relation_id'] = $id;

        $crmLead = \App\Models\CrmLead::create($data);

        log_action(message: 'Fırsat oluşturuldu', relationType: 'CrmLead', relationId: $crmLead->id);

        $this->success('Fısat oluşturuldu.', redirectTo: '/crm/leads');

        $this->dispatch('crm-lead-created');
    }

    public function with(): array
    {
        return [
            //
        ];
    }
};
?>

<div class="bg-gradient-to-r from-blue-100 to-indigo-100 p-8 rounded-lg shadow-lg">
    <x-header title="Yeni Fırsat Oluştur" class="text-3xl font-bold text-indigo-800 mb-6" separator/>

    <x-form wire:submit="save" class="space-y-6">
        <div class="lg:grid lg:grid-cols-5 lg:gap-8">
            <div class="col-span-2">
                <x-header title="Fırsat Detayları" subtitle="Lütfen aşağıdaki bilgileri eksiksiz doldurunuz"
                          size="text-xl" class="text-indigo-700"/>
                <p class="mt-2 text-sm text-gray-600">Tüm alanlar zorunludur ve dikkatle doldurulmalıdır.</p>
            </div>

            <div class="col-span-3 grid gap-5">
                <x-input label="Teklif No" wire:model="proposal_no" icon="o-document-text" readonly required
                         class="bg-gray-100"/>
                <x-input label="Müşteri Adı" wire:model="contact_name" icon="o-user-circle" required
                         placeholder="Örn: ABC Şirketi"/>
                <x-input label="Görüşülen Kişi" wire:model="contacted_person" icon="o-user" required
                         placeholder="Örn: Ahmet Yılmaz"/>
                <x-input label="Görüşme Tarihi" wire:model="contacted_at" icon="o-calendar" type="datetime-local"
                         required/>
                <x-textarea label="Görüşme Notları" wire:model="notes" rows="6" required
                            placeholder="Görüşme detaylarını ve önemli noktaları buraya yazınız..."/>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-4">
                <x-button label="İptal" link="/crm/leads" class="btn-outline"/>
                <x-button label="Fırsatı Kaydet" icon="o-rocket-launch" spinner="save" type="submit"
                          class="btn-primary"/>
            </div>
        </x-slot:actions>
    </x-form>
</div>
