<?php


use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;

    #[\Livewire\Attributes\Rule('required')]
    public string $name = '';

    #[\Livewire\Attributes\Rule('required|email|unique:users',
        message: ['unique' => 'Bu e-posta kullanılıyor'])
    ]
    public string $email = '';

    #[\Livewire\Attributes\Rule('required|min:6|max:20|confirmed',
        message: [
            'min' => 'Parola en az 6 karakter olabilir',
            'max' => 'Parola en fazla 20 karakter olabilir',
            'confirmed' => 'Parolalar uyuşmuyor'
        ])
    ]
    public string $password = '';

    #[\Livewire\Attributes\Rule('required|min:6|max:20',
        message: [
            'min' => 'Parola en az 6 karakter olabilir',
            'max' => 'Parola en fazla 20 karakter olabilir',
        ])
    ]
    public string $password_confirmation = '';

    public bool $is_admin = false;

    public function save(): void
    {
        $data = $this->validate();

        $data['avatar'] = '/empty-user.jpg';
        $data['password'] = Hash::make($data['password']);
        $data['is_admin'] = $this->is_admin;

        $user = \App\Models\User::create($data);

        log_action(message: 'Kullanıcı oluşturuldu', relationType: 'User', relationId: $user->id);

        $this->success('Kullanıcı oluşturuldu.', redirectTo: '/users');

        $this->dispatch('user-created');
    }
};
?>

<div>
    <x-header title="Yeni Kullanıcı" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Kullanıcı" subtitle="Kullanıcı bilgilerini giriniz" size="text-2xl"/>
            </div>

            <div class="col-span-3 grid gap-3">
                <x-input label="Adı" wire:model="name" icon="o-user"  />
                <x-input label="E-Posta" wire:model="email" icon="o-envelope"  />
                <x-input label="Parola" wire:model="password" type="password" icon="o-key"  />
                <x-input label="Parola Tekrarı" wire:model="password_confirmation" type="password" icon="o-key"  />

                <x-toggle label="Yönetici" wire:model="is_admin"/>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/users"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
