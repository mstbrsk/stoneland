<?php
use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;


new class extends Component {

    use Toast;
    use WithFileUploads;

    public string $name = '';
    public string $short_name = '';

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;

    public function save():void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $validated = $this->validate([
            'name' => 'required',
            'short_name' => 'required',
            'created_by' => 'required',
            'updated_by' => 'required',
        ]);


        // Execution doesn't reach here if validation fails.

       Warehouse::create($validated);


        $this->success('We are done, check it out');

        $this->redirect('/uretim');



    }



};

?>

<div>
    <x-header title="Personal address" subtitle="Your home address" separator />
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
