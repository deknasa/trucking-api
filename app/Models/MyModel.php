<?php

namespace App\Models;

use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class MyModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if (!isset($this->toUppercase) || $this->toUppercase) {
            if (is_string($value)) {
                return $this->attributes[$key] = strtoupper($value);
            }
        }
    }

    public function setRequestParameters()
    {
        $this->params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];
    }

    public function lockAndDestroy($identifier, string $field = 'id'): Model
    {
        $table = $this->getTable();
        $model = $this->where($field, $identifier)->lockForUpdate()->first();

        if ($model) {
            $isDeleted = $model->where($field, $identifier)->delete();

            if ($isDeleted) {
                return $model;
            }

            throw new Exception("Error deleting '$field' '$identifier' in '$table'");
        }

        throw new ModelNotFoundException("No data found for '$field' '$identifier' in '$table'");
    }

    private function mustUppercase($key): bool
    {
        return true;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
