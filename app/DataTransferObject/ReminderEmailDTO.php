<?php
namespace App\DataTransferObject;

use App\Http\Requests\StoreReminderEmailRequest;

class ReminderEmailDTO
{
    public string $keterangan;
    public string $statusaktif;

    public function __construct(
        string $keterangan,
        string $statusaktif
    ){
        $this->keterangan = $keterangan;
        $this->statusaktif = $statusaktif;
    }


    public static function dataRequest(StoreReminderEmailRequest $request){
        return new self(
            $request->input('keterangan'),
            $request->input('statusaktif')
        );
    }
}

