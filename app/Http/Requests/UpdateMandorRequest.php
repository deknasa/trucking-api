<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Mandor;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateMandorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $mandor = (new Mandor())->select('user.name')
        ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'mandor.user_id', '=', 'user.id')
        ->where('mandor.id','<>',$this->id)
        ->get();
        $namauser=[];
        foreach ($mandor as $item) {
            if ($item->name) {
                $namauser[] = $item->name;
            }
        } 
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

        return [
            'namamandor' => ['required',Rule::unique('mandor')->whereNotIn('id', [$this->id])],
            'statusaktif' => ['required', Rule::in($status)],
            'user' => ['nullable', Rule::notIn($namauser)],
            'user_id' => ['nullable',Rule::unique('mandor')->whereNotIn('id', [$this->id])],
        ];
    }

    public function attributes()
    {
        return [
            'namamandor' => 'nama mandor',
            'statusaktif' => 'status aktif',
            'user_id' => 'user',
        ];
        
    }
}
