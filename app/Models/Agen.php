<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;

class Agen extends MyModel
{
    use HasFactory;
    use RestrictDeletion;

    protected $table = 'agen';
    
    protected $casts = [
        'tglapproval' => 'date:d-m-Y',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function isDeletable()
    {
        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        
        return $this->statusapproval == $statusApproval->id;
    }
}
