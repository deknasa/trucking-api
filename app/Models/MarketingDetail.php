<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarketingDetail extends Model
{
    use HasFactory;
    protected $table = 'marketingdetail';

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
        // dd($id);
        $query = DB::table("marketingdetail")->from(DB::raw("marketingdetail"))
            ->select('user_id')
            ->where('marketing_id', $id)
            ->get();
            
        return $query;
    }
    
    public function processStore( array $data, MarketingDetail $marketingDetail): MarketingDetail
    {
        // dd( $data);
        // $mandorDetail = new MandorDetail();
        $marketingDetail->marketing_id = $data['marketing_id'];
        $marketingDetail->user_id =  $data['user_id'];
        $marketingDetail->tas_id = $data['tas_id'] ?? '';
        $marketingDetail->modifiedby = auth('api')->user()->name;
        $marketingDetail->info = html_entity_decode(request()->info);

        if (!$marketingDetail->save()) {
            throw new \Exception("Error storing marketing detail.");
        }

        return $marketingDetail;
    }

    public function processDestroy(MarketingDetail $marketingDetail,$idheader): MarketingDetail
    {

        // dd($idheader);
        // $mandor = new Mandor();
        // dd($mandorDetail->get());
        MarketingDetail::where('marketing_id', $idheader)->delete();



        (new LogTrail())->processStore([
            'namatabel' => 'MARKETINGDETAIL',
            'postingdari' => 'DELETE MARKETING DETAIL',
            'idtrans' => $idheader,
            'nobuktitrans' => $idheader,
            'aksi' => 'DELETE',
            'datajson' => '',
            'modifiedby' => auth('api')->user()->name
        ]);

        return $marketingDetail;
    }

}
