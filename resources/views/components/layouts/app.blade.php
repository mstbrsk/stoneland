<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="fantasy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.slim.min.js"
            integrity="sha512-sNylduh9fqpYUK5OYXWcBleGzbZInWj8yCJAU57r1dpSK9tP2ghf/SRYCMj+KsslFkCOt3TvJrX2AV/Gc3wOqA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"/>
    {{--
        <script src="https://cdn.tiny.cloud/1/YOUR-KEY-HERE/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/tr.js"></script>

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"/>

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        flatpickr.localize(flatpickr.l10ns.tr);
    </script>

    <style>
        .input {
            height: 35px !important;
        }

        textarea:focus, .input:focus {
            border: 1px solid #18181b !important;
            outline: none;
        }

        .select {
            height: 33px;
            min-height: 33px;
        }

        .select-primary {
            height: 44px !important;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

<x-modal id="modalLogoff" title="Oturum">
    <div>Oturumunuz kapatılacak?</div>

    <x-slot:actions>
        <x-button label="Vazgeç" onclick="modalLogoff.close()"/>
        <x-button label="Oturumu Kapat" class="btn-primary" onclick="window.location.href='/logout'"/>
    </x-slot:actions>
</x-modal>

{{-- NAVBAR mobile only --}}
<x-nav sticky class="lg:hidden">
    <x-slot:brand>
        <x-app-brand/>
    </x-slot:brand>
    <x-slot:actions>
        <label for="main-drawer" class="lg:hidden mr-3">
            <x-icon name="o-bars-3" class="cursor-pointer"/>
        </label>
    </x-slot:actions>
</x-nav>

{{-- MAIN --}}
<x-main full-width>
    <x-slot:sidebar drawer="main-drawer" collapseText="Daralt" collapsible
                    class="bg-white text-gray-700 border-r border-gray-200">
        <!-- BRAND -->
        <x-app-brand class="p-5 pt-3"/>

        <!-- MENU -->
        <x-menu activate-by-route class="text-gray-700">
            <!-- User -->
            @if($user = auth('web')->user())
                <div class="px-4 py-3 mb-6 bg-gray-100 rounded-lg mx-2">
                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                                 class="!-my-2 rounded">
                        <x-slot:avatar>
                            <img class="w-10 h-10 rounded-full"
                                 src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}"
                                 alt="{{ $user->name }}">
                        </x-slot:avatar>
                        <x-slot:actions>
                            <x-button icon="o-power"
                                      class="btn-circle btn-ghost btn-xs text-gray-500 hover:text-gray-700"
                                      tooltip-left="Oturumu Kapat" id="btnLogoff" no-wire-navigate/>
                        </x-slot:actions>
                    </x-list-item>
                </div>
            @endif

            <x-menu-item title="Ana Ekran" icon="o-home" link="/" class="hover:bg-gray-100"/>

            <x-menu-sub title="Cariler" icon="o-users" class="hover:bg-gray-100">
                <x-menu-item title="Kontaklar" icon="o-user" link="/contacts" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Gruplar" icon="o-user-group" link="/contact-groups" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Adresler" icon="o-map-pin" link="/addresses" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Para Birimi" icon="o-currency-dollar" link="/currencies"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Ödeme Koşulları" icon="o-credit-card" link="/payment-conditions"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Fiyat Listesi" icon="o-document-text" link="/price-lists"
                             class="pl-8 hover:bg-gray-200"/>
            </x-menu-sub>

            <x-menu-sub title="Satış" icon="o-shopping-cart" class="hover:bg-gray-100">
                <x-menu-item title="Satış Siparişleri" icon="o-shopping-bag" link="/sales"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Satış İade" icon="o-arrow-uturn-left" link="/sale-returns"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Sevkiyat" icon="o-truck" link="/shipments" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Teklifler" icon="o-document" link="/proposals" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Numuneler" icon="o-beaker" link="/samples" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Fırsatlar" icon="o-light-bulb" link="/crm/leads" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Üretim" icon="o-cog" link="/uretim" class="pl-8 hover:bg-gray-200"/>
                <x-menu-sub title="Yapılandırma" icon="o-cog-6-tooth" class="pl-8 hover:bg-gray-200">
                    <x-menu-item title="Etiketler" icon="o-tag" link="/tags" class="pl-12 hover:bg-gray-300"/>
                </x-menu-sub>
            </x-menu-sub>

            <x-menu-sub title="Satın Alma" icon="o-shopping-bag" class="hover:bg-gray-100">
                <x-menu-item title="Satın Alma Siparişi" icon="o-clipboard-document-list" link="/purchases"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Satınalma İade" icon="o-arrow-uturn-right" link="/purchase-returns/"
                             class="pl-8 hover:bg-gray-200"/>
            </x-menu-sub>

            <x-menu-sub title="Stok" icon="o-cube" class="hover:bg-gray-100">
                <x-menu-item title="Stok Kartı Oluştur" icon="o-plus-circle" link="/products/create"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Stoklar" icon="o-clipboard-document-check" link="/products"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Stok Durumu" icon="o-clipboard-document-check" link="/products/status"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Stok Hareketleri" icon="o-arrow-path" link="/product-transactions"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Depolar" icon="o-building-office-2" link="/warehouses"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Depo Transfer" icon="o-arrows-right-left" link="/warehouse-transfers"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Depo Stokları/Envanter" icon="o-clipboard-document-list" link="/inventories"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Ölçü Birimleri" icon="o-scale" link="/units" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Ürün Nitelikleri" icon="o-adjustments-horizontal" link="/product-attributes"
                             class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Ürün Varyantları" icon="o-squares-2x2" link="/product-variants"
                             class="pl-8 hover:bg-gray-200"/>
            </x-menu-sub>

            <x-menu-sub title="Ayarlar" icon="o-cog" class="hover:bg-gray-100">
                <x-menu-item title="Kullanıcılar" icon="o-users" link="/users" class="pl-8 hover:bg-gray-200"/>
                <x-menu-item title="Yetki Ayarları" icon="o-key" link="/settings/permissions"
                             class="pl-8 hover:bg-gray-200"/>
            </x-menu-sub>
        </x-menu>
    </x-slot:sidebar>

    <!-- The `$slot` goes here -->
    <x-slot:content>
        {{ $slot }}
    </x-slot:content>
</x-main>

{{--  TOAST area --}}
<x-toast/>
<x-spotlight/>
<x-livewire-alert::scripts/>
<x-spinner/>
</body>

<script>
    document.getElementById('btnLogoff').addEventListener('click', function () {
        const modalLogoff = document.getElementById('modalLogoff');
        modalLogoff.showModal();
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('focus-on-return-invoice-no', () => {
            $('#txtReturnInvoiceNo').focus();
        });
    });
</script>

</html>
