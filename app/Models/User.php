<?php

namespace App\Models;

use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;




class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function isMandor()
    {
        $role = DB::table('role')->select('id')->where('rolename', 'MANDOR')->first();
        $userMandor = $this->checkUserRole($role);

        if ($userMandor->count()) {
            //check user has mandor
            return $this->select('mandor.id as mandor_id')->rightJoin(DB::raw("mandordetail as mandor  with (readuncommitted)"), 'mandor.user_id', 'user.id')->where('mandor.user_id', $this->id)->first();
        }
        return false;
    }
    public function isAdmin()
    {
        $role = DB::table('role')->select('id')->where('rolename', 'ADMIN')->first();
        $userAdmin = $this->checkUserRole($role);
        if ($userAdmin->count())  return true;
        return false;
    }
    public function isUserPusat()
    {
        $cabang = DB::table('cabang')->select('id')->where('namacabang', 'PUSAT')->first();
        $user = auth()->user();
        $userPusat = $this->where('cabang_id',$cabang->id)->where('id',$user->id)->first();

        if ($userPusat)  return true;
        return false;
    }

    public function checkUserRole($role)
    {
        return $user = $this->select('user.id')
            ->leftJoin(DB::raw("userrole with (readuncommitted)"), 'userrole.user_id', 'user.id')
            ->where('userrole.role_id', $role->id)
            ->where('user.id', $this->id);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if (is_string($value) && $key !== 'password') {
            return $this->attributes[$key] = strtoupper($value);
        }
    }

    public function setRequestParameters()
    {
        $this->params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];
    }

    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function get()
    {
        $this->setRequestParameters();
        $role = request()->role ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)
            ->select(
                "$this->table.id",
                "$this->table.user",
                "$this->table.name",
                "$this->table.email",
                "cabang.namacabang as cabang_id",
                "$this->table.karyawan_id",
                "$this->table.dashboard",
                "parameter.memo as statusaktif",
                "statusakses.memo as statusakses",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
                DB::raw("'Laporan User' as judulLaporan "),
                DB::raw("'" . $getJudul->text . "' as judul "),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as statusakses', 'user.statusakses', '=', 'statusakses.id')
            ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id');

        if ($role) {
            $query
                ->leftJoin('userrole', 'user.id', '=', 'userrole.user_id')
                ->leftJoin('role', 'userrole.role_id', '=', 'role.id')
                ->where('role.rolename', $role);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->string('cabang')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 255)->nullable();
            $table->unsignedBigInteger('statusakses')->nullable();
            $table->string('statusaksesnama', 255)->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS KARYAWAN')
            ->where('subgrp', '=', 'STATUS KARYAWAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuskaryawan = $status->id ?? 0;
        
        $status = Cabang::from(
            db::Raw("cabang with (readuncommitted)")
        )
            ->select(
                'cabang.id',
                'cabang.namacabang'
            )
            ->join(DB::raw("parameter with (readuncommitted)"), 'cabang.id', 'parameter.text')
            ->where('grp', '=', 'ID CABANG')
            ->first();

        $iddefaultcabangid = $status->id ?? 0;
        $iddefaultcabang = $status->namacabang ?? '';

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;
        $defaultstatusaktif = $status->text ?? '';

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKSES')
            ->where('subgrp', '=', 'STATUS AKSES')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusakses = $status->id ?? 0;
        $defaultstatusakses = $status->text ?? 0;


        DB::table($tempdefault)->insert(
            ["karyawan_id" => $iddefaultstatuskaryawan, "cabang_id" => $iddefaultcabangid,"cabang" => $iddefaultcabang, "statusaktif" => $iddefaultstatusaktif, "statusaktifnama" => $defaultstatusaktif, "statusakses" => $iddefaultstatusakses, "statusaksesnama" => $defaultstatusakses]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'karyawan_id',
                'cabang_id',
                'cabang',
                'statusaktif',
                'statusaktifnama',
                'statusaksesnama',
                'statusakses'
            );

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.user",
            "$this->table.name",
            "$this->table.email",
            "cabang.namacabang as cabang_id",
            "$this->table.karyawan_id",
            "$this->table.dashboard",
            "parameter.text as statusaktif",
            "statusakses.memo as statusakses",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at"
        )
            ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as statusakses', 'user.statusakses', '=', 'statusakses.id')
            ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('user', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->longText('email')->nullable();
            $table->string('cabang_id', 300)->nullable();
            $table->bigInteger('karyawan_id')->length(11)->nullable();
            $table->string('dashboard', 255)->nullable();
            $table->string('statusaktif', 300)->nullable();
            $table->string('statusakses', 300)->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing(['id', 'user', 'name','email', 'cabang_id', 'karyawan_id', 'dashboard', 'statusaktif','statusakses', 'modifiedby', 'created_at', 'updated_at'], $models);

        return  $temp;
    }

    public function lockAndDestroy($identifier, string $field = 'id'): Model
    {
        $table = $this->getTable();
        $model = $this->where($field, $identifier)->lockForUpdate()->first();

        if ($model) {
            $isDeleted = $model->where($field, $identifier)->delete();

            if ($isDeleted) {
                return $model;
            }

            throw new Exception("Error deleting '$field' '$identifier' in '$table'");
        }

        throw new ModelNotFoundException("No data found for '$field' '$identifier' in '$table'");
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusakses') {
                            $query = $query->where('statusakses.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->where('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', '=', $filters['data']);
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->where('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format([user]." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('[' . $this->table . "].[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusakses') {
                            $query = $query->orWhere('statusakses.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->orWhere('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang.namacabang') {
                            $query = $query->orWhere('cabang_id', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->orWhere('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format([user]." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw('[' . $this->table . "].[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'userrole')->withTimestamps();
    }

    public function acls()
    {
        return $this->belongsToMany(Aco::class, 'useracl')
            ->withTimestamps()
            ->select(
                'acos.id',
                'acos.class',
                'acos.method',
                'acos.nama',
                'acos.modifiedby',
                'useracl.created_at',
                'useracl.updated_at'
            );
    }

    public function processStore(array $data): User
    {
        $user = new User();
        $user->user = strtoupper($data['user']);
        $user->name = strtoupper($data['name']);
        $user->email = strtoupper($data['email']);
        $user->password = Hash::make($data['password']);
        $user->cabang_id = $data['cabang_id'] ?? '';
        $user->karyawan_id = $data['karyawan_id'] ?? '';
        $user->dashboard = strtoupper($data['dashboard']);
        $user->statusaktif = $data['statusaktif'];
        $user->statusakses = $data['statusakses'];
        $user->modifiedby = auth('api')->user()->name;

        if (!$user->save()) {
            throw new \Exception('Error storing user.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($user->getTable()),
            'postingdari' => 'ENTRY USER',
            'idtrans' => $user->id,
            'nobuktitrans' => $user->id,
            'aksi' => 'ENTRY',
            'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
            'modifiedby' => $user->modifiedby
        ]);

        return $user;
    }

    public function processUpdate(User $user, array $data): User
    {
        $user->user = $data['user'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->cabang_id = $data['cabang_id'] ?? '';
        $user->karyawan_id = $data['karyawan_id'] ?? '';
        $user->dashboard = $data['dashboard'];
        $user->statusaktif = $data['statusaktif'];
        $user->statusakses = $data['statusakses'];
        $user->modifiedby = auth('api')->user()->user;
        $user->info = html_entity_decode(request()->info);

        if (!$user->save()) {
            throw new \Exception('Error updating user.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($user->getTable()),
            'postingdari' => 'EDIT USER',
            'idtrans' => $user->id,
            'nobuktitrans' => $user->id,
            'aksi' => 'EDIT',
            'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
            'modifiedby' => $user->modifiedby
        ]);

        // USER ROLE
        UserRole::where('user_id', $user->id)->delete();
        if ($data['role_ids'] != '') {

            $roles = [];
            for ($i = 0; $i < count($data['role_ids']); $i++) {
                $aco = (new UserRole())->processStore([
                    'role_id' => $data['role_ids'][$i],
                    'user_id' => $user->id,
                ]);
                $roles[] = $aco->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper('userrole'),
                'postingdari' => 'ENTRY USER ROLE',
                'idtrans' => $user->id,
                'nobuktitrans' => $user->id,
                'aksi' => 'ENTRY',
                'datajson' => $roles,
                'modifiedby' => $user->modifiedby
            ]);
        }

        // USER ACL
        UserAcl::where('user_id', $user->id)->delete();
        $acos = [];
        for ($i = 0; $i < count($data['aco_ids']); $i++) {
            $aco = (new UserAcl())->processStore([
                'aco_id' => $data['aco_ids'][$i],
                'user_id' => $user->id,
            ]);
            $acos[] = $aco->toArray();
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper('useracl'),
            'postingdari' => 'ENTRY USER ACL',
            'idtrans' => $user->id,
            'nobuktitrans' => $user->id,
            'aksi' => 'ENTRY',
            'datajson' => $acos,
            'modifiedby' => $user->modifiedby
        ]);

        return $user;
    }

    public function processDestroy($id): User
    {
        $user = new User();
        $user = $user->lockAndDestroy($id);

        $getuserrole = DB::table("UserRole")->from(
            DB::raw("userrole with (readuncommitted)")
        )
            ->select('id')
            ->where('user_id', $id)->get();
        $datadetail = json_decode($getuserrole, true);

        foreach ($datadetail as $item) {
            $userrole = (new UserRole())->processDestroy($item['id']);
        }


        $getuseracl = DB::table("UserAcl")->from(
            DB::raw("useracl with (readuncommitted)")
        )
            ->select('id')
            ->where('user_id', $id)->get();
        $datadetail = json_decode($getuseracl, true);
        foreach ($datadetail as $item) {
            $useracl = (new UserAcl())->processDestroy($item['id']);
        }



        (new LogTrail())->processStore([
            'namatabel' => strtoupper($user->getTable()),
            'postingdari' => 'DELETE USER',
            'idtrans' => $user->id,
            'nobuktitrans' => $user->id,
            'aksi' => 'DELETE',
            'datajson' => $user->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $user;
    }

    public function findAll($id)
    {
        $query = DB::table("[user]")->from(DB::raw("[user] with (readuncommitted)"))
            ->select(DB::raw("[user].id, [user].[user], [user].[name], [user].email, [user].cabang_id, cabang.namacabang as cabang, [user].dashboard, [user].karyawan_id, [user].statusaktif, [user].statusakses, statusaktif.text as statusaktifnama, statusakses.text as statusaksesnama"))
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), DB::raw("[user].cabang_id"), 'cabang.id')
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), DB::raw("[user].statusaktif"), 'statusaktif.id')
            ->leftJoin(DB::raw("parameter as statusakses with (readuncommitted)"), DB::raw("[user].statusakses"), 'statusakses.id')
            ->whereRaw("[user].id = $id");

        return $query->first();
    }

    public function getRole($id)
    {
        $query = DB::table("userrole")->from(DB::raw("userrole with (readuncommitted)"))
            ->where('user_id', $id);
        return $query->get();
    }
}
