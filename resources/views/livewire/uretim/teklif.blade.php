<?php

use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;





}; ?>

<style>





</style>


<div>
    <x-header title="Teklif siparişlerini üretim siparişine dönüştürülmesi " subtitle="Your home address" separator />




     <x-form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">

         <div class="col-span-2">
             <label class="block text-gray-700 text-sm font-bold mb-2">Teklif</label>
             <div class="grid grid-cols-3 gap-4">
                 <x-input type="text" name="xs" label="Sipariş" placeholder="Sipariş" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                 <x-input type="number" label="Sipariş No" placeholder="Sipariş No" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                 <x-input type="text" name="m"  label="Müşteri" placeholder="Müşteri" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />

             </div>

             <div class="grid grid-cols-3 py-4 gap-4">
                 <x-input type="text"   label="Sipariş Miktarı" name="xs" placeholder="Sipariş Miktarı" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                 <x-datetime label="Sipariş Tarihi" wire:model="myDate" icon-right="o-calendar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                 <x-datetime label="Teslim Tarihi" wire:model="myDate" icon-right="o-calendar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />


             </div>

         </div>

        <div class="col-span-1">
            <label for="stok-kodu" class="block text-gray-700 text-sm font-bold mb-2">Kumaş Stok Kodu:</label>
            <input type="text" id="stok-kodu" name="stok-kodu" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="col-span-1">
            <label for="stok-adi" class="block text-gray-700 text-sm font-bold mb-2">Stok Adı:</label>
            <input type="text" id="stok-adi" name="stok-adi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="col-span-2">
            <label class="block text-gray-700 text-sm font-bold mb-2">Beden Dağılımı:</label>
            <div class="grid grid-cols-5 gap-4">
                <x-input type="number" name="xs" placeholder="XS" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="s" placeholder="S" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="m" placeholder="M" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="l" placeholder="L" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="xl" placeholder="XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
            </div>
            <div class="grid grid-cols-5 gap-4 mt-4">
                <x-input type="number" name="2xl" placeholder="2XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="3xl" placeholder="3XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="4xl" placeholder="4XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="5xl" placeholder="5XL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                <x-input type="number" name="5xl" placeholder="TOPLAM" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" readonly />
            </div>
        </div>

        <div class="col-span-1">
            <label for="baski-detayi" class="block text-gray-700 text-sm font-bold mb-2">Baskı Detayı:</label>
            <input type="text" id="baski-detayi" name="baski-detayi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="col-span-1">
            <label for="resim" class="block text-gray-700 text-sm font-bold mb-2">Resim:</label>
            <input type="file" id="resim" name="resim" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>



        <div class="col-span-2">
            <label for="aciklama" class="block text-gray-700 text-sm font-bold mb-2">Açıklama:</label>
            <textarea id="aciklama" name="aciklama" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
        </div>


        <x-slot:actions>
            <x-button label="Cancel"  link="/uretim" />
            <x-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
        </x-form>



</div>
