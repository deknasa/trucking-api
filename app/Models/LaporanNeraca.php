<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanNeraca extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getReport($sampai)
    {
        $bulan = substr($sampai, 0, 2);
        $tahun = substr($sampai, -4);

        $tgl = $tahun . '-' . $bulan . '-02';
        $tgl1 = $tahun . '-' . $bulan . '-01';

        $tgl3 = date('Y-m-d', strtotime($tgl1 . ' +32 days'));


        $tahun2 = date('Y', strtotime($tgl3));
        $bulan2 = date('m', strtotime($tgl3));



        $ptgl = $tahun . '-' . $bulan . '-01';

        $tgldari = $ptgl;

        $datetime = $tahun2 . '-' . $bulan2 . '-1';

        $tglsd =  date('Y-m-d', strtotime($datetime . ' -1 day'));

        $judul = Parameter::where('grp', '=', 'JUDULAN LAPORAN')->first();
        $judulLaporan = $judul->text;


        DB::table('akunpusatdetail')
            ->where('bulan', '<>', 0)
            ->delete();


        $subquery1 = DB::table('jurnalumumpusatheader as J')
            ->select('D.coamain as FCOA', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
            ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
            ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
            ->where('D.tglbukti', '>=', $ptgl)
            ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

        $subquery2 = DB::table('jurnalumumpusatheader as J')
            ->select('LR.coa', DB::raw('YEAR(D.tglbukti) as FThn'), DB::raw('MONTH(D.tglbukti) as FBln'), DB::raw('SUM(D.nominal) as FNominal'))
            ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
            ->join('perkiraanlabarugi as LR', function ($join) {
                $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
                    ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'));
            })
            ->whereIn('D.coamain', function ($query) {
                $query->select(DB::raw('DISTINCT C.coa'))
                    ->from('maintypeakuntansi as AT')
                    ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
                    ->where('AT.order', '>=', 4000)
                    ->where('AT.order', '<', 9000)
                    ->where('C.type', '<>', 'Laba/Rugi');
            })
            ->where('D.tglbukti', '>=', $ptgl)
            ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

        $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
            ->mergeBindings($subquery1)
            ->mergeBindings($subquery2)
            ->groupBy('FCOA', 'FThn', 'FBln')
            ->select('FCOA', 'FThn', 'FBln', DB::raw('SUM(FNominal) as FNominal'));

        DB::table('akunpusatdetail')->insertUsing([
            'coa',
            'tahun',
            'bulan',
            'nominal',

        ], $RecalKdPerkiraan);

        $tempAkunPusatDetail = '##tempAkunPusatDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAkunPusatDetail, function ($table) {
            $table->bigIncrements('id');
            $table->string('coa', 50)->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->double('nominal')->nullable();
            $table->string('modifiedby')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryTempSaldoAkunPusatDetail = DB::table('saldoakunpusatdetail')->from(
            DB::raw('saldoakunpusatdetail')
        )
            ->select(
                'coa',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at'

            )
            ->orderBy('id', 'asc');

        DB::table($tempAkunPusatDetail)->insertUsing([
            'coa',
            'bulan',
            'tahun',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $queryTempSaldoAkunPusatDetail);

        $queryTempAkunPusatDetail = DB::table('akunpusatdetail')->from(
            DB::raw('akunpusatdetail')
        )
            ->select(
                'coa',
                'bulan',
                'tahun',
                'nominal',
                'modifiedby',
                'created_at',
                'updated_at'

            )
            ->orderBy('id', 'asc');

        DB::table($tempAkunPusatDetail)->insertUsing([
            'coa',
            'bulan',
            'tahun',
            'nominal',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $queryTempAkunPusatDetail);

        $data = DB::select(DB::raw("
                SELECT xx.TipeMaster, xx.[Order], xx.[Type], xx.KeteranganType, xx.coa, xx.Parent,
                xx.KeteranganCoa, xx.Nominal, xx.CmpyName, xx.pBulan, xx.pTahun,
                xx.GNeraca, xx.GLR, xx.KeteranganCoaParent, xx.pTglSd
        FROM
        (
            SELECT CASE d.akuntansi_id WHEN 1 THEN 'AKTIVA' ELSE 'PASSIVA' END AS TipeMaster,
                d.[order], MAX(d.[Type]) AS Type, MAX(d.keterangantype) AS KeteranganType,
                d.coa, MAX(d.Parent) AS Parent,
                d.Keterangancoa,
                CASE d.akuntansi_id WHEN 1 THEN SUM(d.Nominal) ELSE SUM(d.Nominal * -1) END AS Nominal,
                '$judulLaporan' AS CmpyName,
                MAX($bulan) AS pBulan, MAX($tahun) AS pTahun,
                MAX(d.statusneraca) AS GNeraca, MAX(d.statuslabarugi) AS GLR,
                (SELECT KeteranganCoa FROM akunpusat WHERE coa = MAX(d.Parent)) AS KeteranganCoaParent,
                '$tglsd' AS pTglSd
            FROM
            (
                SELECT C.[type], C.coa, C.keterangancoa,
                    C.Parent, C.statusaktif, C.statusneraca, C.statuslabarugi,
                    ISNULL(cd.tahun, $tahun) AS Tahun,
                    ISNULL(cd.bulan, 0) AS Bulan,
                    ISNULL(cd.nominal, 0) AS Nominal,
                    A.[Order], A.keterangantype, A.akuntansi_id
                FROM mainakunpusat C
                LEFT OUTER JOIN $tempAkunPusatDetail cd ON C.coa = cd.coa
                INNER JOIN maintypeakuntansi A ON A.[kodetype] = C.[type]
            ) d
            WHERE (d.Tahun = $tahun) AND (d.Bulan <= $bulan) AND (d.[Order] < 4000)
            GROUP BY d.akuntansi_id, d.[order], d.coa, d.keterangancoa
            HAVING SUM(d.Nominal) <> 0
        ) xx
        "));


        return $data;
    }
}
