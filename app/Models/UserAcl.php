<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UserAcl extends MyModel
{
    use HasFactory;

    protected $table = 'useracl';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            'acos.id as acos_id',
            '[user].id as acos_id',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at,
            $this->table.statusformat"
            )
        )
            ->leftJoin('acos', 'useracl.acos_id', 'acos.id')
            ->leftJoin('[user]', 'useracl.[user].id', '[user].id');

    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->unsignedBigInteger('aco_id')->default('0');
            $table->unsignedBigInteger('user_id')->default('0');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'aco_id', 'user_id', 'modifiedby', 'created_at', 'updated_at'], $models);

        return  $temp;
    }
}
