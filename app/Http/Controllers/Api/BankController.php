<?php

namespace App\Http\Controllers\Api;

use App\Models\Bank;
use App\Models\AkunPusat;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBankRequest;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class BankController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $bank = new Bank();
        return response([
            'data' => $bank->get(),
            'attributes' => [
                'totalRows' => $bank->totalRows,
                'totalPages' => $bank->totalPages
            ]
        ]);
    }

    public function cekValidasi($id) {
        $bank= new Bank();
        $cekdata=$bank->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
                )
            ->where('kodeerror', '=', 'SATL')
            ->get();
        $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
         
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data); 
        }
    }
    public function default()
    {
        $bank = new Bank();
        return response([
            'status' => true,
            'data' => $bank->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreBankRequest $request)
    {
        DB::beginTransaction();
        try {
            $bank = new Bank();
            $bank->kodebank = $request->kodebank;
            $bank->namabank = $request->namabank;
            $bank->coa = $request->coa;
            $bank->tipe = $request->tipe;
            $bank->statusaktif = $request->statusaktif;
            $bank->formatpenerimaan = $request->formatpenerimaan;
            $bank->formatpengeluaran = $request->formatpengeluaran;
            $bank->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($bank->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bank->getTable()),
                    'postingdari' => 'ENTRY BANK',
                    'idtrans' => $bank->id,
                    'nobuktitrans' => $bank->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bank->toArray(),
                    'modifiedby' => $bank->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */


            $selected = $this->getPosition($bank, $bank->getTable());
            $bank->position = $selected->position;
            $bank->page = ceil($bank->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bank,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Bank $bank)
    {
        return response([
            'status' => true,
            'data' => $bank
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateBankRequest $request, Bank $bank)
    {
        DB::beginTransaction();
        try {
            $bank->kodebank = $request->kodebank;
            $bank->namabank = $request->namabank;
            $bank->coa = $request->coa;
            $bank->tipe = $request->tipe;
            $bank->statusaktif = $request->statusaktif;
            $bank->formatpenerimaan = $request->formatpenerimaan;
            $bank->formatpengeluaran = $request->formatpengeluaran;
            $bank->modifiedby = auth('api')->user()->name;

            if ($bank->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bank->getTable()),
                    'postingdari' => 'EDIT BANK',
                    'idtrans' => $bank->id,
                    'nobuktitrans' => $bank->id,
                    'aksi' => 'EDIT',
                    'datajson' => $bank->toArray(),
                    'modifiedby' => $bank->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
                $selected = $this->getPosition($bank, $bank->getTable());
                $bank->position = $selected->position;
                $bank->page = ceil($bank->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $bank
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyBankRequest $request, $id)
    {
        DB::beginTransaction();

        $bank = new Bank();
        $bank = $bank->lockAndDestroy($id);
        
        if ($bank) {
            $logTrail = [
                'namatabel' => strtoupper($bank->getTable()),
                'postingdari' => 'DELETE BANK',
                'idtrans' => $bank->id,
                'nobuktitrans' => $bank->id,
                'aksi' => 'DELETE',
                'datajson' => $bank->toArray(),
                'modifiedby' => $bank->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();

            $selected = $this->getPosition($bank, $bank->getTable(), true);
            $bank->position = $selected->position;
            $bank->id = $selected->id;
            $bank->page = ceil($bank->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $bank
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
   
        ];

        return response([
            'data' => $data
        ]);
    }



    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bank')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $banks = $decodedResponse['data'];


        $i = 0;
        foreach ($banks as $index => $params) {

            $statusaktif = $params['statusaktif'];
            $statusDefault = $params['statusdefault'];
            $formatPenerimaan = $params['formatpenerimaan'];
            $formatPengeluaran = $params['formatpengeluaran'];

            $result = json_decode($statusaktif, true);
            $resultDefault = json_decode($statusDefault, true);
            $resultPengeluaran = json_decode($formatPengeluaran, true);
            $resultPenerimaan = json_decode($formatPenerimaan, true);

            $statusaktif = $result['MEMO'];
            $statusDefault = $resultDefault['MEMO'];
            $formatPenerimaan = $resultPengeluaran['MEMO'];
            $formatPengeluaran = $resultPenerimaan['MEMO'];


            $banks[$i]['statusaktif'] = $statusaktif;
            $banks[$i]['statusdefault'] = $statusDefault;
            $banks[$i]['formatpenerimaan'] = $formatPenerimaan;
            $banks[$i]['formatpengeluaran'] = $formatPengeluaran;


            $i++;
        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Bank',
                'index' => 'kodebank',
            ],
            [
                'label' => 'Nama Bank',
                'index' => 'namabank',
            ],
            [
                'label' => 'COA',
                'index' => 'coa',
            ],
            [
                'label' => 'Tipe',
                'index' => 'tipe',
            ],
            [
                'label' => 'Status Default',
                'index' => 'statusdefault',
            ],
            [
                'label' => 'Format Penerimaan',
                'index' => 'formatpenerimaan',
            ],
            [
                'label' => 'Format Pengeluaran',
                'index' => 'formatpengeluaran',
            ],
            [
                'label' => 'Status AKtif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Bank', $banks, $columns);
    }
}
