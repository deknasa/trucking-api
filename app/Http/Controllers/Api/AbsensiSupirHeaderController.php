<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\Parameter;
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
            'withRelations' => $request->withRelations ?? false,
        ];

        $totalRows = AbsensiSupirHeader::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

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
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $parameters = $params['withRelations'] == true
            ? $query->with(
                'absensiSupirDetail',
                'absensiSupirDetail.trado',
                'absensiSupirDetail.supir',
                'absensiSupirDetail.absenTrado')->get()
            : $query->get();

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
            $absensiSupirHeader = new AbsensiSupirHeader();
            $absensiSupirHeader->nobukti = $request->nobukti;
            $absensiSupirHeader->tgl = $request->tgl;
            $absensiSupirHeader->keterangan = $request->keterangan ?? '1';
            $absensiSupirHeader->kasgantung_nobukti = $request->kasgantung_nobukti ?? '1';
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->modifiedby = $request->modifiedby ?? '1';
            $absensiSupirHeader->kasgantung_nobukti = $request->kasgantung_nobukti ?? '';
            $absensiSupirHeader->save();

            /* Store detail */
            for ($i = 0; $i < count($request->trado_id); $i++) {
                $absensiSupirHeader->absensiSupirDetail()->create([
                    "absensi_id" => $absensiSupirHeader->id,
                    "trado_id" => $request->trado_id[$i] ?? '',
                    "absen_id" => $request->absen_id[$i] ?? '',
                    "supir_id" => $request->supir_id[$i] ?? '',
                    "jam" => $request->jam[$i] ?? '00:00',
                    "uangjalan" => (float) $request->uangjalan[$i] ?? 0,
                    "keterangan" => $request->keterangan_detail[$i] ?? ''
                ]);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($absensiSupirHeader->save() && $absensiSupirHeader->absensiSupirDetail) {
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function show(AbsensiSupirHeader $absensiSupirHeader, $id)
    {
        $data = AbsensiSupirHeader::with(
            'absensiSupirDetail',
            'absensiSupirDetail.trado',
            'absensiSupirDetail.supir',
            'absensiSupirDetail.absenTrado',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAbsensiSupirHeaderRequest  $request
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAbsensiSupirHeaderRequest $request, AbsensiSupirHeader $absensiSupirHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $absensiSupirHeader = AbsensiSupirHeader::findOrFail($id);
            $absensiSupirHeader->nobukti = $request->nobukti;
            $absensiSupirHeader->tgl = $request->tgl;
            $absensiSupirHeader->keterangan = $request->keterangan ?? '1';
            $absensiSupirHeader->kasgantung_nobukti = $request->kasgantung_nobukti ?? '1';
            $absensiSupirHeader->nominal = $request->nominal ?? '1';
            $absensiSupirHeader->modifiedby = $request->modifiedby ?? '1';
            $absensiSupirHeader->save();

            /* Delete existing detail */
            $absensiSupirHeader->absensiSupirDetail()->delete();

            /* Store detail */
            for ($i = 0; $i < count($request->trado_id); $i++) {
                $absensiSupirHeader->absensiSupirDetail()->create([
                    "trado_id" => $request->trado_id[$i] ?? '',
                    "absen_id" => $request->absen_id[$i] ?? '',
                    "supir_id" => $request->supir_id[$i] ?? '',
                    "jam" => $request->jam[$i] ?? '00:00',
                    "uangjalan" => (float) $request->uangjalan[$i] ?? 0,
                    "keterangan" => $request->keterangan_detail[$i] ?? ''
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
                    'message' => 'Berhasil diubah',
                    'data' => $absensiSupirHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($absensiSupirHeader->absensiSupirDetail);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AbsensiSupirHeader  $absensiSupirHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(AbsensiSupirHeader $absensiSupirHeader, $id)
    {
        $delete = AbsensiSupirHeader::destroy($id);

        if ($delete) {
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus'
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
}
