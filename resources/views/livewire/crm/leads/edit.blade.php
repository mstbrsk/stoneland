<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;

    public \App\Models\CrmLead $lead;
    public ?\Illuminate\Support\Collection $leads = null;

    #[Rule('sometimes')]
    public string $proposal_no = '';

    #[Rule('sometimes')]
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

    public function mount(): void
    {
        $this->fill([
            'proposal_no' => $this->lead->proposal_no,
            'contact_name' => $this->lead->contact_name,
            'leads' => collect(),
        ]);

        $this->leads = \App\Models\CrmLead::where('relation_id', $this->lead->relation_id)->latest()->get();
    }

    public function save(): void
    {
        \App\Models\CrmLead::create([
            'relation_id' => $this->lead->relation_id,
            'contacted_person' => $this->contacted_person,
            'contact_name' => $this->lead->contact_name,
            'notes' => $this->notes,
            'contacted_at' => $this->contacted_at,
            'created_by' => auth('web')->id(),
            'updated_by' => auth('web')->id(),
        ]);

        $this->success('Fırsat başarıyla güncellendi.', redirectTo: '/crm/leads');
    }

    public function with(): array
    {
        return [
            //
        ];
    }

}; ?>

<div class="bg-gradient-to-r from-blue-100 to-indigo-100 p-8 rounded-lg shadow-lg">
    <x-header title="Fırsat Detayları" class="text-3xl font-bold text-indigo-800 mb-6" separator/>

    <x-form wire:submit="save" class="space-y-6">
        <div class="lg:grid lg:grid-cols-5 lg:gap-8">
            <div class="col-span-2">
                <x-header title="Fırsat Bilgileri" subtitle="Fırsat detaylarını inceleyiniz ve güncelleyiniz"
                          size="text-xl" class="text-indigo-700"/>
                <p class="mt-2 text-sm text-gray-600">Tüm alanlar zorunludur ve dikkatle doldurulmalıdır.</p>
            </div>

            <div class="col-span-3 grid gap-5">
                <x-input label="Teklif No" wire:model="proposal_no" icon="o-paper-clip" readonly required
                         class="bg-gray-100"/>
                <x-input label="Müşteri Adı" wire:model="contact_name" icon="o-home-modern" readonly required
                         class="bg-gray-100"/>

                <x-hr/>

                <x-input label="Görüşülen Kişi" wire:model="contacted_person" icon="o-user" required
                         placeholder="Örn: Ahmet Yılmaz"/>
                <x-input label="Görüşme Tarihi" wire:model="contacted_at" icon="o-calendar" type="datetime-local"
                         required/>
                <x-textarea label="Görüşme Notları" wire:model="notes" rows="6" required
                            placeholder="Görüşme detaylarını ve önemli noktaları buraya yazınız..."/>

                <x-card title="" subtitle="En son yazılan mesajlar" shadow separator>
                    @foreach($leads as $key => $lead)
                        <x-timeline-item :title="'Görüşen: ' . $lead->createdBy?->name . ' - Görüşülen: ' . $lead->contacted_person"
                                         icon="o-paper-airplane"
                                         subtitle="{{ $lead->contacted_at->format('d.m.Y H:i') }}"
                                         description="{{ $lead->notes }}"
                                         :first="$key==0"/>
                    @endforeach
                </x-card>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-4">
                <x-button label="İptal" link="/crm/leads" class="btn-outline"/>
                <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
            </div>
        </x-slot:actions>
    </x-form>
</div>
