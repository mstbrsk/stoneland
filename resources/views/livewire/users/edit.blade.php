<?php

use Livewire\Volt\Component;

new class extends Component {
    use \Mary\Traits\Toast;
    use \Livewire\WithFileUploads;

    public \App\Models\User $user;

    #[\Livewire\Attributes\Rule('required')]
    public string $name = '';

    #[\Livewire\Attributes\Rule('required|email')]
    public string $email = '';

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?int $country_id = null;

    #[\Livewire\Attributes\Rule('nullable|image|max:1024')]
    public ?\Illuminate\Http\UploadedFile $photo = null;

    #[\Livewire\Attributes\Rule('required')]
    public array $my_languages = [];

    #[\Livewire\Attributes\Rule('sometimes')]
    public ?string $bio = null;

    public function mount(): void
    {
        $this->fill($this->user);

        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->user->update($data);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        $this->user->languages()->sync($this->my_languages);

        $this->success('User updated with success.', redirectTo: '/users');
    }

    public function with(): array
    {
        return [
            'countries' => \App\Models\Country::all(),
            'languages' => \App\Models\Language::all(),
        ];
    }
};
?>

<div>
    <x-header title="Update {{ $user->name }}" separator/>
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic" subtitle="Basic info from user" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg"/>
                </x-file>

                <x-input label="Name" wire:model="name"/>
                <x-input label="Email" wire:model="email"/>
                <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---"/>

            </div>
        </div>

        <hr class="my-5"/>

        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Details" subtitle="More about the user" size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">
                <x-choices-offline
                    label="My languages"
                    wire:model="my_languages"
                    :options="$languages"
                    searchable/>

                <x-editor wire:model="bio" label="Bio" hint="The great biography"/>

            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="/users"/>
            {{-- The important thing here is `type="submit"` --}}
            {{-- The spinner property is nice! --}}
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
