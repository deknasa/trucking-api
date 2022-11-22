<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahRitasiRincian;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpahRitasiRincianController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'upahritasi_id' => $request->upahritasi_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = UpahRitasiRincian::from('upahritasirincian as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['upahritasi_id'])) {
                $query->where('detail.upahritasi_id', $params['upahritasi_id']);
            }

            if ($params['withHeader']) {
                $query->join('upahritasi', 'upahritasi.id', 'detail.upahritasi_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('upahritasi_id', $params['whereIn']);
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
                    ->join('upahritasi as header', 'header.id', 'detail.upahritasi_id') 
                    ->join('kota as kotadari', 'kotadari.id', '=', 'header.kotadari_id')
                    ->join('kota as kotasampai', 'kotasampai.id', '=', 'header.kotasampai_id')
                    ->leftJoin('zona', 'header.zona_id', 'zona.id')
                    ->leftJoin('parameter as statusluarkota', 'header.statusluarkota', 'statusluarkota.id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id');

                $upahritasi = $query->get();
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
                    ->join('upahritasi as header', 'header.id', 'detail.upahritasi_id')
                    ->leftJoin('container', 'container.id', 'detail.container_id')
                    ->leftJoin('statuscontainer', 'statuscontainer.id', 'detail.statuscontainer_id');

                $upahritasi = $query->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           
            return response([
                'data' => $upahritasi,
                'user' => $getuser,
                
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StoreUpahRitasiRincianRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $upahritasirincian = new UpahRitasiRincian();

            $upahritasirincian->upahritasi_id = $request->upahritasi_id;
            $upahritasirincian->container_id = $request->container_id;
            $upahritasirincian->statuscontainer_id = $request->statuscontainer_id;
            $upahritasirincian->nominalsupir = $request->nominalsupir;
            $upahritasirincian->nominalkenek = $request->nominalkenek;
            $upahritasirincian->nominalkomisi = $request->nominalkomisi;
            $upahritasirincian->nominaltol = $request->nominaltol;
            $upahritasirincian->liter = $request->liter;
            $upahritasirincian->modifiedby = auth('api')->user()->name;
            
            $upahritasirincian->save();
            
            DB::commit();
           
            return [
                'error' => false,
                'id' => $upahritasirincian->id,
                'tabel' => $upahritasirincian->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }        
    }

}
