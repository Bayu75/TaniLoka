<?php

return [

    /*
    | Komisi TaniLoka dalam persen dari SUBTOTAL PRODUK (bukan dari ongkir/PPN).
    | Ubah lewat env TANILOKA_KOMISI_PERSEN (lokal: .env, Railway: Variables).
    */
    'komisi_persen' => (float) env('TANILOKA_KOMISI_PERSEN', 10),

    /*
    | true  = ongkir ikut jadi hak petani (petani yang mengantar sendiri).
    | false = ongkir tidak masuk bagian petani.
    */
    'ongkir_ke_petani' => (bool) env('TANILOKA_ONGKIR_KE_PETANI', true),

];