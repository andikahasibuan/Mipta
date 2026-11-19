<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('report exports', [
    'admin penyaluran excel' => ['admin.distribution.export.excel', Role::Admin],
    'admin penyaluran pdf' => ['admin.distribution.export.pdf', Role::Admin],
    'admin pengawasan excel' => ['admin.supervision.export.excel', Role::Admin],
    'admin pengawasan pdf' => ['admin.supervision.export.pdf', Role::Admin],
    'kepala dinas penyaluran excel' => ['kepala-dinas.distribution.export.excel', Role::KepalaDinas],
    'kepala dinas penyaluran pdf' => ['kepala-dinas.distribution.export.pdf', Role::KepalaDinas],
    'kepala dinas pengawasan excel' => ['kepala-dinas.supervision.export.excel', Role::KepalaDinas],
    'kepala dinas pengawasan pdf' => ['kepala-dinas.supervision.export.pdf', Role::KepalaDinas],
]);

it('mengunduh laporan sebagai berkas', function (string $routeName, Role $role) {
    Distribution::factory()->create();
    FieldDistributionReport::factory()->create();

    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get(route($routeName))
        ->assertOk()
        ->assertDownload();
})->with('report exports');
