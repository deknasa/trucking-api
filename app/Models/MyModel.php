<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    private function mustUppercase($key): bool
    {
        return true;
    }
}
