<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderService extends MyModel
{
    use HasFactory;

    public function get()
    {
        $this->setRequestParameters();

        // $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($query);
        // $this->filter($query);
        // $this->paginate($query);

        // $data = $query->get();

        $data = [
            [
                "id"=>"1",
                "nopol"=>"bk 1010 ask",
                "tanggal"=>"2023-01-20",
                "status"=>"asdsdaf",
                "limit"=>"10000",
                "perjalanan"=>"8000",
            ],
            [
                "id"=>"2",
                "nopol"=>"bk 1010 ask",
                "tanggal"=>"2023-01-20",
                "status"=>"asdsdaf",
                "limit"=>"10000",
                "perjalanan"=>"8000",
            ],
            [
                "id"=>"3",
                "nopol"=>"bk 1010 ask",
                "tanggal"=>"2023-01-20",
                "status"=>"asdsdaf",
                "limit"=>"10000",
                "perjalanan"=>"8000",
            ],
            [
                "id"=>"4",
                "nopol"=>"bk 1010 ask",
                "tanggal"=>"2023-01-20",
                "status"=>"asdsdaf",
                "limit"=>"10000",
                "perjalanan"=>"8000",
            ],
            [
                "id"=>"5",
                "nopol"=>"bk 1010 ask",
                "tanggal"=>"2023-01-20",
                "status"=>"asdsdaf",
                "limit"=>"10000",
                "perjalanan"=>"8000",
            ],

            
        ];

        return $data;
    }
}
