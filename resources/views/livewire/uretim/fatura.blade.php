<?php
/** @var \App\Models\Proposal $proposal */
/** @var \App\Models\ProposalProduct $item */
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

    td {
        border-top: 1px solid #dbdfea;
    }

    td {
        padding: 10px 15px;
        line-height: 1.55em;
    }

    th {
        padding: 10px 15px;
        line-height: 1.55em;
    }

    .text_center { text-align: center}

    .text_left {text-align: left}



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
        width: 8.33333333%;
    }

    .tm_width_2 {
        width: 16.66666667%;
    }

    .tm_width_3 {
        width: 25%;
    }

    .tm_width_4 {
        width: 33.33333333%;
    }

    .tm_semi_bold {
        font-weight: 600;
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
        border-top: 1px solid #dbdfea;
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
        background: #f5f6fa;
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
                    <br>
                    Berka İş Güvenliği<br>
                    Yalı Mah. Çatalay Sok. No:9/1 Kartal İSTANBUL<br><br>

                    <span>V.D : Kartal / V.N : 6080046256</span>
                </div>

                <div class="tm_invoice_head tm_mb10">
                    <div class="tm_invoice_left">
                        <p><b>Firma:</b> </p>


                        <p class="tm_mb2"><b class="tm_primary_color">Satın Alma
                                Temsilcisi: </b></p>
                    </div>

                    <div class="tm_invoice_right">
                        <p>Teklif: </p>
                        <p class="tm_mb2"><b class="tm_primary_color">Teklif Tarihi
                                 </b>
                        </p>


                    </div>


                </div>


                <div>
                <p  style="display: block;text-align: center"><b class="tm_primary_color">
                        TEKLİF MEKTUBU </b>
                </p>
                </div>
                <div   style="display: block;margin-bottom: 50px" >


                    <p class="tm_mb2 text_center" >
                            İhtiyacınıza uygun ürün bilgileri ile sevkiyat ve ödeme koşulları asağıda belirtilmiştir.
                    </p>

                    <p class="tm_mb2 text_left">
                        İlginize teşekkür eder bilgi ve görüşlerinize sunarız.
                    </p>

                </div>


                <div class="tm_table tm_style1">
                    <div class="">
                        <div class="tm_table_responsive">
                            <table>
                                <thead>
                                <tr class="">
                                    <th class="tm_width_3 tm_semi_bold ">Açıklama</th>
                                    <th class="tm_width_1 tm_semi_bold ">Miktar</th>

                                        <th class="tm_width_2 tm_semi_bold ">Birim Fiyat</th>
                                        <th class="tm_width_3 tm_semi_bold ">Vergi Oranı</th>
                                        <th class="tm_width_2 tm_semi_bold tm_text_right">Tutar</th>

                                </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td class="tm_width_3">gfg</td>
                                        <td class="tm_width_1">gfdg</td>

                                            <td class="tm_width_1">fgdf</td>
                                            <td>gfdgf</td>
                                            <td class="tm_width_2 tm_text_right">fdgf</td>

                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tm_invoice_footer tm_border_top tm_mb15 tm_m0_md">
                        <div class="tm_left_footer">

                            <div class="tm_invoice_head tm_mb10">
                                <div class="tm_invoice_left">


                                    <p class="tm_mb2"><b class="tm_primary_color">
                                            Teslimat: </b>
                                    </p>

                                    <p class="tm_mb2"><b class="tm_primary_color">
                                            Vade:</b>
                                    </p>

                                    <p class="tm_mb2"><b class="tm_primary_color">
                                            Geçerlilik Tarihi:</b>
                                    </p>

                                </div>

                            </div>


                            <p class="tm_mb2 mt_10" ><b class="tm_primary_color">
                                    NOTLAR:
                                </b>
                            </p>
                            <p class="tm_m0 tm_f12">gfdg</p>
                        </div>

                            <div class="tm_right_footer">
                                <table class="tm_mb15">
                                    <tbody>
                                    <tr class="tm_gray_bg ">
                                        <td class="tm_width_3 tm_primary_color tm_bold">Vergi Hariç Tutar</td>
                                        <td class="tm_width_3 tm_primary_color tm_bold tm_text_right">fdf</td>
                                    </tr>
                                    <tr class="tm_gray_bg">
                                        <td class="tm_width_3 tm_primary_color">Katma Değer Vergisi(KDV)</td>
                                        <td class="tm_width_3 tm_primary_color tm_text_right">fdsf</td>
                                    </tr>
                                    <tr class="">
                                        <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 ">Toplam</td>
                                        <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16  tm_text_right">dfs</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                    </div>
                    <div class="tm_invoice_footer tm_type1 mt_10">
                        <div class="tm_left_footer">
                            Tel :2163064326 / Mail : info@berkaisguvenligi.com.tr / Web Site :
                            www.berkaisguvenligi.com.tr
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
