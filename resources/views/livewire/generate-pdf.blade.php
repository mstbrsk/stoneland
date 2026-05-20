<div>
    <button wire:click="createPDF">PDF Oluştur ve Kaydet</button>

    @if (session()->has('message'))
        <div style="color: green;">
            {{ session('message') }}
        </div>
    @endif
</div>
