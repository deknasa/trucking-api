<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanSupplierBandingStok extends Model
{
    use HasFactory;

    public function getStokBySupplier($supplier_id) {
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        $query = DB::table('stok')
        
        ->select(
            'stok.id',
            // 'stok.jenistrado_id',
            // 'stok.kelompok_id',
            // 'stok.subkelompok_id',
            // 'stok.kategori_id',
            // 'stok.merk_id',
            // 'stok.satuan_id',
            'stok.namastok',
            // 'stok.statusaktif',
            // 'stok.statusreuse',
            // 'stok.statusban',
            'penerimaanstokheader.tglbukti as tanggal',
            'penerimaanstokdetail.nobukti',
            'penerimaanstokdetail.harga',
            'penerimaanstokdetail.qty',
            'penerimaanstokdetail.total',
            // 'stok.statusservicerutin',
            // 'stok.qtymin',
            // 'stok.qtymax',
            // 'stok.hargabelimin',
            // 'stok.hargabelimax',
            // 'stok.vulkanisirawal',
            // 'stok.totalvulkanisir',
            // 'stok.keterangan',
            // 'stok.gambar',
            // 'stok.namaterpusat',
            // 'stok.statusapprovaltanpaklaim',
            // 'stok.userapprovaltanpaklaim',
            // 'stok.tglapprovaltanpaklaim',
            // 'stok.info',
            // 'stok.tas_id',
            // 'stok.editing_by',
            // 'stok.editing_at',
            // 'stok.modifiedby',
            // 'stok.statuspembulatanlebih2decimal',
            // 'stok.created_at',
            // 'stok.updated_at',
        )
        ->leftJoin('penerimaanstokdetail', 'penerimaanstokdetail.stok_id', '=', 'stok.id')
        ->leftJoin('penerimaanstokheader', 'penerimaanstokheader.id', '=', 'penerimaanstokdetail.penerimaanstokheader_id')
        ->where('penerimaanstokheader.penerimaanstok_id', $spb->text)
        ->where('penerimaanstokheader.supplier_id', $supplier_id)
        ->orderBy('stok.id','asc')
        ->orderBy('penerimaanstokdetail.nobukti','asc');
        return $query->get();
    }
}
