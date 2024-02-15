<?php
namespace App\DataTransferObject;

use App\Http\Requests\StoreReminderEmailRequest;

class ReminderEmailDTO
{
    public string $keterangan;
    public string $statusaktif;

    public ?string $tas_id;
    public ?string $accessTokenTnl;



    public function __construct(
        string $keterangan,
        string $statusaktif,

        ?string $tas_id,
        ?string $accessTokenTnl

    ){
        $this->keterangan = $keterangan;
        $this->statusaktif = $statusaktif;
        $this->tas_id = $tas_id;
        $this->accessTokenTnl = $accessTokenTnl;
    }


    public static function dataRequest(StoreReminderEmailRequest $request){
        return new self(
            $request->input('keterangan'),
            $request->input('statusaktif'),
            $request->input('tas_id'),
            $request->input('accessTokenTnl')
        );
    }
}

