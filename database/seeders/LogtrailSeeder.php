<?php

namespace Database\Seeders;

use App\Models\logtrail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogtrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete logtrail");
        DB::statement("DBCC CHECKIDENT ('logtrail', RESEED, 1);");

        logtrail::create([ 'namatabel' => 'INVOICEHEADER', 'postingdari' => 'ENTRY INVOICE HEADER', 'idtrans' => '2', 'nobuktitrans' => 'INV 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '{"tglbukti":"2023-02-10","nominal":5023000,"tglterima":"2023-02-10","tgljatuhtempo":"2023-04-05","agen_id":"64","jenisorder_id":"2","piutang_nobukti":"EPT 0002\/II\/2023","statusapproval":4,"userapproval":"","tglapproval":"","statuscetak":175,"tgldari":"2023-02-01","tglsampai":"2023-02-02","modifiedby":"ADMIN","statusformat":"151","nobukti":"INV 0002\/II\/2023","updated_at":"05-04-2023 11:10:29","created_at":"05-04-2023 11:10:29","id":2}', 'modifiedby' => 'ADMIN',]);
        logtrail::create([ 'namatabel' => 'INVOICEDETAIL', 'postingdari' => 'ENTRY INVOICE DETAIL', 'idtrans' => '1', 'nobuktitrans' => 'INV 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '[{"invoice_id":2,"nobukti":"INV 0002\/II\/2023","nominal":"1432000.0","nominalextra":"0","nominalretribusi":"716000","total":2148000,"keterangan":"","orderantrucking_nobukti":"0077\/II\/23","suratpengantar_nobukti":"TRP 0034\/II\/2023","modifiedby":"ADMIN","updated_at":"05-04-2023 11:10:29","created_at":"05-04-2023 11:10:29","id":33},{"invoice_id":2,"nobukti":"INV 0002\/II\/2023","nominal":"2875000.0","nominalextra":"0","nominalretribusi":"0","total":2875000,"keterangan":"","orderantrucking_nobukti":"0078\/II\/23","suratpengantar_nobukti":"TRP 0073\/II\/2023,TRP 0081\/II\/2023,TRP 0117\/II\/2023","modifiedby":"ADMIN","updated_at":"05-04-2023 11:10:29","created_at":"05-04-2023 11:10:29","id":34}]', 'modifiedby' => 'ADMIN',]);
        logtrail::create([ 'namatabel' => 'PIUTANGHEADER', 'postingdari' => 'ENTRY INVOICE', 'idtrans' => '49', 'nobuktitrans' => 'EPT 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '{"nobukti":"EPT 0002\/II\/2023","tglbukti":"2023-02-10","postingdari":"ENTRY INVOICE","invoice_nobukti":"INV 0002\/II\/2023","modifiedby":"ADMIN","statusformat":"124","agen_id":"64","coadebet":"01.03.01.02","coakredit":"06.01.01.02","statuscetak":175,"userbukacetak":"","tglbukacetak":"","nominal":5023000,"updated_at":"05-04-2023 11:10:30","created_at":"05-04-2023 11:10:30","id":49}', 'modifiedby' => 'ADMIN',]);
        logtrail::create([ 'namatabel' => 'PIUTANGDETAIL', 'postingdari' => 'ENTRY INVOICE', 'idtrans' => '3', 'nobuktitrans' => 'EPT 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '[{"piutang_id":49,"nobukti":"EPT 0002\/II\/2023","nominal":5023000,"keterangan":"TAGIHAN INVOICE BONGKARAN TAS AP PERIODE 01-02-2023 S\/D 02-02-2023","invoice_nobukti":"INV 0002\/II\/2023","modifiedby":"ADMIN","updated_at":"05-04-2023 11:10:30","created_at":"05-04-2023 11:10:30","id":80}]', 'modifiedby' => 'ADMIN',]);
        logtrail::create([ 'namatabel' => 'JURNALUMUMHEADER', 'postingdari' => 'ENTRY INVOICE', 'idtrans' => '422', 'nobuktitrans' => 'EPT 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '{"nobukti":"EPT 0002\/II\/2023","tglbukti":"2023-02-10","postingdari":"ENTRY INVOICE","statusapproval":4,"userapproval":"","tglapproval":"","statusformat":"0","modifiedby":"ADMIN","updated_at":"05-04-2023 11:10:30","created_at":"05-04-2023 11:10:30","id":422}', 'modifiedby' => 'ADMIN',]);
        logtrail::create([ 'namatabel' => 'JURNALUMUMDETAIL', 'postingdari' => 'ENTRY INVOICE', 'idtrans' => '5', 'nobuktitrans' => 'EPT 0002/II/2023', 'aksi' => 'ENTRY', 'datajson' => '[{"jurnalumum_id":422,"nobukti":"EPT 0002\/II\/2023","tglbukti":"2023-02-10","coa":"01.03.01.02","nominal":5023000,"keterangan":"TAGIHAN INVOICE BONGKARAN TAS AP PERIODE 01-02-2023 S\/D 02-02-2023","modifiedby":"ADMIN","baris":0,"updated_at":"05-04-2023 11:10:30","created_at":"05-04-2023 11:10:30","id":1593},{"jurnalumum_id":422,"nobukti":"EPT 0002\/II\/2023","tglbukti":"2023-02-10","coa":"06.01.01.02","nominal":"-5023000","keterangan":"TAGIHAN INVOICE BONGKARAN TAS AP PERIODE 01-02-2023 S\/D 02-02-2023","modifiedby":"ADMIN","baris":0,"updated_at":"05-04-2023 11:10:30","created_at":"05-04-2023 11:10:30","id":1594}]', 'modifiedby' => 'ADMIN',]);
    }
}
