<?php

namespace App\Http\Requests\Distributor;

use App\Models\Distribution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DistributionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $distribution = $this->route('distribution');

        if ($distribution instanceof Distribution) {
            abort_unless(Gate::allows('update', $distribution), 404);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_store_id' => ['required', 'integer', 'exists:recipient_stores,id'],
            'cartons' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cartons.max' => 'Jumlah penyaluran maksimal 100 karton.',
            'cartons.min' => 'Jumlah penyaluran minimal 1 karton.',
            'cartons.integer' => 'Jumlah penyaluran harus berupa angka bulat.',
        ];
    }
}
