<div>
    <button wire:click="fetchData" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Veriyi Al
    </button>

    @if ($responseData)
        <div class="mt-4">
            <h2 class="text-lg font-bold">API Cevabı:</h2>
            <pre>{{ json_encode($responseData, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    <div class="text-center border border-black p-4 mb-10 break-after-page">
        hghgfhgfhdgfh
        kjghk
        khjgk
    </div>

    <button class="text-white bg-blue-700" wire:click="generatePdf" wire:loading.attr="disabled">
        <span wire:loading.remove>PDF Oluştur</span>
        <div wire:loading>İndiriliyor...</div>
    </button>


    <button wire:click="createPDF">PDF Oluştur ve Kaydet</button>

    @if (session()->has('message'))
        <div style="color: green;">
            {{ session('message') }}
        </div>
    @endif

</div>
