<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    use \Livewire\WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Reset pagination when any component property changes
    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filtre silindi.', position: 'toast-bottom');
    }

    public function delete(User $user): void
    {
        $user->delete();
        $this->warning("$user->name silindi");
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Adı', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'E-Posta', 'class' => 'hidden lg:table-cell'],
            ['key' => 'created_at', 'label' => 'Oluşturulma Tarihi', 'class' => 'hidden lg:table-cell'],
            ['key' => 'is_admin', 'label' => 'Yönetici Mi?'],
        ];
    }

    public function toggleStatus(User $user): void
    {
        $user->update([
            'status' => !$user->status
        ]);
    }

    public function users(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(config('sap.pagination.per_page'));
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <x-header title="Kullanıcılar" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Ara..." wire:model.live.debounce.1000ms="search" clearable
                     icon="o-magnifying-glass"/>
        </x-slot:middle>

        <x-slot:actions>
            <x-button label="Filtre" @click="$wire.drawer = true" responsive icon="o-funnel"/>
            <x-button label="Yeni" link="/users/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table link="users/{id}/edit" with-pagination :headers="$headers" :rows="$users" :sort-by="$sortBy">
            @php
                /** @var User $user */
            @endphp
            @scope('cell_is_admin', $user)
            {{ $user->is_admin ? 'Evet' : 'Hayır' }}
            @endscope

            @scope('actions', $user)
            <input type="checkbox" class="toggle toggle-primary"
                   wire:click="toggleStatus('{{ $user->id }}')" {{ $user->status ? 'checked' : '' }} />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filtre" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Ara..." .../>
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
