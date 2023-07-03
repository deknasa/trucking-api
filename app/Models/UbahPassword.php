<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UbahPassword extends MyModel
{
    use HasFactory;

    protected $table = 'user';

    
    public function processUpdate(User $user, array $data): User
    {
        $user->password = $data['password'];
        $user->modifiedby = auth('api')->user()->name;

        if (!$user->save()) {
            throw new \Exception('Error updating password user.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($user->getTable()),
            'postingdari' => 'EDIT USER',
            'idtrans' => $user->id,
            'nobuktitrans' => $user->id,
            'aksi' => 'EDIT',
            'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
            'modifiedby' => $user->modifiedby
        ]);

        return $user;
    }


}