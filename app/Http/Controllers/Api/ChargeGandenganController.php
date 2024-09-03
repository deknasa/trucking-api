<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChargeGandengan;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class ChargeGandenganController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $chargeGandengan = new ChargeGandengan();
        return response([
            'data' => $chargeGandengan->get(),
            'attributes' => [
                'totalRows' => $chargeGandengan->totalRows,
                'totalPages' => $chargeGandengan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        // dd(request()->cekExport);
        if (request()->cekExport) {
            if (request()->offset == "-1" && request()->limit == '1') {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        }
        $chargeGandengan = new ChargeGandengan();

        $dari = $request->dari;
        $sampai = $request->sampai;

        $response = $chargeGandengan->getExport($dari, $sampai);
        $decodedResponse = json_decode(json_encode($response), true);
        $judulLaporan = "Charge Gandengan";
        $chargeGandengan = $decodedResponse;
        // $chargeGandengan = $decodedResponse['data'];
        $chargeGandengan[0]['judul'] = $judulLaporan;
        // dd($decodedResponse);

        $columns = [
            [
                "label" => "No",
            ],
            [
                "index" => "jobtrucking",
                "label" => "job trucking",
            ],
            [
                "index" => "gandengan",
                "label" => "gandengan",
            ],
            [
                "index" => "tglawal",
                "label" => "tgl awal",
            ],
            [
                "index" => "tglkembali",
                "label" => "tgl kembali",
            ],
            [
                "index" => "jumlahhari",
                "label" => "jumlah hari",
            ],
            [
                "index" => "jenisorder",
                "label" => "jenis order",
            ],
            [
                "index" => "namaemkl",
                "label" => "nama emkl",
            ],
            [
                "index" => "ukurancontainer",
                "label" => "ukuran container",
            ],
            [
                "index" => "nojob",
                "label" => "nojob",
            ],
            [
                "index" => "nojob2",
                "label" => "nojob2",
            ],
            [
                "index" => "nocont",
                "label" => "nocont",
            ],
            [
                "index" => "nocont2",
                "label" => "nocont2",
            ],
            [
                "index" => "trado",
                "label" => "trado",
            ],
            [
                "index" => "supir",
                "label" => "supir",
            ],
            [
                "index" => "namagudang",
                "label" => "nama gudang",
            ],
            [
                "index" => "noinvoice",
                "label" => "no. invoice",
            ],
        ];

        // return $chargeGandengan;
        $this->toExcel($judulLaporan, $chargeGandengan, $columns);
    }
}
