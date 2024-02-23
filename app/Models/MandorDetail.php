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
    
    public function processStore(Mandor $mandor, array $data): MandorDetail
    {
        $mandorDetail = new MandorDetail();
        $mandorDetail->mandor_id = $mandor->id;
        $mandorDetail->user_id =  $data['user_id'];
        $mandorDetail->tas_id = $data['tas_id'] ?? '';
        $mandorDetail->modifiedby = auth('api')->user()->name;
        $mandorDetail->info = html_entity_decode(request()->info);

        if (!$mandorDetail->save()) {
            throw new \Exception("Error storing mandor detail.");
        }

        return $mandorDetail;
    }
}
