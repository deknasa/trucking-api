<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SuratPengantarBiayaTambahan extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantarbiayatambahan';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.id as id_header',
                'header.nobukti as nobukti_header',
                'header.tglbukti as tgl_header',
                'header.tgljatuhtempo as tgljatuhtempo',
                'header.keterangan as keterangan_header',
                'header.invoice_nobukti as invoice_nobukti',
                'agen.namaagen as agen_id',
                $this->table . '.keterangan as keterangan_detail',
                $this->table . '.nominal',
                $this->table . '.invoice_nobukti as invoice_nobukti_detail'
            )
                ->leftJoin('piutangheader as header', 'header.id',  $this->table . '.piutang_id')
                ->leftJoin('agen', 'header.agen_id', 'agen.id');

            $query->where($this->table . '.piutang_id', '=', request()->piutang_id);
        } else {
            $query->select(
                'suratpengantar.nobukti',
                $this->table . '.id',
                $this->table . '.keteranganbiaya',
                $this->table . '.nominal',
                $this->table . '.nominaltagih',
                'parameter.memo as statusapproval',
                'suratpengantarbiayatambahan.userapproval',
                DB::raw('(case when (year(suratpengantarbiayatambahan.tglapproval) <= 2000) then null else suratpengantarbiayatambahan.tglapproval end ) as tglapproval'),

            )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'suratpengantarbiayatambahan.statusapproval', 'parameter.id');

            $this->sort($query);
            $query->where($this->table . '.suratpengantar_id', '=', request()->suratpengantar_id);
            $this->filter($query);
            $this->totalNominal = $query->sum('nominal');
            $this->totalNominalTagih = $query->sum('nominaltagih');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nobukti') {
            return $query->orderBy('suratpengantar.nobukti', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'nobukti') {
                                    $query = $query->where('suratpengantar.nobukti', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->where('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->whereRaw("format(suratpengantarbiayatambahan.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominaltagih') {
                                    $query = $query->whereRaw("format(suratpengantarbiayatambahan.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'nobukti') {
                                    $query = $query->orWhere('suratpengantar.nobukti', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(suratpengantarbiayatambahan.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominaltagih') {
                                    $query = $query->orWhereRaw("format(suratpengantarbiayatambahan.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
                            }
                        }
                    });
                    break;
                default:

                    break;
            }


            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function findUpdate($id)
    {
        $data = SuratPengantarBiayaTambahan::from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))->select(
            'suratpengantarbiayatambahan.id',
            'suratpengantarbiayatambahan.keteranganbiaya',
            'suratpengantarbiayatambahan.nominal',
            'suratpengantarbiayatambahan.nominaltagih',
            'suratpengantarbiayatambahan.modifiedby',
        )
            ->where('suratpengantarbiayatambahan.id', $id)->first();

        return $data;
    }

    public function processStore(SuratPengantar $suratPengantar, array $data): SuratPengantarBiayaTambahan
    {
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();
        $suratpengantarbiayatambahan->suratpengantar_id = $suratPengantar->id;
        $suratpengantarbiayatambahan->keteranganbiaya = $data['keteranganbiaya'];
        $suratpengantarbiayatambahan->nominal = $data['nominal'];
        $suratpengantarbiayatambahan->nominaltagih = $data['nominaltagih'];
        $suratpengantarbiayatambahan->statusapproval = $statusNonApproval->id;

        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
        $suratpengantarbiayatambahan->info = html_entity_decode(request()->info);

        if (!$suratpengantarbiayatambahan->save()) {
            throw new \Exception("Error storing surat pengantar biaya tambahan.");
        }

        return $suratpengantarbiayatambahan;
    }

    public function processUpdate($id, array $data): SuratPengantarBiayaTambahan
    {
        $suratpengantarbiayatambahan = SuratPengantarBiayaTambahan::findUpdate($id);
        $suratpengantarbiayatambahan->keteranganbiaya = $data['keteranganbiaya'];
        $suratpengantarbiayatambahan->nominal = $data['nominal'];
        $suratpengantarbiayatambahan->nominaltagih = $data['nominaltagih'];

        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
        $suratpengantarbiayatambahan->info = html_entity_decode(request()->info);

        if (!$suratpengantarbiayatambahan->save()) {
            throw new \Exception("Error update surat pengantar biaya tambahan.");
        }

        return $suratpengantarbiayatambahan;
    }


    public function processDestroy($id): SuratPengantarBiayaTambahan
    {
        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();
        $suratpengantarbiayatambahan = $suratpengantarbiayatambahan->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => $suratpengantarbiayatambahan->getTable(),
            'postingdari' => 'EDIT SURATPENGANTAR',
            'idtrans' => $suratpengantarbiayatambahan->id,
            'nobuktitrans' => $suratpengantarbiayatambahan->id,
            'aksi' => 'DELETE',
            'datajson' => $suratpengantarbiayatambahan->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $suratpengantarbiayatambahan;
    }

    public function processApproval(array $data)
    {

        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['id']); $i++) {

            $getBiayaTambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
                ->where('suratpengantar_id', $data['id'][$i])->get();
            for ($a = 0; $a < count($getBiayaTambahan); $a++) {
                $suratPengantarTambahan = SuratPengantarBiayaTambahan::find($getBiayaTambahan[$a]->id);


                if ($suratPengantarTambahan->statusapproval == $statusApproval->id) {
                    $suratPengantarTambahan->statusapproval = $statusNonApproval->id;
                    $suratPengantarTambahan->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                    $suratPengantarTambahan->userapproval = '';
                    $aksi = $statusNonApproval->text;
                } else {
                    $suratPengantarTambahan->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                    $suratPengantarTambahan->tglapproval = date('Y-m-d H:i:s');
                    $suratPengantarTambahan->userapproval = auth('api')->user()->name;
                    $suratPengantarTambahan->info = html_entity_decode(request()->info);
                }

                if (!$suratPengantarTambahan->save()) {
                    throw new \Exception("Error approval surat pengantar biaya tambahan.");
                }
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($suratPengantarTambahan->getTable()),
                    'postingdari' => 'APPROVAL SURAT PENGANTAR BIAYA TAMBAHAN',
                    'idtrans' => $suratPengantarTambahan->id,
                    'nobuktitrans' => $suratPengantarTambahan->suratpengantar_id,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantarTambahan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }

        return $suratPengantarTambahan;
    }

    public function deleteRow(array $data)
    {
        $id = $data['id'];
        $cekStatus = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))->where('id', $id)->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        if ($cekStatus != '') {

            if ($cekStatus->statusapproval == $statusNonApproval->id) {
                $status = true;
            } else {
                $status = false;
            }
        } else {
            $status = true;
        }

        return $status;
    }
}
