<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiSupirApprovalHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirapprovalheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'absensisupirapprovalheader.id',
            'absensisupirapprovalheader.nobukti',
            'absensisupirapprovalheader.tglbukti',
            'absensisupirapprovalheader.absensisupir_nobukti',
            'absensisupirapprovalheader.keterangan',
            'statusapproval.memo as statusapproval',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglapproval,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglapproval end) as tglapproval"),
            'absensisupirapprovalheader.userapproval',
            'statusformat.memo as statusformat',
            'absensisupirapprovalheader.pengeluaran_nobukti',
            'absensisupirapprovalheader.coakaskeluar',
            'absensisupirapprovalheader.postingdari',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglkaskeluar,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglkaskeluar end) as tglkaskeluar"),
            'statuscetak.memo as statuscetak',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglbukacetak,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglbukacetak end) as tglbukacetak"),
            'absensisupirapprovalheader.userbukacetak',
            'absensisupirapprovalheader.jumlahcetak',
            'absensisupirapprovalheader.modifiedby',
            'absensisupirapprovalheader.updated_at',
            'absensisupirapprovalheader.created_at',
        )
            ->leftJoin('parameter as statusapproval', 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusformat', 'absensisupirapprovalheader.statusformat', 'statusformat.id');




        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('absensisupir_nobukti', 50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('statusapproval',1000)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('userapproval', 200)->default('');
            $table->string('statusformat',1000)->default('');
            $table->string('pengeluaran_nobukti', 50)->default('');
            $table->string('coakaskeluar', 50)->default('');
            $table->string('postingdari', 50)->default('');
            $table->date('tglkaskeluar')->default('1900/1/1');
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 1000)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });
        

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'absensisupir_nobukti',
            'keterangan',
            'statusapproval',
            'tglapproval',
            'userapproval',
            'statusformat',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar', 
            'statuscetak', 
            'userbukacetak', 
            'tglbukacetak', 
            'jumlahcetak',
            'modifiedby',
            'created_at','updated_at'
        ], $models);

        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.absensisupir_nobukti,
                $this->table.keterangan,
                'statusapproval.text as statusapproval',
                $this->table.tglapproval,
                $this->table.userapproval,
                'statusformat.text as statusformat',
                $this->table.pengeluaran_nobukti,
                $this->table.coakaskeluar,
                $this->table.postingdari,
                $this->table.tglkaskeluar,
                'statuscetak.text as statuscetak',
                $this->table . userbukacetak,
                $this->table . tglbukacetak,
                $this->table . jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )
        )
        ->leftJoin('absensisupirheader', 'absensisupirapprovalheader.nobukti', 'absensisupirheader.nobukti')
        ->leftJoin('parameter as statuscetak' , 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusapproval' , 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
        ->leftJoin('parameter as statusformat' , 'absensisupirapprovalheader.statusformat', 'statusformat.id');
   }

    public function getApproval($nobukti)
    {
        $query = DB::table('absensisupirdetail')
            ->select(
                'absensisupirdetail.keterangan as keterangan_detail',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan',
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.id',
                'trado.keterangan as trado',
                'supirutama.namasupir as supir',
                'trado.id as trado_id',
                'supirutama.id as supir_id',
                'absensisupirheader.kasgantung_nobukti',
            )
            ->leftJoin('absensisupirheader', 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin('trado', 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin('supir as supirutama', 'absensisupirdetail.supir_id', 'supirutama.id')
            ->whereRaw(" EXISTS (
            SELECT absensisupirapprovalheader.absensisupir_nobukti
    FROM absensisupirdetail          
    left join absensisupirapprovalheader on absensisupirapprovalheader.absensisupir_nobukti= absensisupirdetail.nobukti
    WHERE absensisupirapprovalheader.absensisupir_nobukti = absensisupirheader.nobukti
          )")
            ->where('absensisupirdetail.nobukti', $nobukti);
        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                default:
                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('pengeluaranheader', 'absensisupirapprovalheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin('absensisupirheader', 'absensisupirapprovalheader.absensisupir_nobukti', 'absensisupirheader.nobukti')
            ->leftJoin('parameter as statusapproval', 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusformat', 'absensisupirapprovalheader.statusformat', 'statusformat.id');
        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
