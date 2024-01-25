<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStokReuse extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'approvalstokreuse';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function processStokReuseStore(Stok $stok) : ApprovalStokReuse {
        $approvalStokReuse = new ApprovalStokReuse();
        $checkStok = $approvalStokReuse->where('stok_id',$stok->id)->first();
        if ($checkStok) {
            $approvalStokReuse = $checkStok;
        }
        
        $approvalStokReuse->stok_id = $stok->id;
        $approvalStokReuse->info = html_entity_decode(request()->info);
        $approvalStokReuse->modifiedby = auth('api')->user()->name;
        $approvalStokReuse->updated_at = date('Y-m-d H:i:s');
        $approvalStokReuse->save();
        return $approvalStokReuse;

    }

}
