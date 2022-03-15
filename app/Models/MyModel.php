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
        
        if (is_string($value)) {
            return $this->attributes[$key] = strtoupper($value);
        }
    }
}
