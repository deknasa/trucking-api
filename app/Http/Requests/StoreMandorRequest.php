<?php

namespace App\Http\Requests;

use App\Models\Mandor;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;


class StoreMandorRequest extends FormRequest
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
        $mandor = (new Mandor())->select('user.name')->leftJoin(DB::raw("[user] with (readuncommitted)"), 'mandor.user_id', '=', 'user.id')->get();
        $namauser = [];
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
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        }

        return [
            'namamandor' => 'required|unique:mandor',
            'statusaktifnama' => ['required'],
            'user' => ['nullable', Rule::notIn($namauser)],
            'user_id' => ['nullable', 'unique:mandor'],
        ];
    }

    public function attributes()
    {
        return [
            'namamandor' => 'nama mandor',
            'statusaktifnama' => ['required'],
            'user_id' => 'user',
        ];
    }
}
