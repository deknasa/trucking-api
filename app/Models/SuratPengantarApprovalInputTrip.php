<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarApprovalInputTrip extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantarapprovalinputtrip';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function get()
    {
        $this->setRequestParameters();

        //         select b.approvalbukatanggal_id, count(nobukti) as jumlah from suratpengantar as b
        // where b.approvalbukatanggal_id != 0
        // group by b.approvalbukatanggal_id
        $tempTerpakai = '##tempTerpakai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTerpakai, function ($table) {
            $table->integer('approvalbukatanggal_id')->nullable();
            $table->integer('terpakai')->nullable();
        });

        $queryHitungTrip = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar with (readuncommitted)")
        )
            ->select(
                'approvalbukatanggal_id',
                DB::raw("count(nobukti) as terpakai")
            )
            ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
            ->groupby('approvalbukatanggal_id');

        DB::table($tempTerpakai)->insertUsing([
            'approvalbukatanggal_id',
            'terpakai',
        ], $queryHitungTrip);

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "suratpengantarapprovalinputtrip.id",
            "suratpengantarapprovalinputtrip.tglbukti",
            "suratpengantarapprovalinputtrip.tglbatas",
            "suratpengantarapprovalinputtrip.jumlahtrip",
            'parameter.memo as statusapproval',
            DB::raw("[user].[name] as user_id"),
            "suratpengantarapprovalinputtrip.modifiedby",
            "suratpengantarapprovalinputtrip.created_at",
            "suratpengantarapprovalinputtrip.updated_at",
            DB::raw("isnull(b.terpakai,0) as terpakai")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'suratpengantarapprovalinputtrip.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("$tempTerpakai as b with (readuncommitted)"), 'suratpengantarapprovalinputtrip.id', 'b.approvalbukatanggal_id')
            ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'suratpengantarapprovalinputtrip.user_id', 'user.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($id, $Aksi)
    {
        if ($Aksi == 'DELETE') {

            $suratPengantar = DB::table('suratpengantar')
                ->from(
                    DB::raw("suratpengantar as a with (readuncommitted)")
                )
                ->select(
                    'a.approvalbukatanggal_id'
                )
                ->where('a.approvalbukatanggal_id', '=', $id)
                ->first();
            if (isset($suratPengantar)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'Surat Pengantar',
                    'kodeerror' => 'SATL'
                ];
                goto selesai;
            }
        } else {
            $getTglBukti = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))->where('id', $id)->first();
            $cek = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                ->where('tglbukti', $getTglBukti->tglbukti)
                ->where('user_id', $getTglBukti->user_id)
                ->where('id', '>', $id)
                ->first();

            if (isset($cek)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => date('d-m-Y', strtotime($getTglBukti->tglbukti)),
                    'kodeerror' => 'ABTT'
                ];
                goto selesai;
            }

            $tanggal = date('Y-m-d', strtotime('+1 days')) . ' ' . '10:00:00';
            $cek = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                ->whereRaw("a.tglbatas<getdate()")
                ->where('id', $id)
                ->first();

            if (isset($cek)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => date('d-m-Y H:i:s', strtotime($getTglBukti->tglbatas)),
                    'kodeerror' => 'TBABT'
                ];
                goto selesai;
            }
        }
        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusapproval')->nullable();
            $table->string('statusapprovalnama')->nullable();
        });

        $statusapproval = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id','text'
            )
            ->where('grp', '=', 'STATUS APPROVAL')
            ->where('subgrp', '=', 'STATUS APPROVAL')
            ->where('text', '=', 'APPROVAL')
            ->first();

        DB::table($tempdefault)->insert(["statusapproval" => $statusapproval->id,"statusapprovalnama" => $statusapproval->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusapprovalnama',
                'statusapproval'
            );

        $data = $query->first();
        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.tglbukti',
                'a.jumlahtrip',
                'a.statusapproval',
                'parameter.text as statusapprovalnama',
                'a.user_id',
                DB::raw("[user].[name] as [user]")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'a.user_id', 'user.id')
            ->where('a.id', $id)
            ->first();

        return $query;
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else if ($filters['field'] == 'user_id') {
                            $query = $query->where('user.name', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'terpakai') {
                            $query = $query->whereRaw("isnull(b.terpakai,0) LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'user_id') {
                            $query = $query->orWhere('user.name', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'terpakai') {
                            $query = $query->orWhereRaw("isnull(b.terpakai,0) LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function isTanggalAvaillable()
    {

        $tutupbuku = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->where('a.grp', '=', 'TUTUP BUKU')
            ->where('a.subgrp', '=', 'TUTUP BUKU')
            ->first();
        $approval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "APPROVAL")->first();
        // $bukaAbsensi = DB::table('suratpengantar')
        // ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
        // ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip FROM suratpengantarapprovalinputtrip GROUP BY tglbukti) AS subquery'), 'suratpengantar.tglbukti', '=', 'subquery.tglbukti')
        // ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
        // ->where('suratpengantar.tglbukti', '>', '2022-12-25')

        // ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')
        // ->get();


        // $bukaAbsensi = DB::table('suratpengantar')
        //     ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip, statusapproval FROM suratpengantarapprovalinputtrip GROUP BY tglbukti,statusapproval) as subquery'), function ($join) {
        //         $join->on('suratpengantar.tglbukti', '=', 'subquery.tglbukti');
        //     })
        //     ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
        //     ->where('suratpengantar.tglbukti', '>', $tutupbuku->text)
        //     ->where('subquery.statusapproval', $approval->id)
        //     ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
        //     ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')

        //     ->get();

        $bukaAbsensi = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->select(DB::raw("suratpengantarapprovalinputtrip.tglbukti, count(suratpengantar.approvalbukatanggal_id) as jumlah"))
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantarapprovalinputtrip.id', "suratpengantar.approvalbukatanggal_id")
            ->where('suratpengantarapprovalinputtrip.tglbukti', '>', $tutupbuku->text)
            ->where('suratpengantarapprovalinputtrip.statusapproval', $approval->id)
            ->groupBy('suratpengantarapprovalinputtrip.tglbukti')
            ->get();

        return $bukaAbsensi;
    }

    public function validasiTanggalTrip($tanggal)
    {

        $error = new Error();
        $keterangantambahan = $error->cekKeteranganError('SHP') ?? '';
        $bukaAbsensi = SuratPengantarApprovalInputTrip::where('tglbukti', '=', $tanggal)
            ->orderBy('id', 'desc')
            ->first();
        $today = date('Y-m-d', strtotime("today"));
        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first();
        $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;

        if ($tanggal == $today) {
            $data = [
                'status' => true,
                'keterangan' => ''
            ];
            return $data;
        }
        // if ($getFormat->text == 'FORMAT 2') {
        if (date('Y-m-d', strtotime($tanggal . "+$getBatasHari days")) . ' ' . $getBatasInput->text > date('Y-m-d H:i:s')) {
            $data = [
                'status' => true,
                'keterangan' => ''
            ];
            return $data;
        }
        $user_id = auth('api')->user()->id;
        if ($bukaAbsensi) {
            // GET APPROVAL INPUTTRIP
            $tempApp = '##tempApp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempApp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->date('tglbukti')->nullable();
                $table->unsignedBigInteger('jumlahtrip')->nullable();
                $table->unsignedBigInteger('statusapproval')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->datetime('tglbatas')->nullable();
            });

            $querybukaabsen = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
                ->select('id', 'tglbukti', 'jumlahtrip', 'statusapproval', 'user_id', 'tglbatas')
                ->where('tglbukti', $tanggal);
            DB::table($tempApp)->insertUsing([
                'id',
                'tglbukti',
                'jumlahtrip',
                'statusapproval',
                'user_id',
                'tglbatas',
            ],  $querybukaabsen);

            // GET MANDOR DETAIL
            $tempMandor = '##tempMandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempMandor, function ($table) {
                $table->id();
                $table->unsignedBigInteger('mandor_id')->nullable();
            });

            $querymandor = DB::table("mandordetail")->from(DB::raw("mandordetail with (readuncommitted)"))
                ->select('mandor_id')->where('user_id', $user_id);
            DB::table($tempMandor)->insertUsing([
                'mandor_id',
            ],  $querymandor);


            // BUAT TEMPORARY SP GROUP BY TEMPO ID
            $tempSP = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempSP, function ($table) {
                $table->id();
                $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
                $table->unsignedBigInteger('jumlahtrip')->nullable();
            });

            $querySP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('approvalbukatanggal_id', DB::raw("count(nobukti) as jumlahtrip"))
                ->where('tglbukti', $tanggal)
                ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
                ->groupBy('approvalbukatanggal_id');

            DB::table($tempSP)->insertUsing([
                'approvalbukatanggal_id',
                'jumlahtrip'
            ],  $querySP);
            $getAll = DB::table("mandordetail")->from(DB::raw("mandordetail as a"))
                ->select('a.mandor_id', 'c.id', 'c.user_id', 'c.statusapproval', 'c.tglbatas', 'c.jumlahtrip', 'e.namamandor')
                ->leftJoin(DB::raw("$tempMandor as b with (readuncommitted)"), 'a.mandor_id', 'b.mandor_id')
                ->leftJoin(DB::raw("$tempApp as c with (readuncommitted)"), 'a.user_id', 'c.user_id')
                ->leftJoin(DB::raw("$tempSP as d with (readuncommitted)"), 'c.id', 'd.approvalbukatanggal_id')
                ->leftjoin(db::raw("mandor e "), 'a.mandor_id', 'e.id')
                ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
                ->whereRaw('COALESCE(c.user_id, 0) <> 0')
                ->whereRaw('isnull(d.jumlahtrip,0) <= c.jumlahtrip')
                ->orderBy('c.tglbatas', 'desc')
                ->first();
            if (isset($getAll)) {
                if ($user_id != $getAll->user_id) {
                    $querycekuser = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
                        ->select(
                            'a.user_id'
                        )
                        ->where('a.user_id', $user_id)
                        ->first();
                    if (!isset($querycekuser)) {
                        $keteranganerror = $error->cekKeteranganError('TSTB') ?? '';
                        $keteranganerror2 = $error->cekKeteranganError('BBA') ?? '';
                        $data = [
                            'status' => false,
                            'keterangan' => $keteranganerror . '<br>' . $keteranganerror2 . '<br><b>' . $keterangantambahan . '</b>'
                        ];
                        return $data;
                    }
                }
            }
            if ($getAll == '') {

                $keteranganerror = $error->cekKeteranganError('TSTB') ?? '';
                $keteranganerror2 = $error->cekKeteranganError('BBA') ?? '';
                $data = [
                    'status' => false,
                    'keterangan' => $keteranganerror . '<br>' . $keteranganerror2 . '<br><b>' . $keterangantambahan . '</b>'
                ];
                return $data;
            }

            $now = date('Y-m-d');
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
            if ($getAll->statusapproval == $nonApproval->id) {
                $data = [
                    'status' => false,
                    'keterangan' => 'APPROVAL BUKA TANGGAL TRIP BELUM DI APPROVAL <br><b>' . $keterangantambahan . '</b>'
                ];
                return $data;
            }

            $suratPengantar = SuratPengantar::where('tglbukti', '=', $tanggal)->whereRaw("approvalbukatanggal_id = $getAll->id")->count();
            // dd($getAll,);
            $now = date('Y-m-d H:i:s');
            if ($now > $getAll->tglbatas) {

                $keteranganerror = $error->cekKeteranganError('LB') ?? '';
                $data = [
                    'status' => false,
                    'keterangan' => 'APPROVAL BUKA TANGGAL TRIP UNTUK <b>' . date('d-m-Y', strtotime($tanggal)) . "</b> $keteranganerror INPUT <b>" . date('d-m-Y H:i:s', strtotime($getAll->tglbatas)) . '</b><br><b>' . $keterangantambahan . '</b>'
                ];
                return $data;
            }

            if ($getAll->jumlahtrip < ($suratPengantar + 1)) {
                $keteranganerror = $error->cekKeteranganError('KISH') ?? '';
                $data = [
                    'status' => false,
                    'keterangan' => $keteranganerror . '<br>kuota : ' . $getAll->jumlahtrip . '<br> terpakai : ' . $suratPengantar . '<br><b>' . $keterangantambahan . '</b>'
                ];
                return $data;
            }

            $data = [
                'status' => true,
                'keterangan' => ''
            ];
            return $data;
        } else {
            $batasHari = $getBatasHari;
            $date = date('Y-m-d', strtotime($tanggal));

            $kondisi = true;
            // if ($getBatasHari != 0) {

            while ($kondisi) {
                $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))
                    ->where('tgl', $date)
                    ->first();

                $todayIsSunday = date('l', strtotime($date));
                $tomorrowIsSunday = date('l', strtotime($date . "+1 days"));
                if ($cekHarilibur == '') {
                    $kondisi = false;
                    $allowed = true;
                    if (strtolower($todayIsSunday) == 'sunday') {
                        $kondisi = true;
                        $batasHari += 1;
                    }
                    if (strtolower($tomorrowIsSunday) == 'sunday') {
                        $kondisi = true;
                        $batasHari += 1;
                    }
                } else {
                    $batasHari += 1;
                }
                $date = date('Y-m-d', strtotime($tanggal . "+$batasHari days"));
            }
            // }
            if (date('Y-m-d H:i:s') > $date . ' ' . $getBatasInput->text) {
                $keteranganerror = $error->cekKeteranganError('TSTB') ?? '';
                $keteranganerror2 = $error->cekKeteranganError('BBA') ?? '';
                $data = [
                    'status' => false,
                    'keterangan' => $keteranganerror . '<br>' . $keteranganerror2 . '<br><b>' . $keterangantambahan . '</b>'
                ];
                return $data;
            }
            // if (date('Y-m-d', strtotime($tanggal . "+$getBatasHari days")) . ' ' . $getBatasInput->text < date('Y-m-d H:i:s')) {

            //     $keteranganerror = $error->cekKeteranganError('TSTB') ?? '';
            //     $keteranganerror2 = $error->cekKeteranganError('BBA') ?? '';
            //     $data = [
            //         'status' => false,
            //         'keterangan' => $keteranganerror . '<br>' . $keteranganerror2 . '<br><b>' . $keterangantambahan . '</b>'
            //     ];
            //     return $data;
            // }
        }

        $data = [
            'status' => true,
            'keterangan' => ''
        ];
        return $data;
    }
    public function selectColumns($query)
    {
        $tempTerpakai = '##tempTerpakai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTerpakai, function ($table) {
            $table->integer('approvalbukatanggal_id')->nullable();
            $table->integer('terpakai')->nullable();
        });

        $queryHitungTrip = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar with (readuncommitted)")
        )
            ->select(
                'approvalbukatanggal_id',
                DB::raw("count(nobukti) as terpakai")
            )
            ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
            ->groupby('approvalbukatanggal_id');

        DB::table($tempTerpakai)->insertUsing([
            'approvalbukatanggal_id',
            'terpakai',
        ], $queryHitungTrip);

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.tglbukti",
            "$this->table.tglbatas",
            "$this->table.jumlahtrip",
            "parameter.text as statusapproval",
            DB::raw("[user].[name] as user_id"),
            DB::raw("isnull(b.terpakai,0) as terpakai"),
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'suratpengantarapprovalinputtrip.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("$tempTerpakai as b with (readuncommitted)"), 'suratpengantarapprovalinputtrip.id', 'b.approvalbukatanggal_id')
            ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'suratpengantarapprovalinputtrip.user_id', 'user.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->integer('jumlahtrip')->nullable();
            $table->string('statusapproval', 500)->nullable();
            $table->string('user_id', 500)->nullable();
            $table->integer('terpakai')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'tglbukti',
            'tglbatas',
            'jumlahtrip',
            'statusapproval',
            'user_id',
            'terpakai',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
    }


    public function processStore(array $data): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal = new SuratPengantarApprovalInputTrip();

        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first()->text;
        $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;
        $tanggal = date('Y-m-d', strtotime("+$getBatasHari days"));
        $batasHari = $getBatasHari;

        $kondisi = true;
        if ($getBatasHari != 0) {

            while ($kondisi) {
                $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))
                    ->where('tgl', $tanggal)
                    ->first();

                $isSunday = date('l', strtotime($tanggal));
                if ($cekHarilibur == '') {
                    $kondisi = false;
                    if (strtolower($isSunday) == 'sunday') {
                        $kondisi = true;
                        $batasHari += 1;
                    }
                } else {
                    $batasHari += 1;
                }
                $tanggal = date('Y-m-d', strtotime("+$batasHari days"));
            }
        }

        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlahtrip = $data['jumlahtrip'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->user_id = $data['user_id'];
        $approvalBukaTanggal->tglbatas = date('Y-m-d', strtotime($tanggal)) . ' ' . $getBatasInput;
        $approvalBukaTanggal->modifiedby = auth('api')->user()->name;
        $approvalBukaTanggal->info = html_entity_decode(request()->info);

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error storing surat pengantar approval input trip.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'ENTRY SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalBukaTanggal->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $approvalBukaTanggal;
    }

    public function processUpdate(SuratPengantarApprovalInputTrip $approvalBukaTanggal, array $data): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlahtrip = $data['jumlahtrip'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->user_id = $data['user_id'];
        $approvalBukaTanggal->modifiedby = auth('api')->user()->name;
        $approvalBukaTanggal->info = html_entity_decode(request()->info);

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error updating surat pengantar approval input trip.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'EDIT SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'EDIT',
            'datajson' => $approvalBukaTanggal->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $approvalBukaTanggal;
    }

    public function processDestroy($id): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal = new SuratPengantarApprovalInputTrip();
        $approvalBukaTanggal = $approvalBukaTanggal->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalBukaTanggal->getTable()),
            'postingdari' => 'DELETE SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'DELETE',
            'datajson' => $approvalBukaTanggal->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $approvalBukaTanggal;
    }

    public function storeTglValidation($tanggal)
    {
        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->where('tglbatas', '<=', $tanggal = date('Y-m-d', strtotime('+1 days')) . ' ' . '10:00:00')
            ->first();

        return $query;
    }
    public function updateTglValidation($tanggal, $id)
    {
        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->where('id', '<>', $id)
            ->first();

        return $query;
    }

    public function validationJumlahTrip($id)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(DB::raw("COUNT(approvalbukatanggal_id) as jumlah"))
            ->where('approvalbukatanggal_id', $id)
            ->first();
        return $query;
    }

    public function updateApproval()
    {
        $now = date('Y-m-d');

        $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
        $query = DB::table("suratpengantarapprovalinputtrip")->whereRaw("CAST(created_at AS DATE) = '$now'")->get();

        foreach ($query as $value) {
            $getSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(DB::raw("COUNT(approvalbukatanggal_id) as jumlah"))
                ->where('approvalbukatanggal_id', $value->id)
                ->first();

            $result = DB::table("suratpengantarapprovalinputtrip")->where('tglbukti', $value->tglbukti)->update([
                'jumlahtrip' => $getSP->jumlah,
                'statusapproval' => $nonApproval->id,
            ]);
        }

        return $result;
    }
}
