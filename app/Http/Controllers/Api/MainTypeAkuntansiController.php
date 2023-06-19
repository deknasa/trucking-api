<?php 


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMainTypeAkuntansiRequest;
use App\Http\Requests\UpdateMainTypeAkuntansiRequest;
use App\Http\Requests\DestroyMainTypeAkuntansiRequest;
use App\Models\MainTypeAkuntansi;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;



class MainTypeAkuntansiController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        
        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'data' => $maintypeakuntansi->get(),
            'attributes' => [
                'totalRows' => $maintypeakuntansi->totalRows,
                'totalPages' => $maintypeakuntansi->totalPages
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

        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'status' => true,
            'data' => $maintypeakuntansi->default(),
        ]);
        
    }
    

    /**
     * @ClassName 
     */
    public function store(StoreMainTypeAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processStore($request->all());
            $maintypeakuntansi->position = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable())->position;
            $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $maintypeakuntansi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function show($id)
    {
        $maintypeakuntansi = new MainTypeAkuntansi();
        return response([
            'status' => true,
            'data' => $maintypeakuntansi->find($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMainTypeAkuntansiRequest $request, MainTypeAkuntansi $maintypeakuntansi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processUpdate($maintypeakuntansi, $request->all());
            $maintypeakuntansi->position = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable())->position;
            $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $maintypeakuntansi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyMainTypeAkuntansiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $maintypeakuntansi = (new MainTypeAkuntansi())->processDestroy($id);
            $selected = $this->getPosition($maintypeakuntansi, $maintypeakuntansi->getTable(), true);
            $maintypeakuntansi->position = $selected->position;
            $maintypeakuntansi->id = $selected->id;
            $maintypeakuntansi->page = ceil($maintypeakuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $maintypeakuntansi
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
             $maintypeakuntansi = $decodedResponse['data'];
 
             $judulLaporan = $maintypeakuntansi[0]['judulLaporan'];
 
             $i = 0;
             foreach ($maintypeakuntansi as $index => $params) {
 
 
                 $statusaktif = $params['statusaktif'];
 
 
                 $result = json_decode($statusaktif, true);
 
                 $statusaktif = $result['MEMO'];
 
 
                 $maintypeakuntansi[$i]['statusaktif'] = $statusaktif;
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
 
             $this->toExcel($judulLaporan, $maintypeakuntansi, $columns);
         }
     }
     
     public function fieldLength()
     {
         $data = [];
         $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('maintypeakuntansi')->getColumns();
 
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