<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NotaDebetFifo extends Model
{
    use HasFactory;
    protected $table = 'notadebetfifo';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(array $data): NotaDebetFifo
    {

        $nominal = $data['nominal'] ?? 0;
        $agen_id = $data['agen_id'] ?? 0;
        $pelunasanpiutang_id = $data['pelunasanpiutang_id'] ?? 0;
        $pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';


        $tempnotadebetfifo = '##tempnotadebetfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnotadebetfifo, function ($table) {
            $table->string('notadebet_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->bigInteger('id')->nullable();
        });





        // dd('test');
        // dd(db::table($tempnotadebetfifo)->get());
        $a = 0;
        $kondisi = true;
        while ($kondisi == true) {

            DB::delete(DB::raw("delete " . $tempnotadebetfifo));


            $querynotadebetfifo = db::table('notadebetfifo')->from(db::raw("notadebetfifo a with (readuncommitted)"))
                ->select(
                    'a.notadebet_nobukti as nobukti',
                    db::raw("sum(a.nominal) as nominal"),
                    db::raw("max(b.id) as id"),
                )
                ->join(db::raw("notadebetheader b with (readuncommitted)"), 'a.notadebet_nobukti', 'b.nobukti')
                ->where('b.agen_id', '=',   $agen_id)
                ->groupBY('a.notadebet_nobukti');

            DB::table($tempnotadebetfifo)->insertUsing([
                'notadebet_nobukti',
                'nominal',
                'id',
            ], $querynotadebetfifo);

            $querysisa = db::table('notadebetrincian')->from(db::raw("notadebetrincian a with (readuncommitted)"))
                ->select(
                    db::raw("(a.nominal-isnull(B.nominal,0)) as nominalsisa"),
                    'a.nobukti',
                    'a.nominal',
                    'c.id as notadebet_id',
                )
                ->leftjoin(db::raw($tempnotadebetfifo . " b "), 'a.nobukti', 'b.notadebet_nobukti')
                ->join(db::raw("notadebetheader c "), 'a.nobukti', 'c.nobukti')
                ->where('c.agen_id', $agen_id)
                ->whereRaw("(a.nominal-isnull(B.nominal,0))<>0")
                ->orderBy('a.id', 'asc')
                ->first();
            $a = $a + 1;

            // dump($a);
            // dump($nominal);
            // dump($querysisa);

            if (isset($querysisa)) {
                $nominalsisa = $querysisa->nominalsisa ?? 0;
                if ($nominal <= $nominalsisa) {
                    // dd('test1');
                    $notadebetFifo = new notadebetFifo();
                    $notadebetFifo->pelunasanpiutang_id = $pelunasanpiutang_id;
                    $notadebetFifo->pelunasanpiutang_nobukti = $pelunasanpiutang_nobukti;
                    $notadebetFifo->agen_id = $agen_id;
                    $notadebetFifo->notadebet_id = $querysisa->notadebet_id ?? '';
                    $notadebetFifo->nominal = $nominal ?? 0;
                    $notadebetFifo->notadebet_nobukti = $querysisa->nobukti ?? '';
                    $notadebetFifo->notadebet_nominal = $querysisa->nominal ?? 0;
                    $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
                    $kondisi = false;
                    if (!$notadebetFifo->save()) {
                        throw new \Exception("Error Simpan Nota Debet Detail fifo.");
                    }
                } else {
                    // dd('test');
                    $nominal = $nominal - $nominalsisa;
                    $notadebetFifo = new notadebetFifo();
                    $notadebetFifo->pelunasanpiutang_id = $pelunasanpiutang_id;
                    $notadebetFifo->pelunasanpiutang_nobukti = $pelunasanpiutang_nobukti;
                    $notadebetFifo->notadebet_id = $querysisa->notadebet_id ?? '';
                    $notadebetFifo->agen_id = $agen_id;
                    $notadebetFifo->nominal = $nominalsisa ?? 0;
                    $notadebetFifo->notadebet_nobukti = $querysisa->nobukti ?? '';
                    $notadebetFifo->notadebet_nominal = $querysisa->nominal ?? 0;
                    $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
                    if (!$notadebetFifo->save()) {
                        throw new \Exception("Error Simpan Nota Debet Detail fifo.");
                    }
                }
            }
        }
        // dd('test1');
        return $notadebetFifo;
    }
    public function processStoreNotFifo(array $data): NotaDebetFifo
    {
        $notaDebetDetail = NotaDebetDetail::where('nobukti',$data['notadebet_nobukti'])->get();
        $lebihbayar = $notaDebetDetail->sum('lebihbayar') ?? 0;

        $notadebetFifo = new NotaDebetFifo();
        $notadebetFifo->pelunasanpiutang_id = $data['pelunasanpiutang_id'];
        $notadebetFifo->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'];
        $notadebetFifo->agen_id = $data['agen_id'];
        $notadebetFifo->notadebet_id = $notaDebetDetail[0]->notadebet_id ?? '';
        $notadebetFifo->nominal = $data['nominal'] ?? 0;
        $notadebetFifo->notadebet_nobukti = $data['notadebet_nobukti'] ?? '';
        $notadebetFifo->notadebet_nominal = $lebihbayar ?? 0;
        $notadebetFifo->modifiedby = $data['modifiedby'] ?? '';
        if (!$notadebetFifo->save()) {
            throw new \Exception("Error Simpan Nota Debet Detail fifo.");
        }
        return $notadebetFifo;
    }
}
