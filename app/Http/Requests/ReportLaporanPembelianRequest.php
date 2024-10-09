<?php

namespace App\Http\Requests;

use App\Rules\ExistSupplierId;
use Illuminate\Foundation\Http\FormRequest;

class ReportLaporanPembelianRequest extends FormRequest
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
        $supplierdari_id = $this->supplierdari_id;
        $rulessupplierdari = [];
        if ($supplierdari_id != null) {
            $rulessupplierdari = [
                'supplierdari_id' => ['required', 'numeric', 'min:1', new ExistSupplierId()],
            ];
        } else if ($supplierdari_id == null && $this->supplierdari != '') {
            $rulessupplierdari = [
                'supplierdari_id' => ['required', 'numeric', 'min:1', new ExistSupplierId()],
            ];
        }

        $suppliersampai_id = $this->suppliersampai_id;
        $rulessuppliersampai = [];
        if ($suppliersampai_id != null) {
            $rulessuppliersampai = [
                'suppliersampai_id' => ['required', 'numeric', 'min:1', new ExistSupplierId()],
            ];
        } else if ($suppliersampai_id == null && $this->suppliersampai != '') {
            $rulessuppliersampai = [
                'suppliersampai_id' => ['required', 'numeric', 'min:1', new ExistSupplierId()],
            ];
        }

        $rule = [
            'dari' => [
                'required', 'date_format:d-m-Y',
            ],
            'sampai' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . request()->dari
            ],
            // 'supplierdari' => ['required'],
            // 'suppliersampai' => ['required'],
            'status' => ['required']
        ];

        $rule = array_merge(
            $rule,
            // $rulessupplierdari,
            // $rulessuppliersampai
        );

        return $rule;
    }
}
