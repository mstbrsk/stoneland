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
    <x-header title="Reçete oluşturma " subtitle="Your home address" separator />




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

        <div class="col-span-2">



            <div >
                <h2 class="text-2xl font-bold mb-6 text-center">Üretim Talimatları</h2>
                <div class="overflow-x-auto">
                    <div class="min-w-full">
                        <div class="flex border-b-2 border-gray-200 bg-gray-100">
                            <div class="flex-none w-14 h-14 px-6 py-2" style="padding-right: 50px">Pozisyon </div>
                            <div class="grow px-4 py-2" >Talimat Kodu </div>
                        </div>


                       <div class="flex ">

                           <div class="flex-none w-14 h-14 px-4 py-2 ">  01 </div>

                        <div class="grow h-14 ">
                            <x-input  type="textarea" name="s"  class=" grid-span-3 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                    </div>

                        <div class="flex ">

                            <div class="flex-none w-14 h-14 px-4 py-2 ">  02 </div>

                            <div class="grow h-14 ">
                                <x-input  type="textarea" name="s"  class=" grid-span-3 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            </div>

                        </div>

                    </div>
                </div>
            </div>














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





        <div class=" p-8 rounded-lg  w-full max-w-5xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-center">Stok Tablosu</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full ">
                    <thead>
                    <tr>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Pozisyon</th>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Stok Kodu</th>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Stok Adı</th>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Miktar </th>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Birim </th>
                        <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Toplam Miktar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">1</td>
                        <td class="py-2 px-4 border-b border-gray-200">STK001</td>
                        <td class="py-2 px-4 border-b border-gray-200">Ürün Adı 1</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">100</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">2</td>
                        <td class="py-2 px-4 border-b border-gray-200">STK002</td>
                        <td class="py-2 px-4 border-b border-gray-200">Ürün Adı 2</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">200</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">3</td>
                        <td class="py-2 px-4 border-b border-gray-200">STK003</td>
                        <td class="py-2 px-4 border-b border-gray-200">Ürün Adı 3</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">Adet</td>
                        <td class="py-2 px-4 border-b border-gray-200">300</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>











        <div class="col-span-1">
            <label for="baski-detayi" class="block text-gray-700 text-sm font-bold mb-2">Ürün Görseli:</label>
            <input type="file" id="resim" name="resim" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>




        <x-slot:actions>
            <x-button label="Cancel"  link="/uretim" />
            <x-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>



</div>
