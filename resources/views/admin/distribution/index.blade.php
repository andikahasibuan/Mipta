@extends('layouts.admin') {{-- Sesuaikan dengan layout utama aplikasi Anda --}}

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Data Penyaluran</h3>

                    <!-- Tombol Export Excel & PDF -->
                    <div class="btn-group">
                        <a href="{{ route('admin.distribution.export.excel', request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.distribution.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form Filter (Bulan / Pencarian) -->
                    <form method="GET" action="{{ route('admin.distribution.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="month" class="form-label">Pilih Bulan</label>
                                <input type="month" name="month" id="month" class="form-control" value="{{ $filters['month'] ?? request('month') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="q" class="form-label">Pencarian</label>
                                <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? request('q') }}" placeholder="Cari pasar atau kontributor...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Data Penyaluran -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pasar</th>
                                    <th>Kontributor</th>
                                    <th>Kondisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributions as $index => $item)
                                    <tr>
                                        <td>{{ $distributions->firstItem() + $index }}</td>
                                        <td>{{ $item->report_date }}</td>
                                        <td>{{ $item->market->name ?? '-' }}</td>
                                        <td>{{ $item->contributor->name ?? '-' }}</td>
                                        <td>{{ $item->condition }}</td>
                                        <td>
                                            <!-- Tombol aksi lain jika ada -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data penyaluran untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $distributions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection