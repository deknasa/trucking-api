<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportNeraca extends MyModel
{
    use HasFactory;

    public function getReport($tgldr, $tglsd, $coadr, $coasd)
    {

        
        $query = DB::select("
                    SELECT A.*,
                    $tgldr AS TglDr,$tglsd AS TglSd,
                    $coadr AS KdPerkDr,$coasd AS KdPerkSd,
                    ISNULL((SELECT keterangancoa FROM akunpusat WHERE COA=$coadr),'SEMUA') AS NmPerkDr,
                    ISNULL((SELECT keterangancoa FROM akunpusat WHERE COA=$coasd),'SEMUA') AS NmPerkSd
                    FROM
                    (
                    SELECT 1 AS Urut,0 AS ID,'SALDO AWAL' AS FNTrans,Null AS FTgl,CM.COA,
                        CM.keterangancoa  ,0 AS FDebet,0 AS FCredit,SUM(Nominal) AS FSaldo,'' AS FKetD,
                        0 AS FOrder,0 AS FSeqTime
                    FROM jurnalumumpusatdetail D
                    INNER JOIN jurnalumumpusatheader H ON H.nobukti=D.nobukti
                    INNER JOIN akunpusat CM ON CM.COA=D.COA
                    WHERE (D.tglbukti < $tgldr) AND 
                        ((CM.COA BETWEEN $coadr AND $coasd) OR 
                            $coadr='SEMUA' AND $coasd='SEMUA')
                        
                    GROUP BY CM.COA,CM.keterangancoa
                    
                    UNION ALL
                    
                    SELECT 2 AS Urut,D.jurnalumumpusat_id as ID,D.nobukti,D.tglbukti,
                        CM.COA,CM.keterangancoa ,CASE SIGN(D.Nominal) WHEN 1 THEN D.Nominal ELSE 0 END AS FDebet,
                        CASE SIGN(D.Nominal) WHEN 1 THEN 0 ELSE D.Nominal END AS FCredit,D.Nominal AS FSaldo,
                        CASE D.Keterangan WHEN '' THEN H.Keterangan ELSE D.Keterangan END AS FKetD,0 as FORder,0 as FSeqTime
                    FROM jurnalumumpusatdetail D 
                    INNER JOIN jurnalumumpusatheader H ON H.nobukti=D.nobukti
                    INNER JOIN akunpusat CM ON CM.COA=D.COA
                    WHERE ((CM.COA BETWEEN $coadr AND $coasd) OR 
                                ($coadr='SEMUA' AND $coasd='SEMUA')) AND
                        (D.TglBukti BETWEEN $tgldr AND $tglsd)
                    )A
                    ORDER BY A.coa,A.Urut
                ");


        return response([
            'data' => $query,
            'user' => auth('api')->user()->name
        ]);
    }
}
