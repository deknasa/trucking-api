<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Kategori;
use App\Models\Parameter;
use App\Models\SubKelompok;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Requests\DestroyKategoriRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class KategoriController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $kategori = new Kategori();

        return response([
            'data' => $kategori->get(),
            'attributes' => [
                'totalRows' => $kategori->totalRows,
                'totalPages' => $kategori->totalPages
            ]
        ]);
    }

    public function cekValidasi($id, request $request)
    {

        
  
        $kategori = new Kategori();
        $dataMaster = $kategori->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekdata = $kategori->cekvalidasihapus($id);

        $aksi=$request->aksi ?? '';
        $acoid = db::table('acos')->from(db::raw("acos a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.class', 'kategori')
            ->where('a.method', 'update')
            ->first()->id ?? 0;
        $userid = auth('api')->user()->id;

        $data = (new MyModel())->hakuser($userid, $acoid);
        if ($data == true) {
            $hakutama = 1;
        } else {
            $hakutama = 0;
        }
        if ($aksi=='edit') {
            if ($cekdata['kondisi'] == true) {
                if ($hakutama == 1) {
                    $cekdata['kondisi'] = false;
                }
            }
    
        }

        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('kategori', $id, $aksi);
                }
                
                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];
                
                // return response($data);
            } else {
                
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('kategori', $id, $aksi);
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
        $kategori = new Kategori();
        return response([
            'status' => true,
            'data' => $kategori->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreKategoriRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodekategori' => $request->kodekategori ?? '',
                'keterangan' => $request->keterangan ?? '',
                'subkelompok_id' => $request->subkelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $kategori = (new Kategori())->processStore($data);
            $kategori->position = $this->getPosition($kategori, $kategori->getTable())->position;
            if ($request->limit == 0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $kategori->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('kategori', 'add', $data);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kategori
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $kategori = new Kategori();
        return response([
            'status' => true,
            'data' => $kategori->find($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateKategoriRequest $request, Kategori $kategori): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodekategori' => $request->kodekategori ?? '',
                'keterangan' => $request->keterangan ?? '',
                'subkelompok_id' => $request->subkelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $kategori = (new Kategori())->processUpdate($kategori, $data);
            $kategori->position = $this->getPosition($kategori, $kategori->getTable())->position;
            if ($request->limit == 0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $kategori->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('kategori', 'edit', $data);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyKategoriRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $kategori = (new Kategori())->processDestroy($id);
            $selected = $this->getPosition($kategori, $kategori->getTable(), true);
            $kategori->position = $selected->position;
            $kategori->id = $selected->id;
            if ($request->limit == 0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('kategori', 'delete', $data);
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Kategori())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kategori')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'subkelompok' => SubKelompok::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {

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
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $kategoris = $decodedResponse['data'];

            $judulLaporan = $kategoris[0]['judulLaporan'];

            $i = 0;
            foreach ($kategoris as $index => $params) {

                $statusaktif = $params['status'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $kategoris[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode kategori',
                    'index' => 'kodekategori',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Sub Kelompok',
                    'index' => 'subkelompok',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $kategoris, $columns);
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA USER
     */
    public function updateuser()
    {
    }
}
