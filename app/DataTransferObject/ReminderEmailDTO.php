<?php
namespace App\DataTransferObject;

use App\Http\Requests\StoreReminderEmailRequest;

class ReminderEmailDTO
{
    public string $keterangan;
    public string $statusaktif;
    public ? string $accessTokenTnl;
    public ? int $tas_id;

    public function __construct(
        string $keterangan,
        string $statusaktif,
        ? string $accessTokenTnl,
        ? int $tas_id
    ){
        $this->keterangan = $keterangan;
        $this->statusaktif = $statusaktif;
        $this->accessTokenTnl = $accessTokenTnl;
        $this->tas_id = $tas_id;
    }


    public static function dataRequest(StoreReminderEmailRequest $request){
        return new self(
            $request->input('keterangan'),
            $request->input('statusaktif'),
            $request->input('accessTokenTnl'),
            $request->input('tas_id')
        );
    }
}

