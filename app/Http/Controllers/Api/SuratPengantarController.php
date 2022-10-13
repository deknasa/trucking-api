<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantar;
use App\Models\SuratPengantarBiayaTambahan;
use App\Models\Pelanggan;
use App\Models\UpahSupir;
use App\Models\UpahSupirRincian;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Models\Trado;
use App\Models\Supir;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Tarif;
use App\Models\Kota;
use App\Models\Parameter;
use App\Http\Requests\StoreSuratPengantarRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new SuratPengantar())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new SuratPengantar())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new SuratPengantar())->getTable())->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.nourutorder',
                'kotadari.keterangan as dari_id',
                'kotasampai.keterangan as sampai_id',
                'container.keterangan as container_id',
                'suratpengantar.nocont',
                'suratpengantar.nocont2',
                'statuscontainer.keterangan as statuscontainer_id',
                'trado.keterangan as trado_id',
                'supir.namasupir as supir_id',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'statuslongtrip.text as statuslongtrip',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'statusperalihan.text as statusperalihan',
                'tarif.tujuan as tarif_id',
                'suratpengantar.nominalperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.statusritasiomset',
                'suratpengantar.cabang_id',
                'suratpengantar.komisisupir',
                'suratpengantar.tolsupir',
                'suratpengantar.jarak',
                'suratpengantar.nosptagihlain',
                'suratpengantar.nilaitagihlain',
                'suratpengantar.tujuantagih',
                'suratpengantar.liter',
                'suratpengantar.nominalstafle',
                'suratpengantar.statusnotif',
                'suratpengantar.statusoneway',
                'suratpengantar.statusedittujuan',
                'suratpengantar.upahbongkardepo',
                'suratpengantar.upahmuatdepo',
                'suratpengantar.hargatol',
                'suratpengantar.qtyton',
                'suratpengantar.totalton',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.statustrip',
                'suratpengantar.notripasal',
                'suratpengantar.tgldoor',
                // 'suratpengantar.upahritasi_id',
                'suratpengantar.statusdisc',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at'
            )
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', '=', 'pelanggan.id')
                ->leftJoin('kota as kotadari', 'suratpengantar.dari_id', '=', 'kotadari.id')
                ->leftJoin('kota as kotasampai', 'suratpengantar.sampai_id', '=', 'kotasampai.id')
                ->leftJoin('container', 'suratpengantar.container_id', '=', 'container.id')
                ->leftJoin('supir', 'suratpengantar.supir_id', '=', 'supir.id')
                ->leftJoin('trado', 'suratpengantar.trado_id', '=', 'trado.id')
                ->leftJoin('agen', 'suratpengantar.agen_id', '=', 'agen.id')
                ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', '=', 'jenisorder.id')
                ->leftJoin('tarif', 'suratpengantar.tarif_id', '=', 'tarif.id')
                ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', '=', 'statuscontainer.id')
                ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', '=', 'statusperalihan.id')
                ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', '=', 'statuslongtrip.id')
                ->orderBy('suratpengantar.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'nobukti' or $params['sortIndex'] == 'keterangan') {
            $query = DB::table((new SuratPengantar())->getTable())->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.nourutorder',
                'kotadari.keterangan as dari_id',
                'kotasampai.keterangan as sampai_id',
                'container.keterangan as container_id',
                'suratpengantar.nocont',
                'suratpengantar.nocont2',
                'statuscontainer.keterangan as statuscontainer_id',
                'trado.keterangan as trado_id',
                'supir.namasupir as supir_id',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'statuslongtrip.text as statuslongtrip',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'statusperalihan.text as statusperalihan',
                'tarif.tujuan as tarif_id',
                'suratpengantar.nominalperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.statusritasiomset',
                'suratpengantar.cabang_id',
                'suratpengantar.komisisupir',
                'suratpengantar.tolsupir',
                'suratpengantar.jarak',
                'suratpengantar.nosptagihlain',
                'suratpengantar.nilaitagihlain',
                'suratpengantar.tujuantagih',
                'suratpengantar.liter',
                'suratpengantar.nominalstafle',
                'suratpengantar.statusnotif',
                'suratpengantar.statusoneway',
                'suratpengantar.statusedittujuan',
                'suratpengantar.upahbongkardepo',
                'suratpengantar.upahmuatdepo',
                'suratpengantar.hargatol',
                'suratpengantar.qtyton',
                'suratpengantar.totalton',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.statustrip',
                'suratpengantar.notripasal',
                'suratpengantar.tgldoor',
                'suratpengantar.upahritasi_id',
                'suratpengantar.statusdisc',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at'
            )
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', '=', 'pelanggan.id')
                ->leftJoin('kota as kotadari', 'suratpengantar.dari_id', '=', 'kotadari.id')
                ->leftJoin('kota as kotasampai', 'suratpengantar.sampai_id', '=', 'kotasampai.id')
                ->leftJoin('container', 'suratpengantar.container_id', '=', 'container.id')
                ->leftJoin('supir', 'suratpengantar.supir_id', '=', 'supir.id')
                ->leftJoin('trado', 'suratpengantar.trado_id', '=', 'trado.id')
                ->leftJoin('agen', 'suratpengantar.agen_id', '=', 'agen.id')
                ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', '=', 'jenisorder.id')
                ->leftJoin('tarif', 'suratpengantar.tarif_id', '=', 'tarif.id')
                ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', '=', 'statuscontainer.id')
                ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', '=', 'statusperalihan.id')
                ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', '=', 'statuslongtrip.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('suratpengantar.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new SuratPengantar())->getTable())->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'suratpengantar.keterangan',
                    'suratpengantar.nourutorder',
                    'kotadari.keterangan as dari_id',
                    'kotasampai.keterangan as sampai_id',
                    'container.keterangan as container_id',
                    'suratpengantar.nocont',
                    'suratpengantar.nocont2',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'trado.keterangan as trado_id',
                    'supir.namasupir as supir_id',
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'statuslongtrip.text as statuslongtrip',
                    'agen.namaagen as agen_id',
                    'jenisorder.keterangan as jenisorder_id',
                    'statusperalihan.text as statusperalihan',
                    'tarif.tujuan as tarif_id',
                    'suratpengantar.nominalperalihan',
                    'suratpengantar.persentaseperalihan',
                    'suratpengantar.nosp',
                    'suratpengantar.tglsp',
                    'suratpengantar.statusritasiomset',
                    'suratpengantar.cabang_id',
                    'suratpengantar.komisisupir',
                    'suratpengantar.tolsupir',
                    'suratpengantar.jarak',
                    'suratpengantar.nosptagihlain',
                    'suratpengantar.nilaitagihlain',
                    'suratpengantar.tujuantagih',
                    'suratpengantar.liter',
                    'suratpengantar.nominalstafle',
                    'suratpengantar.statusnotif',
                    'suratpengantar.statusoneway',
                    'suratpengantar.statusedittujuan',
                    'suratpengantar.upahbongkardepo',
                    'suratpengantar.upahmuatdepo',
                    'suratpengantar.hargatol',
                    'suratpengantar.qtyton',
                    'suratpengantar.totalton',
                    'suratpengantar.mandorsupir_id',
                    'suratpengantar.mandortrado_id',
                    'suratpengantar.statustrip',
                    'suratpengantar.notripasal',
                    'suratpengantar.tgldoor',
                    'suratpengantar.upahritasi_id',
                    'suratpengantar.statusdisc',
                    'suratpengantar.gajisupir',
                    'suratpengantar.gajikenek',
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at'
                )
                    ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', '=', 'pelanggan.id')
                    ->leftJoin('kota as kotadari', 'suratpengantar.dari_id', '=', 'kotadari.id')
                    ->leftJoin('kota as kotasampai', 'suratpengantar.sampai_id', '=', 'kotasampai.id')
                    ->leftJoin('container', 'suratpengantar.container_id', '=', 'container.id')
                    ->leftJoin('supir', 'suratpengantar.supir_id', '=', 'supir.id')
                    ->leftJoin('trado', 'suratpengantar.trado_id', '=', 'trado.id')
                    ->leftJoin('agen', 'suratpengantar.agen_id', '=', 'agen.id')
                    ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', '=', 'jenisorder.id')
                    ->leftJoin('tarif', 'suratpengantar.tarif_id', '=', 'tarif.id')
                    ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', '=', 'statuscontainer.id')
                    ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', '=', 'statusperalihan.id')
                    ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', '=', 'statuslongtrip.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('suratpengantar.id', $params['sortOrder']);
            } else {
                $query = DB::table((new SuratPengantar())->getTable())->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'suratpengantar.keterangan',
                    'suratpengantar.nourutorder',
                    'kotadari.keterangan as dari_id',
                    'kotasampai.keterangan as sampai_id',
                    'container.keterangan as container_id',
                    'suratpengantar.nocont',
                    'suratpengantar.nocont2',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'trado.keterangan as trado_id',
                    'supir.namasupir as supir_id',
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'statuslongtrip.text as statuslongtrip',
                    'agen.namaagen as agen_id',
                    'jenisorder.keterangan as jenisorder_id',
                    'statusperalihan.text as statusperalihan',
                    'tarif.tujuan as tarif_id',
                    'suratpengantar.nominalperalihan',
                    'suratpengantar.persentaseperalihan',
                    'suratpengantar.nosp',
                    'suratpengantar.tglsp',
                    'suratpengantar.statusritasiomset',
                    'suratpengantar.cabang_id',
                    'suratpengantar.komisisupir',
                    'suratpengantar.tolsupir',
                    'suratpengantar.jarak',
                    'suratpengantar.nosptagihlain',
                    'suratpengantar.nilaitagihlain',
                    'suratpengantar.tujuantagih',
                    'suratpengantar.liter',
                    'suratpengantar.nominalstafle',
                    'suratpengantar.statusnotif',
                    'suratpengantar.statusoneway',
                    'suratpengantar.statusedittujuan',
                    'suratpengantar.upahbongkardepo',
                    'suratpengantar.upahmuatdepo',
                    'suratpengantar.hargatol',
                    'suratpengantar.qtyton',
                    'suratpengantar.totalton',
                    'suratpengantar.mandorsupir_id',
                    'suratpengantar.mandortrado_id',
                    'suratpengantar.statustrip',
                    'suratpengantar.notripasal',
                    'suratpengantar.tgldoor',
                    'suratpengantar.upahritasi_id',
                    'suratpengantar.statusdisc',
                    'suratpengantar.gajisupir',
                    'suratpengantar.gajikenek',
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at'
                )
                    ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', '=', 'pelanggan.id')
                    ->leftJoin('kota as kotadari', 'suratpengantar.dari_id', '=', 'kotadari.id')
                    ->leftJoin('kota as kotasampai', 'suratpengantar.sampai_id', '=', 'kotasampai.id')
                    ->leftJoin('container', 'suratpengantar.container_id', '=', 'container.id')
                    ->leftJoin('supir', 'suratpengantar.supir_id', '=', 'supir.id')
                    ->leftJoin('trado', 'suratpengantar.trado_id', '=', 'trado.id')
                    ->leftJoin('agen', 'suratpengantar.agen_id', '=', 'agen.id')
                    ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', '=', 'jenisorder.id')
                    ->leftJoin('tarif', 'suratpengantar.tarif_id', '=', 'tarif.id')
                    ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', '=', 'statuscontainer.id')
                    ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', '=', 'statusperalihan.id')
                    ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', '=', 'statuslongtrip.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('suratpengantar.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'dari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'sampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'statuscontainer_id') {
                            $query = $query->where('statuscontainer.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'tarif_id') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('suratpengantar.' . $search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'dari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'sampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'statuscontainer_id') {
                            $query = $query->where('statuscontainer.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'tarif_id') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('suratpengantar.' . $search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $suratpengantar = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $suratpengantar,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }
    /**
     * @ClassName 
     */
    public function store(StoreSuratPengantarRequest $request)
    {
        DB::beginTransaction();

        try {
            // $content = new Request();
            // $content['group'] = 'SURATPENGANTAR';
            // $content['subgroup'] = 'SURATPENGANTAR';
            // $content['table'] = 'suratpengantar';


            $group = 'SURAT PENGANTAR';
            $subgroup = 'SURAT PENGANTAR';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'suratpengantar';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $suratpengantar = new SuratPengantar();
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $request->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
            $suratpengantar->nourutorder = $request->nourutorder ?? 0;
            $suratpengantar->dari_id = $request->dari_id;
            $suratpengantar->sampai_id = $request->sampai_id;
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();
            if ($upahsupir == '') {
                return response([
                    'status' => false,
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ]);
            }
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();
            $suratpengantar->upah_id = $upahsupir->id;
            $suratpengantar->jarak = $upahsupirRincian->jarak ?? 0;
            $suratpengantar->container_id = $request->container_id;
            $suratpengantar->nocont = $request->nocont ?? '';
            $suratpengantar->nocont2 = $request->nocont2 ?? '';
            $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratpengantar->trado_id = $request->trado_id;
            $suratpengantar->supir_id = $request->supir_id;
            $suratpengantar->nojob = $request->nojob ?? '';
            $suratpengantar->nojob2 = $request->nojob2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip ?? 0;
            $suratpengantar->gajisupir = $request->gajisupir ?? 0;
            $suratpengantar->gajikenek = $request->gajikenek ?? 0;
            $suratpengantar->gajiritasi = $request->gajiritasi ?? 0;
            $suratpengantar->agen_id = $request->agen_id;
            $suratpengantar->jenisorder_id = $request->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan ?? 0;
            $suratpengantar->tarif_id = $request->tarif_id;
            $tarif = Tarif::find($request->tarif_id);
            $persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->persentaseperalihan = $persentaseperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglsp));
            $suratpengantar->statusritasiomset = $request->statusritasiomset ?? 0;
            $suratpengantar->cabang_id = $request->cabang_id ?? 0;
            $suratpengantar->komisisupir = $request->komisisupir;
            $suratpengantar->tolsupir = $request->tolsupir ?? 0;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? 0;
            $suratpengantar->nilaitagihlain = $request->nilaitagihlain ?? 0;
            $suratpengantar->tujuantagih = $request->tujuantagih ?? '';

            $suratpengantar->liter = $upahsupir->liter ?? 0;
            $suratpengantar->nominalstafle = $request->nominalstafle ?? 0;
            $suratpengantar->statusnotif = $request->statusnotif ?? 0;
            $suratpengantar->statusoneway = $request->statusoneway ?? 0;
            $suratpengantar->statusedittujuan = $request->statusedittujuan ?? 0;
            $suratpengantar->upahbongkardepo = $request->upahbongkardepo ?? 0;
            $suratpengantar->upahmuatdepo = $request->upahmuatdepo ?? 0;
            $suratpengantar->hargatol = $upahsupirRincian->hargatol ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $request->totalton ?? 0;
            // $supir = Supir::find($request->supir_id);
            // $trado = Trado::find($request->trado_id);
            $suratpengantar->mandorsupir_id = $request->mandorsupir_id ?? 0;
            $suratpengantar->mandortrado_id = $request->mandortrado_id ?? 0;
            $suratpengantar->statustrip = $request->statustrip ?? 0;
            $suratpengantar->notripasal = $request->notripasal ?? '';
            $suratpengantar->tgldoor = date('Y-m-d', strtotime($request->tgldoor));
            $suratpengantar->upahritasi_id = $request->upahritasi_id ?? 0;
            $suratpengantar->statusdisc = $request->statusdisc ?? 0;
            $suratpengantar->modifiedby = auth('api')->user()->name;;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $suratpengantar->nobukti = $nobukti;

            // try {
            //     $suratpengantar->save();
            // } catch (\Exception $e) {
            //     $errorCode = @$e->errorInfo[1];
            //     if ($errorCode == 2601) {
            //         goto TOP;
            //     }
            // }

            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'ENTRY SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                foreach ($request->keteranganbiaya as $key => $value) {
                    $nominal = $request->nominal[$key];
                    $nominal = str_replace('.', '', $nominal);
                    $nominal = str_replace(',', '', $nominal);
                    if ($value != '' and $nominal > 0) {
                        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();

                        $suratpengantarbiayatambahan->suratpengantar_id = $suratpengantar->id;
                        $suratpengantarbiayatambahan->keteranganbiaya = $value;
                        $suratpengantarbiayatambahan->nominal = $nominal;
                        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
                        $suratpengantarbiayatambahan->save();
                    }
                    // else {
                    //     return response([
                    //         'status' => false,
                    //         'message' => 'Harap Lengapin Informasi Biaya',
                    //     ]);
                    // }
                }


                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable());
            $suratpengantar->position = $selected->position;
            $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));

            // /* Set position and page */
            // $del = 0;
            // $data = $this->getid($suratpengantar->id, $request, $del);
            // $suratpengantar->position = $data->row;

            // if (isset($request->limit)) {
            //     $suratpengantar->page = ceil($suratpengantar->position / $request->limit);
            // }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $suratpengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = DB::table((new SuratPengantar())->getTable())->with(
            'suratpengantarBiaya',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    public function edit(suratpengantar $suratpengantar)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(StoreSuratPengantarRequest $request, SuratPengantar $suratpengantar)
    {
        try {
            // $suratpengantar = DB::table((new SuratPengantar())->getTable())->findOrFail($suratpengantar->id);
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $request->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
            $suratpengantar->nourutorder = $request->nourutorder ?? 0;
            $suratpengantar->dari_id = $request->dari_id;
            $suratpengantar->sampai_id = $request->sampai_id;
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();
            if ($upahsupir == '') {
                return response([
                    'status' => false,
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ]);
            }
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();
            $suratpengantar->upah_id = $upahsupir->id;
            $suratpengantar->jarak = $upahsupirRincian->jarak ?? 0;
            $suratpengantar->container_id = $request->container_id;
            $suratpengantar->nocont = $request->nocont ?? '';
            $suratpengantar->nocont2 = $request->nocont2 ?? '';
            $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratpengantar->trado_id = $request->trado_id;
            $suratpengantar->supir_id = $request->supir_id;
            $suratpengantar->nojob = $request->nojob ?? '';
            $suratpengantar->nojob2 = $request->nojob2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip ?? 0;
            $suratpengantar->gajisupir = $request->gajisupir ?? 0;
            $suratpengantar->gajikenek = $request->gajikenek ?? 0;
            $suratpengantar->gajiritasi = $request->gajiritasi ?? 0;
            $suratpengantar->agen_id = $request->agen_id;
            $suratpengantar->jenisorder_id = $request->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan ?? 0;
            $suratpengantar->tarif_id = $request->tarif_id;
            $tarif = Tarif::find($request->tarif_id);
            $persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->persentaseperalihan = $persentaseperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglsp));
            $suratpengantar->statusritasiomset = $request->statusritasiomset ?? 0;
            $suratpengantar->cabang_id = $request->cabang_id ?? 0;
            $suratpengantar->komisisupir = $request->komisisupir;
            $suratpengantar->tolsupir = $request->tolsupir ?? 0;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? 0;
            $suratpengantar->nilaitagihlain = $request->nilaitagihlain ?? 0;
            $suratpengantar->tujuantagih = $request->tujuantagih ?? '';

            $suratpengantar->liter = $upahsupir->liter ?? 0;
            $suratpengantar->nominalstafle = $request->nominalstafle ?? 0;
            $suratpengantar->statusnotif = $request->statusnotif ?? 0;
            $suratpengantar->statusoneway = $request->statusoneway ?? 0;
            $suratpengantar->statusedittujuan = $request->statusedittujuan ?? 0;
            $suratpengantar->upahbongkardepo = $request->upahbongkardepo ?? 0;
            $suratpengantar->upahmuatdepo = $request->upahmuatdepo ?? 0;
            $suratpengantar->hargatol = $upahsupirRincian->hargatol ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $request->totalton ?? 0;
            // $supir = Supir::find($request->supir_id);
            // $trado = Trado::find($request->trado_id);
            $suratpengantar->mandorsupir_id = $request->mandorsupir_id ?? 0;
            $suratpengantar->mandortrado_id = $request->mandortrado_id ?? 0;
            $suratpengantar->statustrip = $request->statustrip ?? 0;
            $suratpengantar->notripasal = $request->notripasal ?? '';
            $suratpengantar->tgldoor = date('Y-m-d', strtotime($request->tgldoor));
            $suratpengantar->upahritasi_id = $request->upahritasi_id ?? 0;
            $suratpengantar->statusdisc = $request->statusdisc ?? 0;
            $suratpengantar->modifiedby = auth('api')->user()->name;;

            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'EDIT suratpengantar',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->id,
                    'aksi' => 'EDIT',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                $suratpengantar->suratpengantarBiaya()->delete();

                foreach ($request->keteranganbiaya as $key => $value) {
                    $nominal = $request->nominal[$key];
                    $nominal = str_replace('.', '', $nominal);
                    $nominal = str_replace(',', '', $nominal);
                    if ($value != '' and $nominal > 0) {
                        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();

                        $suratpengantarbiayatambahan->suratpengantar_id = $suratpengantar->id;
                        $suratpengantarbiayatambahan->keteranganbiaya = $value;
                        $suratpengantarbiayatambahan->nominal = $nominal;
                        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
                        $suratpengantarbiayatambahan->save();
                    }
                }

                /* Set position and page */
                $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable());
                $suratpengantar->position = $selected->position;
                $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));

                // /* Set position and page */
                // $suratpengantar->position = $this->getid($suratpengantar->id, $request, 0)->row;

                // if (isset($request->limit)) {
                //     $suratpengantar->page = ceil($suratpengantar->position / $request->limit);
                // }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $suratpengantar
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\suratpengantar  $suratpengantar
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function destroy(SuratPengantar $suratpengantar, Request $request)
    {
        DB::beginTransaction();
        $d = SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratpengantar->id)->delete();
        $delete = SuratPengantar::destroy($suratpengantar->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($suratpengantar->getTable()),
                'postingdari' => 'DELETE SURAT PENGANTAR',
                'idtrans' => $suratpengantar->id,
                'nobuktitrans' => $suratpengantar->id,
                'aksi' => 'DELETE',
                'datajson' => $suratpengantar->toArray(),
                'modifiedby' => $suratpengantar->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable(), true);
            $suratpengantar->position = $selected->position;
            $suratpengantar->id = $selected->id;
            $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));

            // $data = $this->getid($suratpengantar->id, $request, $del);
            // $suratpengantar->position = @$data->row  ?? 0;
            // $suratpengantar->id = @$data->id  ?? 0;
            // if (isset($request->limit)) {
            //     $suratpengantar->page = ceil($suratpengantar->position / $request->limit);
            // }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $suratpengantar
            ]);
        } else {
            DB::rollBack();
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('suratpengantar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getGaji(Request $request)
    {
        $data = DB::table('upahsupir')
            ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->select('nominalsupir', 'nominalkenek', 'nominalkomisi')
            ->where('kotadari_id', $request->dari)
            ->where('kotasampai_id', $request->sampai)
            ->where('container_id', $request->container)
            ->where('statuscontainer_id', $request->statuscontainer)->first();

        return response([
            'data' => $data
        ]);
    }

    public function getid($id, $request, $del)
    {
        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('nobukti', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new SuratPengantar())->getTable())->select(
                'suratpengantar.id as id_',
                'suratpengantar.nobukti',
                'suratpengantar.keterangan',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at'
            )
                ->orderBy('suratpengantar.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodesuratpengantar' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new SuratPengantar())->getTable())->select(
                'suratpengantar.id as id_',
                'suratpengantar.nobukti',
                'suratpengantar.keterangan',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('suratpengantar.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new SuratPengantar())->getTable())->select(
                    'suratpengantar.id as id_',
                    'suratpengantar.nobukti',
                    'suratpengantar.keterangan',
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('suratpengantar.id', $params['sortorder']);
            } else {
                $query = DB::table((new SuratPengantar())->getTable())->select(
                    'suratpengantar.id as id_',
                    'suratpengantar.nobukti',
                    'suratpengantar.keterangan',
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('suratpengantar.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'nobukti', 'keterangan', 'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }

    public function combo(Request $request)
    {
        $data = [
            'pelanggan' => Pelanggan::all(),
            'upahsupir' => UpahSupir::all(),
            'container' => Container::all(),
            'statuscontainer' => StatusContainer::all(),
            'trado' => Trado::all(),
            'supir' => Supir::all(),
            'agen' => Agen::all(),
            'jenisorder' => JenisOrder::all(),
            'tarif' => Tarif::all(),
            'kota' => Kota::all(),
            'statuslongtrip' => Parameter::where('grp', 'STATUS LONGTRIP')->get(),
            'statusperalihan' => Parameter::where('grp', 'STATUS PERALIHAN')->get(),
            'statusritasiomset' => Parameter::where('grp', 'STATUS RITASIOMSET')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
}
