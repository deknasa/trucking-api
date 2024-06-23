<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MandorDetail extends Model
{
    use HasFactory;

    protected $table = 'mandordetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findAll($id)
    {
        $query = DB::table("mandordetail")->from(DB::raw("mandordetail"))
            ->select('user_id')
            ->where('mandor_id', $id)
            ->get();
        return $query;
    }
    
    public function processStore( array $data, MandorDetail $mandorDetail): MandorDetail
    {
        // dd( $data);
        // $mandorDetail = new MandorDetail();
        $mandorDetail->mandor_id = $data['mandor_id'];
        $mandorDetail->user_id =  $data['user_id'];
        $mandorDetail->tas_id = $data['tas_id'] ?? '';
        $mandorDetail->modifiedby = auth('api')->user()->name;
        $mandorDetail->info = html_entity_decode(request()->info);

        if (!$mandorDetail->save()) {
            throw new \Exception("Error storing mandor detail.");
        }

        return $mandorDetail;
    }

    public function processDestroy(MandorDetail $mandorDetail,$idheader): MandorDetail
    {

        // dd($idheader);
        // $mandor = new Mandor();
        // dd($mandorDetail->get());
        MandorDetail::where('mandor_id', $idheader)->delete();



        (new LogTrail())->processStore([
            'namatabel' => 'MANDORDETAIL',
            'postingdari' => 'DELETE MANDOR DETAIL',
            'idtrans' => $idheader,
            'nobuktitrans' => $idheader,
            'aksi' => 'DELETE',
            'datajson' => '',
            'modifiedby' => auth('api')->user()->name
        ]);

        return $mandorDetail;
    }

}
