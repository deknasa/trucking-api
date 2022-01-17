<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiSupirHeaderController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = AbsensiSupirHeader::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        $query = AbsensiSupirHeader::orderBy($params['sortIndex'], $params['sortOrder']);

        /* Searching */
        if (count($params['search']) > 0) {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = ceil($totalRows / $params['limit']);
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $parameters = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $parameters,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAbsensiSupirHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAbsensiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $absensiSupirHeader = AbsensiSupirHeader::create([
                'nobukti' => $request->nobukti,
                'tgl' => $request->tgl,
                'keterangan' => $request->keterangan,
            ]);

            /* Store detail */
            for ($i = 0; $i < count($request->trado_id); $i++) {
                $absensiSupirHeader->absensiSupirDetail()->create([
                    "trado_id" => $request->trado_id[$i],
                    "absensi_id" => $request->absensi_id[$i],
                    "supir_id" => $request->supir_id[$i],
                    "jam" => $request->jam[$i],
                    "uangjalan" => $request->uangjalan[$i],
                    "keterangan" => $request->keterangan_detail[$i]
                ]);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            if ($absensiSupirHeader && $absensiSupirHeader->absensiSupirDetail) {
                DB::commit();

                /* Set position and page */
                $absensiSupirHeader->position = AbsensiSupirHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $absensiSupirHeader->{$request->sortname})
                    ->where('id', '<=', $absensiSupirHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $absensiSupirHeader->page = ceil($absensiSupirHeader->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $absensiSupirHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($absensiSupirHeader->absensiSupirDetail);

        /* Last update */
        // DB::beginTransaction();

        // try {
        //     $absensi = new AbsensiSupirHeader();
        //     $absensi->tgl = $request->tgl;
        //     $absensi->keterangan = $request->keterangan;

        //     $absensi_detail = new AbsensiSupirDetail();

        //     $request->sortname = $request->sortname ?? 'id';
        //     $request->sortorder = $request->sortorder ?? 'asc';

        //     if ($absensi->save() && $absensi_detail->insert($request->detail)) {
        //         DB::commit();

        //         /* Set position and page */
        //         $absensi->position = AbsensiSupirHeader::orderBy($request->sortname, $request->sortorder)
        //             ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $absensi->{$request->sortname})
        //             ->where('id', '<=', $absensi->id)
        //             ->count();

        //         if (isset($request->limit)) {
        //             $absensi->page = ceil($absensi->position / $request->limit);
        //         }

        //         return response([
        //             'status' => true,
        //             'message' => 'Berhasil disimpan',
        //             'data' => $absensi
        //         ]);
        //     } else {
        //         DB::rollBack();
        //         return response([
        //             'status' => false,
        //             'message' => 'Gagal disimpan'
        //         ]);
        //     }
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     return response($th->getMessage());
        // }

        // ------------------------
        // DB::beginTransaction();

        // try {
        //     $absensi = new AbsensiSupirHeader();
        //     $absensi->nobukti = $request->nobukti;
        //     $absensi->tgl = $request->tgl;
        //     $absensi->keterangan = $request->keterangan;

        //     // $absensi_detail = new AbsensiSupirDetail();
        //     // $absensi_detail->jam = $request->jam[0];
        //     // $absensi_detail->save();

        //     // $request->sortname = $request->sortname ?? 'id';
        //     // $request->sortorder = $request->sortorder ?? 'asc';

        //     if ($absensi->save()) {
        //         // DB::commit();
        //         /* Set position and page */
        //         $absensi->position = AbsensiSupirHeader::orderBy($request->sortname, $request->sortorder)
        //             ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $absensi->{$request->sortname})
        //             ->where('id', '<=', $absensi->id)
        //             ->count();

        //         if (isset($request->limit)) {
        //             $absensi->page = ceil($absensi->position / $request->limit);
        //         }

        //         return response([
        //             'status' => true,
        //             'message' => 'Berhasil disimpan',
        //             'data' => $absensi
        //         ]);
        //     } else {
        //         return response([
        //             'status' => false,
        //             'message' => 'Gagal disimpan'
        //         ]);
        //     }
        // } catch (\Throwable $th) {
        //     DB::commit();
        //     return response($th->getMessage());
        // }
    }
}
