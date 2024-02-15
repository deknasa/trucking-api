<?php
namespace App\Services;

use App\Models\LogTrail;
use App\Models\ReminderEmail;
use App\DataTransferObject\ReminderEmailDTO;

class ReminderEmailService
{
    public function store(array $dto) : ReminderEmail
    {

        $reminderEmail = new ReminderEmail();
        $reminderEmail->keterangan = $dto['keterangan'];
        $reminderEmail->statusaktif = $dto['statusaktif'];
        $reminderEmail->tas_id = $dto['tas_id'];
        $reminderEmail->modifiedby = auth('api')->user()->name;
        $reminderEmail->info = html_entity_decode(request()->info);

        if (!$reminderEmail->save()) {
            throw new \Exception("Error Storing reminder Email.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($reminderEmail->getTable()),
            'postingdari' => 'ENTRY To Email',
            'idtrans' => $reminderEmail->id,
            'nobuktitrans' => $reminderEmail->id,
            'aksi' => 'ENTRY',
            'datajson' => $reminderEmail->toArray(),
            'modifiedby' => $reminderEmail->modifiedby
        ]);

        return $reminderEmail;
    }

    function update(ReminderEmail $reminderEmail, array $dto) : ReminderEmail{
        $reminderEmail->keterangan = $dto['keterangan'];
        $reminderEmail->statusaktif = $dto['statusaktif'];
        $reminderEmail->modifiedby = auth('api')->user()->name;
        $reminderEmail->info = html_entity_decode(request()->info);

        if (!$reminderEmail->save()) {
            throw new \Exception("Error Storing reminder Email.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($reminderEmail->getTable()),
            'postingdari' => 'ENTRY To Email',
            'idtrans' => $reminderEmail->id,
            'nobuktitrans' => $reminderEmail->id,
            'aksi' => 'ENTRY',
            'datajson' => $reminderEmail->toArray(),
            'modifiedby' => $reminderEmail->modifiedby
        ]);

        return $reminderEmail;
    }
}