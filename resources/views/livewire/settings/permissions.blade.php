<?php
/** @var \Illuminate\Support\Collection $modules */

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Rule('required', message: 'Bir kullanıcı seçiniz')]
    public string $user_id;

    public ?\Illuminate\Support\Collection $settings = null;

    #[Rule('required')]
    public string $updated_by;

    public string $search = '';

    public bool $editable = false;

    public bool $selectAll = false;

    public function updatedSelectAll($value)
    {
        foreach (collect(config('sap.modules'))->sort() as $key => $name) {
            $this->toggleAll($key, $value);
        }
    }

    public function toggleAll($key, $value): void
    {
        $permissions = ['show', 'create', 'update', 'delete'];

        foreach ($permissions as $permission) {
            $this->settings["{$permission}_{$key}"] = $value;
        }
    }

    public function updatedUserId(?string $userId)
    {
        $this->editable = !is_null($userId);

        if ($userId) {
            $this->settings = collect(\App\Models\User::firstWhere('id', $this->user_id)->permissions);
        }
    }

    public function mount()
    {
        $this->fill([
            'settings' => collect(),
            'updated_by' => auth('web')->id(),
        ]);
    }

    public function save(): void
    {
        $this->validate();

        $user = \App\Models\User::findOrFail($this->user_id);

        $user->update([
            'permissions' => $this->settings,
            'updated_by' => $this->updated_by,
        ]);

        $this->success('İzinler güncellendi.', redirectTo: '/settings/permissions');

        $this->dispatch('user-permissions-updated');
    }

    public function with(): array
    {
        return [
            'users' => \App\Models\User::all(),

            'modules' => collect(config('sap.modules'))->sort(),
        ];
    }
};
?>

<div class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Yetki Ayarları</h1>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="mb-6">
                <x-header size="text-xl">
                    <x-slot:middle class="!justify-start">
                        <x-choices-offline label="Kullanıcı" wire:model.live="user_id" :options="$users" single
                                           searchable no-result-text="Kullanıcı bulunamadı"
                                           style="width: 600px"/>
                    </x-slot:middle>
                </x-header>
            </div>

            <div class="mb-6 flex items-center justify-between bg-gray-100 p-4 rounded-lg"
                 x-bind:class="{ 'opacity-50 pointer-events-none': {{ $editable ? 'false' : 'true' }} }">
                <span class="font-semibold text-gray-700">Tüm Yetkileri Seç/Kaldır</span>
                <button wire:click="$toggle('selectAll')"
                        class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $selectAll ? 'bg-indigo-600' : 'bg-gray-200' }}">
                    <span class="sr-only">Tüm yetkileri seç</span>
                    <span aria-hidden="true"
                          class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $selectAll ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" x-data="xPermissions"
                 x-bind:class="{ 'opacity-50 pointer-events-none': {{ $editable ? 'false' : 'true' }} }">
                @foreach($modules as $key => $name)
                    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $name }}</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Görüntüleme</span>
                                <x-toggle wire:model="settings.show_{{ $key }}"/>
                            </label>
                            @if (!in_array($key, ['permission', 'product_variant']))
                                <label class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Oluşturma</span>
                                    <x-toggle wire:model="settings.create_{{ $key }}"/>
                                </label>
                                <label class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Düzenleme</span>
                                    <x-toggle wire:model="settings.update_{{ $key }}"/>
                                </label>
                                <label class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Silme</span>
                                    <x-toggle wire:model="settings.delete_{{ $key }}"/>
                                </label>
                            @endif
                        </div>
                        <div class="mt-4">
                            <button @click="selectAll('{{ $key }}')"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Tümünü Seç/Kaldır
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="/product-attributes"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    İptal
                </a>
                <button wire:click="save"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" {{ $editable ? '' : 'disabled' }}>
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('xPermissions', () => ({
        selectAll: function (key) {
            $wire = @this;
            const setting = $wire.settings;
            console.log(setting)

            setting[`show_${key}`] = !setting[`show_${key}`];
            setting[`create_${key}`] = !setting[`create_${key}`];
            setting[`update_${key}`] = !setting[`update_${key}`];
            setting[`delete_${key}`] = !setting[`delete_${key}`];
        },
    }));
</script>
@endscript
