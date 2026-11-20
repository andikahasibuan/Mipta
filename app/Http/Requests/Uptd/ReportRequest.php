<?php

namespace App\Http\Requests\Uptd;

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\UptdContributor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');

        if ($report instanceof FieldDistributionReport) {
            $contributor = $this->attributes->get('uptd_contributor');

            abort_unless(
                $contributor instanceof UptdContributor
                && Gate::allows('update', [$report, $contributor]),
                404,
            );
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'condition' => ['required', Rule::enum(FieldCondition::class)],
            'description' => ['required', 'string', 'max:120', 'regex:/\p{L}/u'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.regex' => 'Keterangan harus berisi teks, tidak boleh hanya angka atau simbol.',
        ];
    }
}
