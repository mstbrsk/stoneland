<?php
/** @var \App\Models\PurchaseReturn $purchaseReturn */
?>

<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap");

    table {
        width: 100%;
        caption-side: bottom;
        border-collapse: collapse;
    }

    th {
        text-align: left;
    }

    .line > td {
        border-top: 1px solid #EBEBEB;
    }

    td {
        padding: 5px 15px;
        line-height: 1.55em;
    }

    td.siparis {
        padding: 0px 15px;
        line-height: 1.55em;

    }

    th {
        padding: 10px 15px;
        line-height: 1.55em;
    }

    .text_center { text-align: center}

    .text_left {text-align: left}

    .font_siparis {font-size: 11px;}

    .font_9 {font-size: 7px;}

    .font_12 {font-size: 12px;}

    .font_firma {font-size: 11px;}

    .mt_10{ margin-top: 100px;}

    .tm_container {
        max-width: 880px;
        padding: 30px 15px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .tm_invoice_wrap {
        position: relative;
    }

    .tm_invoice {
        padding: 30px 20px;
    }


    .tm_invoice.tm_style1.tm_type1 {
        padding: 30px 20px;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_head {
        height: initial;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_info {
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-align: start;
        -ms-flex-align: start;
        align-items: flex-start;
        padding-left: 15px;
        padding-right: 15px;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_seperator {
        width: 100%;
        -webkit-transform: initial;
        transform: initial;
        right: 0;
        top: 0;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_logo img {
        max-height: 60px;
    }

    .tm_invoice_in {
        position: relative;
        z-index: 100;
    }

    .tm_invoice.tm_style1 .tm_invoice_head {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
    }

    .tm_invoice.tm_style1 .tm_invoice_head .tm_invoice_right div {
        line-height: 1em;
    }

    .tm_mb15 {
        margin-bottom: 15px;
    }

    .tm_align_center {
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .tm_invoice.tm_style1 .tm_invoice_left {
        max-width: 100%;
    }


    .tm_invoice.tm_style1.tm_type1 .tm_invoice_head {
        height: 110px;
        position: relative;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_shape_bg {
        position: absolute;
        height: 100%;
        width: 70%;
        -webkit-transform: skewX(35deg);
        transform: skewX(35deg);
        top: 0px;
        right: -100px;
        overflow: hidden;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_shape_bg img {
        height: 100%;
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
        -webkit-transform: skewX(-35deg) translateX(-45px);
        transform: skewX(-35deg) translateX(-45px);
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_right {
        position: relative;
        z-index: 2;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_logo img {
        max-height: 70px;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_seperator {
        margin-right: 0;
        border-radius: 0;
        -webkit-transform: skewX(35deg);
        transform: skewX(35deg);
        position: absolute;
        height: 100%;
        width: 57.5%;
        right: -60px;
        overflow: hidden;
        border: none;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_seperator img {
        height: 100%;
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
        -webkit-transform: skewX(-35deg);
        transform: skewX(-35deg);
        -webkit-transform: skewX(-35deg) translateX(-10px);
        transform: skewX(-35deg) translateX(-10px);
    }

    .tm_invoice.tm_style1.tm_type1 .tm_invoice_info {
        position: relative;
        padding: 4px 0;
    }

    .tm_invoice.tm_style1.tm_type1 .tm_card_note,
    .tm_invoice.tm_style1.tm_type1 .tm_invoice_info_list {
        position: relative;
        z-index: 1;
    }

    .tm_table_responsive {
        overflow-x: auto;
    }

    .tm_table_responsive > table {
        min-width: 600px;
    }

    .tm_width_1 {
        width: 12.33333333%;
    }

    .tm_width_2 {
        width: 16.66666667%;
    }

    .tm_width_3 {
        width: 45%;
    }

    .tm_width_9 {
        width: 8.33333333%;
    }

    .tm_width_4 {
        width: 33.33333333%;
    }

    .tm_width_6 {
        width: 50%;
    }


    .tm_width_8 {
        width: 80.33333333%;
    }

    .tm_width_7 {
        width: 30%;
    }


    .tm_semi_bold {
        font-weight: 600;
        font-size: 10px;
    }

    .tm_text_right {
        text-align: right;
    }

    .tm_invoice_footer {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .tm_invoice_footer table {
        margin-top: -1px;
    }

    .tm_border_top {
        border-top: 1px solid #EBEBEB;
    }

    .tm_invoice_footer .tm_left_footer {
        width: 58%;
        padding: 10px 15px;
        -webkit-box-flex: 0;
        -ms-flex: none;
        flex: none;
    }

    .tm_invoice_footer .tm_right_footer {
        width: 42%;
    }

    .tm_table.tm_style1.tm_type1 {
        padding: 0px 20px;
    }

    .tm_gray_bg {
        background: #EBEBEB
    }

    .tm_primary_color {
        color: #111;
    }

    .tm_f16 {
        font-size: 16px;
    }

    .tm_bold {
        font-weight: 700;
    }

    .tm_border_top_0 {
        border-top: 0;
    }


</style>

<div class="tm_container">
    <div class="tm_invoice_wrap">
        <div class="tm_invoice tm_style1 tm_type1" id="tm_download_section">
            <div class="tm_invoice_in">
                <div class="tm_invoice_head tm_top_head tm_mb15 tm_align_center">
                    <div class="tm_invoice_left">
                        <div class="tm_logo"><img style="width: 90px;height: 30px"
                                                  src="{{ image_to_base64(public_path('assets/images/logo.png')) }}"
                                                  alt="Logo"></div>
                    </div>

                    <div class="tm_invoice_right font_siparis">
                        <p>Sipariş Tarihi : {{ now()->format('d-m-Y') }}</p>
                        <p class="tm_mb2"><b class="tm_primary_color">Satışiade Sipariş No :</b>
                            {{ $purchaseReturn->purchase->purchase_no }}
                        </p>
                    </div>
                </div>



                <div>
                    <p  class="font_12" style="display: block;text-align: center;margin-bottom: 30px"><b class="tm_primary_color">
                            SATIN ALMA İADE FORMU  </b>
                    </p>
                </div>

                <div class="border_0" style="margin-bottom: 30px;">
                    <div class="border_0">
                        <div class="tm_table_responsive">
                            <table>
                                <tbody>
                                <tr>
                                    <td class="tm_width_6 border_0 text_justify ">
                                        <b class="font_siparis">Müşteri, </b><br>
                                        <div class="font_firma"><b>{{ $purchaseReturn->purchase->supplier->name }}</b></div>
                                        <div class="font_firma"><b>Telefon: </b>{{ $purchaseReturn->purchase->supplier->phone }}</div>
                                        <div class="font_firma"><b>Vergi
                                                Dairesi: </b>{{ $purchaseReturn->purchase->supplier?->taxAdministration?->name }}</div>
                                        <div class="font_firma"><b>Vergi No: </b>{{ $purchaseReturn->purchase->supplier->tax_number }}</div>
                                    </td>

                                    <td class="tm_width_6 border_0 " style="padding-left:120px;">
                                        <div class="font_firma"><b>Satış İade Fatura No: : </b> {{ $purchaseReturn->sale_invoice_no }}</div>
                                        <div class="font_firma"><b>Teslim Tarihi: </b>   @if($purchaseReturn->deadline_at)
                                                {{ \Carbon\Carbon::parse($purchase->deadline_at)->format('d.m.Y') }}
                                            @else
                                                Belirtilmemiş
                                            @endif</div>
                                        <div class="font_firma"><b>Satınalma Temsilcisi: </b>{{ $purchaseReturn->updatedBy->name }}
                                        </div>
                                        <div class="font_firma"><b> Teslimat Adresi: </b>{{ $purchaseReturn->warehouse?->address?->address }}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>









                <div class="tm_table tm_style1">
                    <div class="">
                        <div class="tm_table_responsive">
                            <table>
                                <thead>
                                <tr class="">
                                    <th class="tm_width_2 tm_semi_bold ">Stok Kodu </th>
                                    <th class="tm_width_7 tm_semi_bold ">Stok Adı</th>
                                    <th class="tm_width_5 tm_semi_bold ">Beden</th>
                                    <th class="tm_width_1 tm_semi_bold ">Satış Miktarı</th>
                                    <th class="tm_width_1 tm_semi_bold ">İade Miktarı</th>

                                </tr>
                                </thead>
                                <tbody>
                                 @php
                                    $totalReturnQty = 0;
                                @endphp
                                @foreach($purchaseReturn->getReturns() as $return)
                                    @if(isset($return['return_qty']) && $return['return_qty'] > 0)
                                        @php
                                            $totalReturnQty += $return['return_qty'];
                                        @endphp
                                        <tr>

                                            <td class="tm_width_2 siparis font_firma"> {{ $return['stock_code'] }}</td>
                                            <td class="tm_width_7 siparis font_firma">{{ $return['product_name'] }} </td>
                                            <td class="tm_width_5 siparis font_siparis">{{ $return['variant_name'] }}</td>
                                            <td class="tm_width_1 siparis font_siparis ">{{ $return['purchased_qty'] }}</td>
                                            <td class="tm_width_1 siparis font_siparis ">{{ $return['return_qty'] }}</td>

                                        </tr>
                                    @endif
                                @endforeach





                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tm_invoice_footer tm_border_top tm_mb15 tm_m0_md">
                        <div class="tm_left_footer">

                            <div class="tm_invoice_head tm_mb10">
                                <div class="tm_invoice_left">


                                    <p class="tm_mb2"><b class="tm_primary_color font_siparis">
                                        </b>
                                    </p>
                                    <br>
                                    <p class="tm_mb2"><b class="tm_primary_color font_siparis">
                                            AD SOYAD
                                        </b>
                                        <br><br>
                                    </p>

                                    <br>
                                    <p class="tm_mb2"><b class="tm_primary_color font_siparis">
                                            KAŞE - İMZA
                                        </b>

                                    </p>

                                </div>

                            </div>


                        </div>

                        <div class="tm_right_footer">
                            <table class="tm_mb15">
                                <tbody>
                                <tr class="tm_gray_bg ">
                                    <td class="tm_width_3 tm_primary_color font_siparis "></td>

                                    <td class="tm_width_3 tm_primary_color  tm_text_right font_siparis"></td>
                                </tr>
                                @if ($type==\App\Enums\Proposal\PdfPrintType::WITH_PRICE->value)
                                    <tr class="tm_gray_bg ">
                                        <td class="tm_width_3 tm_primary_color font_siparis">Toplam İade Miktarı</td>

                                        <td class="tm_width_3 tm_primary_color  tm_text_right font_siparis"> {{ $totalReturnQty }}</td>
                                    </tr>




                                @endif
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="tm_invoice_footer tm_type1 mt_10">
                        <div class="tm_left_footer font_siparis">
                            Tel :2163064326  <br>
                            Mail : info@berkaisguvenligi.com.tr  <br>
                            Web Site : www.berkaisguvenligi.com.tr

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
