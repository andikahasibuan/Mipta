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
            'distribution_date' => ['required', 'date', 'before_or_equal:today'],
            'recipient_store_id' => ['required', 'integer', 'exists:recipient_stores,id'],
            'cartons' => ['required', 'integer', 'min:1'],
        ];
    }
}
