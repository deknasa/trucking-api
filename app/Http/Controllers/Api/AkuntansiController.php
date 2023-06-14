<?php 


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAkuntansiRequest;
use App\Http\Requests\UpdateAkuntansiRequest;
use App\Models\Akuntansi;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;



class AkuntansiController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        
        $akuntansi = new Akuntansi();
        return response([
            'data' => $akuntansi->get(),
            'attributes' => [
                'totalRows' => $akuntansi->totalRows,
                'totalPages' => $akuntansi->totalPages
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

        $akuntansi = new Akuntansi();
        return response([
            'status' => true,
            'data' => $akuntansi->default(),
        ]);
        
    }
    

    /**
     * @ClassName 
     */
    public function store(StoreAkuntansiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $akuntansi = (new Akuntansi())->processStore($request->all());
            $akuntansi->position = $this->getPosition($akuntansi, $akuntansi->getTable())->position;
            $akuntansi->page = ceil($akuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $akuntansi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function show(Akuntansi $akuntansi)
    {
        return response([
            'status' => true,
            'data' => $akuntansi
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAkuntansiRequest $request, Akuntansi $akuntansi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $akuntansi = (new Akuntansi())->processUpdate($akuntansi, $request->all());
            $akuntansi->position = $this->getPosition($akuntansi, $akuntansi->getTable())->position;
            $akuntansi->page = ceil($akuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $akuntansi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $akuntansi = (new Akuntansi())->processDestroy($id);
            $selected = $this->getPosition($akuntansi, $akuntansi->getTable(), true);
            $akuntansi->position = $selected->position;
            $akuntansi->id = $selected->id;
            $akuntansi->page = ceil($akuntansi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $akuntansi
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
                     'label' => 'Kode Akuntansi',
                     'index' => 'kodeakuntansi',
                 ],
                 [
                     'label' => 'Keterangan',
                     'index' => 'keterangan',
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
         $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('akuntansi')->getColumns();
 
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