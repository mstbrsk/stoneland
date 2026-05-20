<?php

use App\Enums\Proposal\PdfPrintType;
use App\Models\Proposal;
use App\Models\PurchaseReturn;
use App\Models\Shipment;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Spatie\LaravelPdf\Facades\Pdf;

Volt::route('/login', 'login')->name('login');

/*if (app()->environment(['local', 'staging'])) {
    Volt::route('/register', 'register');
}*/

Route::get('/logout', function () {
    auth('web')->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

Route::middleware('auth:web')->group(function () {
    Volt::route('/', 'index');

    /*Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');*/

    Volt::route('/products', 'products.index')->middleware('allow:show_product_cart');
    Volt::route('/products/create', 'products.create')->middleware('allow:create_product_cart');
    Volt::route('/products/{product}/edit', 'products.edit')->middleware('allow:update_product_cart');
    Volt::route('/products/status', 'products.stock_status')->middleware('allow:show_product_cart');

    Volt::route('/warehouses', 'warehouses.index')->middleware('allow:show_warehouse');
    Volt::route('/warehouses/create', 'warehouses.create')->middleware('allow:create_warehouse');
    Volt::route('/warehouses/{warehouse}/edit', 'warehouses.edit')->middleware('allow:update_warehouse');

    Volt::route('/warehouse-transfers', 'warehouse-transfer.index')->middleware('allow:show_warehouse_transfer');
    Volt::route('/warehouse-transfers/create', 'warehouse-transfer.create')->middleware('allow:create_warehouse_transfer');

    Volt::route('/addresses', 'addresses.index')->middleware('allow:show_address');
    Volt::route('/addresses/create', 'addresses.create')->middleware('allow:create_address');
    Volt::route('/addresses/{addressModel}/edit', 'addresses.edit')->middleware('allow:update_address');

    Volt::route('/contacts', 'contacts.index')->middleware('allow:show_contact');
    Volt::route('/contacts/create', 'contacts.create')->middleware('allow:create_contact');
    Volt::route('/contacts/{contact}/edit', 'contacts.edit')->middleware('allow:update_contact');

    Volt::route('/purchases', 'purchases.index')->middleware('allow:show_purchase');
    Volt::route('/purchases/create', 'purchases.create')->middleware('allow:create_purchase');
    Volt::route('/purchases/{purchase}/edit', 'purchases.edit')->middleware('allow:update_purchase');

    Route::get('/purchases/{id}/print', function (string $id) {
        $type = request('type', PdfPrintType::WITH_PRICE->value);

        $type = PdfPrintType::tryFrom($type);

        $purchases = Purchase::findOrFail($id);

        $filename = "satin_alma_{$type->to('lower')}_";

        return Pdf::view('livewire.purchases.print', ['sales' => $purchases, 'type' => $type->value])->download($filename);
    })
        ->name('purchase.print');

    Volt::route('/sales', 'sales.index')->middleware('allow:show_sale');
    Volt::route('/sales/create', 'sales.create')->middleware('allow:create_sale');
    Volt::route('/sales/{sale}/edit', 'sales.edit')->middleware('allow:update_sale');

    Volt::route('/proposals', 'proposals.index')->middleware('allow:show_proposal');
    Volt::route('/proposals/create', 'proposals.create')->middleware('allow:create_proposal');
    Volt::route('/proposals/{proposal}/edit', 'proposals.edit')->middleware('allow:update_proposal');

    Volt::route('/currencies', 'currencies.index')->middleware('allow:show_currency');
    Volt::route('/currencies/create', 'currencies.create')->middleware('allow:create_currency');
    Volt::route('/currencies/{currency}/edit', 'currencies.edit')->middleware('allow:update_currency');

    Volt::route('/units', 'units.index')->middleware('allow:show_unit');
    Volt::route('/units/create', 'units.create')->middleware('allow:create_unit');
    Volt::route('/units/{unit}/edit', 'units.edit')->name('units.edit')->middleware('allow:update_unit');

    Volt::route('/payment-conditions', 'payment-conditions.index')->middleware('allow:payment_conditions');
    Volt::route('/payment-conditions/create', 'payment-conditions.create')->middleware('allow:payment_conditions');
    Volt::route('/payment-conditions/{paymentConditions}/edit', 'payment-conditions.edit')->name('payment-conditions.edit')->middleware('allow:payment_conditions');

    Volt::route('/price-lists', 'price-lists.index')->middleware('allow:price_lists');
    Volt::route('/price-lists/create', 'price-lists.create')->middleware('allow:price_lists');
    Volt::route('/price-lists/{priceList}/edit', 'price-lists.edit')->name('price-lists.edit')->middleware('allow:price_lists');

    Volt::route('/product-attributes', 'product-attributes.index')->middleware('allow:show_product_attribute');
    Volt::route('/product-attributes/create', 'product-attributes.create')->middleware('allow:create_product_attribute');
    Volt::route('/product-attributes/{productAttribute}/edit', 'product-attributes.edit')->middleware('allow:update_product_attribute');

    Volt::route('/tags', 'tags.index')->middleware('allow:show_tag');
    Volt::route('/tags/create', 'tags.create')->middleware('allow:create_tag');
    Volt::route('/tags/{tag}/edit', 'tags.edit')->middleware('allow:update_tag');

    Volt::route('/crm/leads', 'crm.leads.index')->middleware('allow:show_crm_lead');
    Volt::route('/crm/leads/create', 'crm.leads.create')->middleware('allow:create_crm_lead');
    Volt::route('/crm/leads/{lead}/edit', 'crm.leads.edit')->middleware('allow:update_crm_lead');

    Volt::route('/settings/permissions', 'settings.permissions')->middleware('allow:show_permission');

    Volt::route('/product-variants', 'product-variants.index')->middleware('allow:show_product_variant');
    Volt::route('/inventories', 'inventory.index')->middleware('allow:show_inventories');

    Volt::route('/product-transactions', 'product-transactions.index')->middleware('allow:show_product_transactions');

    Volt::route('/uretim', 'uretim.index');               // User (list)
    Volt::route('/uretim/create', 'uretim.create');
    Volt::route('/uretim/teklif', 'uretim.teklif');
    Volt::route('/uretim/recete', 'uretim.recete');
    Volt::route('/uretim/{warehouse}/edit', 'uretim.edit');    // User (edit)
    Volt::route('/uretim/fatura', 'uretim.fatura');       // User (create)
    Volt::route('/uretim/siparis', 'uretim.siparis');       // User (create)
    Volt::route('/uretim/satis', 'uretim.satis');       // User (create)
    Volt::route('/uretim/satisteklif', 'uretim.satisteklif');
    Volt::route('/uretim/satinalma', 'uretim.satinalma');
    Volt::route('/uretim/sevkiyat', 'uretim.sevkiyat');
    Volt::route('/uretim/sevk-irsaliyesi', 'uretim.sevk_irsaliyesi');
    Volt::route('/uretim/satin-alma-iade', 'uretim.satin_alma_iade');

    Volt::route('/guzzle', 'guzzle.index');


    Volt::route('/samples', 'samples.index');
    Volt::route('/samples/create', 'samples.create');
    Volt::route('/samples/{sample}/edit', 'samples.edit');

    Volt::route('/return', 'return.index');
    Volt::route('/return/create', 'return.create');
    Volt::route('/return/{return}/edit', 'return.edit');

    Volt::route('/sale-returns', 'sale-returns.index');
    Volt::route('/sale-returns/{sale}/edit', 'sale-returns.edit');

    Volt::route('/purchase-returns', 'purchase-returns.index');
    Volt::route('/purchase-returns/{purchase}/edit', 'purchase-returns.edit');

    Volt::route('/shipments', 'shipments.index');
    Volt::route('/shipments/{shipment}', 'shipments.edit');


    Volt::route('/buys-return', 'buys-return.index');
    Volt::route('/buys-return/create', 'buys-return.create');

    Volt::route('/transport', 'transport.index');
    Volt::route('/transport/create', 'transport.create');

    Volt::route('/contact-groups', 'contact-groups.index');
    Volt::route('/contact-groups/create', 'contact-groups.create');
    Volt::route('/contact-groups/{group}/edit', 'contact-groups.edit');

    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{$user}/edit', 'users.edit');

    Route::get('/sales/{id}/print', function (string $id) {
        $type = request('type', PdfPrintType::WITH_PRICE->value);

        $type = PdfPrintType::tryFrom($type);

        $sale = Sale::with('contact', 'items', 'updatedBy','items.product')->findOrFail($id);

        $filename = "satis_{$type->to('lower')}_{$sale->sales_no}";

        return Pdf::view('livewire.sales.print', ['sale' => $sale, 'type' => $type->value])->download($filename);
    })
        ->name('sales.print');

    Route::get('/proposals/{id}/print', function (string $id) {
        $type = request('type', PdfPrintType::WITH_PRICE->value);

        $type = PdfPrintType::tryFrom($type);

        $proposal = Proposal::findOrFail($id);

        $filename = "teklif_{$type->to('lower')}_{$proposal->proposal_no}";

        return Pdf::view('livewire.proposals.print', ['proposal' => $proposal, 'type' => $type->value])->download($filename);
    })
        ->name('proposals.print');

    Route::get('/purchases/{id}/print', function (string $id) {
        $type = request('type', PdfPrintType::WITH_PRICE->value);

        $type = PdfPrintType::tryFrom($type);

        $purchase = Purchase::findOrFail($id);

        $filename = "satin_alma_{$type->to('lower')}_{$purchase->purchase_no}";

        return Pdf::view('livewire.purchases.print', ['purchase' => $purchase, 'type' => $type->value])->download($filename);
    })
        ->name('purchases.print');

    Route::get('/purchase-returns/{id}/print', function (string $id) {
        $type = request('type', PdfPrintType::WITH_PRICE->value);

        $type = PdfPrintType::tryFrom($type);

        $purchaseReturn = PurchaseReturn::findOrFail($id);

        $filename = "satin_alma_iade_{$type->to('lower')}_{$purchaseReturn->sale_invoice_no}";

        return Pdf::view('livewire.purchase-returns.print', ['purchaseReturn' => $purchaseReturn, 'type' => $type->value]);
    })
        ->name('purchase-returns.print');

    Route::get('/shipments/{id}/print', function (string $id) {
        $shipment = Shipment::with('contact')->findOrFail($id);
        $shipmentItems = ShipmentItem::with('product')->where('shipment_id', $id)->where('can_printable', true)->get();

        $sale = Sale::where('shipment_id', $id)->first();


        return Pdf::view('livewire.shipments.print', compact('shipmentItems', 'shipment','sale'));
    })
        ->name('shipments.print');

});
