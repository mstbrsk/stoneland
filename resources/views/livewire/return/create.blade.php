<?php


use App\Models\Warehouse;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;
    use WithFileUploads;

    public string $return_order = '';
    public string $return_invoice = '';
    public string $product_invoice = '';
    public string $state = '';

    #[Rule('required')]
    public string $created_by;

    #[Rule('required')]
    public string $updated_by;


    public function save():void
    {
        $this->created_by = auth('web')->id();
        $this->updated_by = auth('web')->id();

        $validated = $this->validate([
            'return_order' => 'required',
            'return_invoice' => 'required',
            'product_invoice' => 'required',
            'state' => 'required',
            'created_by' => 'required',
            'updated_by' => 'required',
        ]);




        Warehouse::create($validated);


        $this->success('İade oluşturuldu');

        $this->redirect('/return');



    }

}; ?>

<div>

    <x-header title="İade" separator />
    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-1">
                <x-header title="Özellikleri"  size="text-2xl"/>
            </div>
            <div class="col-span-3 grid gap-3">

                <x-input label="İade Siparişi" wire:model="return_order" required/>
                <x-input label="İade Faturası" wire:model="return_invoice" required/>
                <x-input label="Ürün Faturası" wire:model="product_invoice" />
                <x-input label="Durum" wire:model="state" />


            </div>
        </div>

        <x-slot:actions>
            <x-button label="İptal" link="/return"/>
            <x-button label="Kaydet" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
        </x-slot:actions>
    </x-form>
</div>
