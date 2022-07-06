<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait WithSelectedPosition
{
    public function save(array $options = [])
    {
        parent::save();

        /* Set position and page */
        $selected = $this->getPosition($this, $this->getTable());
        $this->position = $selected->position;
        $this->page = ceil($this->position / (request()->limit ?? 10));

        return $this;
    }

    public function delete()
    {
        parent::delete();

        /* Set position and page */
        $selected = $this->getPosition($this, $this->getTable(), true);
        $this->position = $selected->position;
        $this->page = ceil($this->position / (request()->limit ?? 10));

        return $this;
    }
    
    /**
     * Get data position after
     * add, edit, or delete
     * 
     * @param Model $model
     * @param string $modelTable
     * 
     * @return mixed
     */
    function getPosition(Model $model, string $modelTable, bool $isDeleting = false)
    {
        $indexRow = request()->indexRow ?? 1;
        $limit = request()->limit ?? 10;
        $page = request()->page ?? 1;
        $sortname = request()->sortname ?? "id";
        $sortorder = request()->sortorder ?? "asc";

        $temporaryTable = '##temp' . rand(1, 10000);
        $columns = Schema::getColumnListing($modelTable);

        $models = DB::table($modelTable)->orderBy($modelTable . '.' . $sortname, $sortorder);

        Schema::create($temporaryTable, function (Blueprint $table) use ($columns) {
            $table->increments('position');

            foreach ($columns as $column) {
                $table->string($column)->nullable();
            }

            $table->index('id');
        });

        DB::table($temporaryTable)->insertUsing($columns, $models);

        if ($isDeleting) {
            if ($page == 1) {
                $position = $indexRow + 1;
            } else {
                $page = $page - 1;
                $row = $page * $limit;
                $position = $indexRow + $row + 1;
            }

            if (!DB::table($temporaryTable)->where('position', '=', $position)->exists()) {
                $position -= 1;
            }

            $query = DB::table($temporaryTable)
                ->select('position', 'id')
                ->where('position', '=', $position)
                ->orderBy('position');
        } else {
            $query = DB::table($temporaryTable)->select('position')->where('id', $model->id)->orderBy('position');
        }

        $data = $query->first();

        return $data;
    }
}
