<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = "penerimaantruckingdetail";

    protected $casts = [
        "created_at" => "date:d-m-Y H:i:s",
        "updated_at" => "date:d-m-Y H:i:s"
    ];

    protected $guarded = [
        "id",
        "created_at",
        "updated_at",
    ];


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->penerimaantruckingheader_id)) {
            $query->where("$this->table.penerimaantruckingheader_id", request()->penerimaantruckingheader_id);
        }

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "supir.namasupir as supir_id",
                "karyawan.namakaryawan as karyawan_id",
                "$this->table.pengeluarantruckingheader_nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",
            )
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), "$this->table.karyawan_id", "karyawan.id")
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "$this->table.supir_id", "supir.id");
        } else {
            $query->select(
                "$this->table.nobukti",
                "$this->table.nominal",
                "$this->table.keterangan",

                "karyawan.namakaryawan as karyawan_id",
                "supir.namasupir as supir_id",
                "$this->table.pengeluarantruckingheader_nobukti",
                db::raw("cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluarantruckingheader"),
                db::raw("cast(cast(format((cast((format(pengeluarantruckingheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluarantruckingheader"), 
            )
                ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', '=', 'pengeluarantruckingheader.nobukti')
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), "$this->table.karyawan_id", "karyawan.id")
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "$this->table.supir_id", "supir.id");
            $this->totalNominal = $query->sum('nominal');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function getAll($id)
    {
        $query = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(
                "penerimaantruckingdetail.penerimaantruckingheader_id",
                "penerimaantruckingdetail.nominal",
                "penerimaantruckingdetail.keterangan",
                "penerimaantruckingdetail.pengeluarantruckingheader_nobukti",

                "supir.namasupir as supir",
                "supir.id as supir_id",
                "karyawan.namakaryawan as karyawandetail",
                "karyawan.id as karyawan_id"
            )
            ->leftJoin("supir", "penerimaantruckingdetail.supir_id", "supir.id")
            ->leftJoin("karyawan", "penerimaantruckingdetail.karyawan_id", "karyawan.id")
            ->where("penerimaantruckingdetail.penerimaantruckingheader_id", "=", $id);


        $data = $query->get();

        return $data;
    }

    public function getPotSemua($nobukti)
    {
        $fetch =  DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("penerimaantrucking_nobukti"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->whereRaw("supir_id = 0")
            ->first();

        if ($fetch != null) {
            $this->setRequestParameters();

            $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    'penerimaantruckingdetail.nobukti',
                    'penerimaantruckingdetail.pengeluarantruckingheader_nobukti',
                    "penerimaantruckingdetail.nominal",
                    "penerimaantruckingdetail.keterangan",
                    'supir.namasupir as supir_id'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "penerimaantruckingdetail.supir_id", "supir.id")
                ->where('nobukti', $fetch->penerimaantrucking_nobukti);


            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalNominalPotSemua = $query->sum('nominal');
            return $query->get();
        } else {
            $this->totalNominalPotSemua = 0;
        }
    }

    public function getPotPribadi($nobukti)
    {
        $fetch =  DB::table('gajisupirpelunasanpinjaman')->from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->select(DB::raw("penerimaantrucking_nobukti"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->whereRaw("supir_id != 0")
            ->first();

        if ($fetch != null) {
            $this->setRequestParameters();

            $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    'penerimaantruckingdetail.nobukti',
                    'penerimaantruckingdetail.pengeluarantruckingheader_nobukti',
                    "penerimaantruckingdetail.nominal",
                    "penerimaantruckingdetail.keterangan",
                    'supir.namasupir as supir_id'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "penerimaantruckingdetail.supir_id", "supir.id")
                ->where('nobukti', $fetch->penerimaantrucking_nobukti);

            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalNominalPotPribadi = $query->sum('nominal');
            return $query->get();
        } else {
            $this->totalNominalPotPribadi = 0;
        }
    }

    public function getDeposito($nobukti)
    {
        $deposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
            ->select(
                'penerimaantrucking_nobukti'
            )
            ->where('gajisupir_nobukti', $nobukti)->first();
        if ($deposito != null) {

            $this->setRequestParameters();

            $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    'penerimaantruckingdetail.nobukti',
                    'penerimaantruckingdetail.pengeluarantruckingheader_nobukti',
                    "penerimaantruckingdetail.nominal",
                    "penerimaantruckingdetail.keterangan",
                    'supir.namasupir as supir_id'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "penerimaantruckingdetail.supir_id", "supir.id")
                ->where('nobukti', $deposito->penerimaantrucking_nobukti);

            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);
            // dd($query->toSql());
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalNominalDeposito = $query->sum('nominal');

            return $query->get();
        } else {
            $this->totalNominalDeposito = 0;
        }
    }

    public function getBBM($nobukti)
    {
        $bbm = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
            ->select(
                'penerimaantrucking_nobukti'
            )
            ->where('gajisupir_nobukti', $nobukti)->first();
        if ($bbm != null) {

            $this->setRequestParameters();

            $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
                ->select(
                    'penerimaantruckingdetail.nobukti',
                    'penerimaantruckingdetail.pengeluarantruckingheader_nobukti',
                    "penerimaantruckingdetail.nominal",
                    "penerimaantruckingdetail.keterangan",
                    'supir.namasupir as supir_id'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), "penerimaantruckingdetail.supir_id", "supir.id")
                ->where('nobukti', $bbm->penerimaantrucking_nobukti);

            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);
            // dd($query->toSql());
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalNominalBBM = $query->sum('nominal');

            return $query->get();
        } else {
            $this->totalNominalBBM = 0;
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'karyawan_id') {
                                $query = $query->where('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'karyawan_id') {
                                $query = $query->orWhere('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }
        }
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(PenerimaanTruckingHeader $penerimaanTruckingHeader, array $data): PenerimaanTruckingDetail
    {

        $penerimaantruckingDetail = new PenerimaanTruckingDetail();

        $penerimaantruckingDetail->penerimaantruckingheader_id = $data['penerimaantruckingheader_id'];
        $penerimaantruckingDetail->nobukti = $data['nobukti'];
        $penerimaantruckingDetail->supir_id = $data['supir_id'];
        $penerimaantruckingDetail->karyawan_id = $data['karyawan_id'];
        $penerimaantruckingDetail->pengeluarantruckingheader_nobukti = $data['pengeluarantruckingheader_nobukti'] ?? '';
        $penerimaantruckingDetail->keterangan = mb_convert_encoding($data['keterangan'],  'ISO-8859-1', 'UTF-8');
        $penerimaantruckingDetail->nominal = $data['nominal'];
        $penerimaantruckingDetail->modifiedby = auth('api')->user()->name;
        $penerimaantruckingDetail->info = html_entity_decode(request()->info);

        if (!$penerimaantruckingDetail->save()) {
            throw new \Exception("Error storing Penerimaan Trucking Detail.");
        }

        return $penerimaantruckingDetail;
    }
}
