<?php 


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTypeAkuntansiRequest;
use App\Http\Requests\UpdateTypeAkuntansiRequest;
use App\Http\Requests\DestroyTypeAkuntansiRequest;
use App\Models\TypeAkuntansi;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;



class TypeAkuntansiController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        
        $typeakuntansi = new TypeAkuntansi();
        return response([
            'data' => $typeakuntansi->get(),
            'attributes' => [
                'totalRows' => $typeakuntansi->totalRows,
                'totalPages' => $typeakuntansi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    public function default()
    {

        $typeakuntansi = new TypeAkuntansi();
        return response([
            'status' => true,
            'data' => $typeakuntansi->default(),
        ]);
        
    }
    

    /**
     * @ClassName 
     */
    public function store(StoreTypeAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processStore($request->all());
            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            if ($request->limit==0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $typeakuntansi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function show($id)
    {
        $typeakuntansi = new TypeAkuntansi();
        return response([
            'status' => true,
            'data' => $typeakuntansi->find($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateTypeAkuntansiRequest $request, TypeAkuntansi $typeakuntansi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processUpdate($typeakuntansi, $request->all());
            $typeakuntansi->position = $this->getPosition($typeakuntansi, $typeakuntansi->getTable())->position;
            if ($request->limit==0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $typeakuntansi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyTypeAkuntansiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $typeakuntansi = (new TypeAkuntansi())->processDestroy($id);
            $selected = $this->getPosition($typeakuntansi, $typeakuntansi->getTable(), true);
            $typeakuntansi->position = $selected->position;
            $typeakuntansi->id = $selected->id;
            if ($request->limit==0) {
                $typeakuntansi->page = ceil($typeakuntansi->position / (10));
            } else {
                $typeakuntansi->page = ceil($typeakuntansi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $typeakuntansi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    
    /**
     * @ClassName 
     */

     public function export(RangeExportReportRequest $request)
     {
         if (request()->cekExport) {
             return response([
                 'status' => true,
             ]);
         } else {
             $response = $this->index();
             $decodedResponse = json_decode($response->content(), true);
             $akuntansi = $decodedResponse['data'];
 
             $judulLaporan = $akuntansi[0]['judulLaporan'];
 
             $i = 0;
             foreach ($akuntansi as $index => $params) {
 
 
                 $statusaktif = $params['statusaktif'];
 
 
                 $result = json_decode($statusaktif, true);
 
                 $statusaktif = $result['MEMO'];
 
 
                 $akuntansi[$i]['statusaktif'] = $statusaktif;
                 $i++;
             }
 
             $columns = [
                 [
                     'label' => 'No',
                 ],
                 [
                     'label' => 'Kode Tipe',
                     'index' => 'kodetype',
                 ],
                 [
                     'label' => 'Order',
                     'index' => 'order',
                 ],
                 [
                    'label' => 'Keterangan',
                    'index' => 'keterangantype',
                ],
                [
                    'label' => 'Akuntansi',
                    'index' => 'akuntansi',
                ],
                 [
                     'label' => 'Status Aktif',
                     'index' => 'statusaktif',
                 ],
             ];
 
             $this->toExcel($judulLaporan, $akuntansi, $columns);
         }
     }
     
     public function fieldLength()
     {
         $data = [];
         $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('typeakuntansi')->getColumns();
 
         foreach ($columns as $index => $column) {
             $data[$index] = $column->getLength();
         }
 
         return response([
             'data' => $data
         ]);
     }
     public function combostatus(Request $request)
     {
 
         $params = [
             'status' => $request->status ?? '',
             'grp' => $request->grp ?? '',
             'subgrp' => $request->subgrp ?? '',
         ];
         $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
         if ($params['status'] == 'entry') {
             $query = Parameter::select('id', 'text as keterangan')
                 ->where('grp', "=", $params['grp'])
                 ->where('subgrp', "=", $params['subgrp']);
         } else {
             Schema::create($temp, function ($table) {
                 $table->integer('id')->length(11)->nullable();
                 $table->string('parameter', 50)->nullable();
                 $table->string('param', 50)->nullable();
             });
 
             DB::table($temp)->insert(
                 [
                     'id' => '0',
                     'parameter' => 'ALL',
                     'param' => '',
                 ]
             );
 
             $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                 ->where('grp', "=", $params['grp'])
                 ->where('subgrp', "=", $params['subgrp']);
 
             $query = DB::table($temp)
                 ->unionAll($queryall);
         }
 
         $data = $query->get();
 
         return response([
             'data' => $data
         ]);
     }
     
}






?>