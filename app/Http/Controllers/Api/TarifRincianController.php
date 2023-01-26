<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarifRincian;
use App\Http\Requests\StoreTarifRincianRequest;
use App\Http\Requests\UpdateTarifRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifRincianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'tarif_id' => $request->tarif_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = TarifRincian::from(DB::raw("tarifrincian as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['tarif_id'])) {
                $query->where('detail.tarif_id', $params['tarif_id']);
            }

            if ($params['withHeader']) {
                $query->join('tarif', 'tarif.id', 'detail.tarif_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('tarif_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.tujuan',
                    'container.keterangan as container_id',
                    'detail.nominal',
                )
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id');

                $tarif = $query->get();
            } else {
                $query->select(
                    'container.keterangan as container_id',
                    'detail.nominal',
                )
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'container.id', 'detail.container_id');
                $tarif = $query->get();
            }


            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           
            return response([
                'data' => $tarif,
                'user' => $getuser,
                
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function get()
     {
         $tarifrincian = new TarifRincian();
 
         return response([
             'data' => $tarifrincian->get(),
             'attributes' => [
                 'totalRows' => $tarifrincian->totalRows,
                 'totalPages' => $tarifrincian->totalPages
             ]
         ]);
     }

    
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTarifRincianRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTarifRincianRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $tarifRincian = new TarifRincian();

            $tarifRincian->tarif_id = $request->tarif_id;
            $tarifRincian->container_id = $request->container_id;
            $tarifRincian->nominal = $request->nominal;
            $tarifRincian->modifiedby = auth('api')->user()->name;

            
            $tarifRincian->save();
            // dd('test');
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $tarifRincian,
                'id' => $tarifRincian->id,
                'tabel' => $tarifRincian->getTable(),
            ];
        } catch (\Throwable $th) {
            // dd('test2');
            DB::rollBack();
            
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TarifRincian  $tarifRincian
     * @return \Illuminate\Http\Response
     */
    public function show(TarifRincian $tarifRincian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TarifRincian  $tarifRincian
     * @return \Illuminate\Http\Response
     */
    public function edit(TarifRincian $tarifRincian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTarifRincianRequest  $request
     * @param  \App\Models\TarifRincian  $tarifRincian
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTarifRincianRequest $request, TarifRincian $tarifRincian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TarifRincian  $tarifRincian
     * @return \Illuminate\Http\Response
     */
    public function destroy(TarifRincian $tarifRincian)
    {
        //
    }

    public function setUpRow()
    {
        $tarifRincian = new tarifRincian();

        return response([
            'status' => true,
            'detail' => $tarifRincian->setUpRow()
        ]);        
    }
    public function setUpRowExcept($id)
    {
        $tarifRincian = new tarifRincian();
        $rincian = $tarifRincian->where('tarif_id',$id)->get();
        foreach ($rincian as $e) {
            $data[] = [
                 "container_id" => $e->container_id,
                ];
        }
        // return $data;
        return response([
            'status' => true,
            'detail' => $tarifRincian->setUpRowExcept($data)
        ]);        
    }
}
