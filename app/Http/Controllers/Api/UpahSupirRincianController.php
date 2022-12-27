<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahSupirRincian;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpahSupirRincianController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'upahsupir_id' => $request->upahsupir_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = UpahSupirRincian::from('upahsupirrincian as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['upahsupir_id'])) {
                $query->where('detail.upahsupir_id', $params['upahsupir_id']);
            }

            if ($params['withHeader']) {
                $query->join('upahsupir', 'upahsupir.id', 'detail.upahsupir_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('upahsupir_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'kotadari.keterangan as kotadari',
                    'kotasampai.keterangan as kotasampai',
                    'header.jarak',
                    'zona.keterangan as zona',
                    'header.tglmulaiberlaku',
                    'header.tglakhirberlaku',
                    'statusluarkota.text as statusluarkota',
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                )
                    ->join('upahsupir as header', 'header.id', 'detail.upahsupir_id') 
                    ->join('kota as kotadari', 'kotadari.id', '=', 'header.kotadari_id')
                    ->join('kota as kotasampai', 'kotasampai.id', '=', 'header.kotasampai_id')
                    ->leftJoin('zona', 'header.zona_id', 'zona.id')
                    ->leftJoin('parameter as statusluarkota', 'header.statusluarkota', 'statusluarkota.id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id');

                $upahsupir = $query->get();
            } else {
                $query->select(
                    'container.keterangan as container_id',
                    'statuscontainer.keterangan as statuscontainer_id',
                    'detail.nominalsupir',
                    'detail.nominalkenek',
                    'detail.nominalkomisi',
                    'detail.nominaltol',
                    'detail.liter',
                )
                    ->join('upahsupir as header', 'header.id', 'detail.upahsupir_id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id');
                $upahsupir = $query->get();
            }


            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           
            return response([
                'data' => $upahsupir,
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUpahSupirRincianRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpahSupirRincianRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $upahSupirRincian = new UpahSupirRincian();

            $upahSupirRincian->upahsupir_id = $request->upahsupir_id;
            $upahSupirRincian->container_id = $request->container_id;
            $upahSupirRincian->statuscontainer_id = $request->statuscontainer_id;
            $upahSupirRincian->nominalsupir = $request->nominalsupir;
            $upahSupirRincian->nominalkenek = $request->nominalkenek;
            $upahSupirRincian->nominalkomisi = $request->nominalkomisi;
            $upahSupirRincian->nominaltol = $request->nominaltol;
            $upahSupirRincian->liter = $request->liter;
            $upahSupirRincian->modifiedby = auth('api')->user()->name;
            
            $upahSupirRincian->save();
            
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $upahSupirRincian,
                'id' => $upahSupirRincian->id,
                'tabel' => $upahSupirRincian->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
