@extends('layouts.kepala-dinas') {{-- Sesuaikan dengan layout utama untuk Kepala Dinas di proyek Anda --}}

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Laporan Pengawasan Lapangan (Kepala Dinas)</h3>

                    <!-- Tombol Export Excel & PDF untuk Kepala Dinas (Pengawasan) -->
                    <div class="btn-group">
                        <a href="{{ route('kepala-dinas.supervision.export.excel', request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('kepala-dinas.supervision.export.pdf', request()->query()) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form Filter (Bulan / Kondisi / Pencarian) -->
                    <form method="GET" action="{{ route('kepala-dinas.supervision.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="month" class="form-label">Pilih Bulan</label>
                                <input type="month" name="month" id="month" class="form-control" value="{{ $filters['month'] ?? request('month') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="condition" class="form-label">Kondisi</label>
                                <select name="condition" id="condition" class="form-control">
                                    <option value="">Semua Kondisi</option>
                                    @foreach($conditions as $cond)
                                        <option value="{{ $cond->value }}" {{ (isset($filters['condition']) && $filters['condition'] === $cond->value) ? 'selected' : '' }}>
                                            {{ ucfirst($cond->name) }}
                                        </option>
                                    @endforeach
                                </select>
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

                    <!-- Ringkasan / Summary Kartu Informasi -->
                    @if(isset($summary))
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-box bg-light p-3 border rounded">
                                    <span class="info-box-text text-muted">Total Laporan</span>
                                    <h4 class="info-box-number mb-0">{{ $summary['reports'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-light p-3 border rounded">
                                    <span class="info-box-text text-muted">Pasar Dilaporkan</span>
                                    <h4 class="info-box-number mb-0">{{ $summary['reported_markets'] ?? 0 }} / {{ $summary['total_markets'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-light p-3 border rounded">
                                    <span class="info-box-text text-muted">Perlu Perhatian</span>
                                    <h4 class="info-box-number mb-0 text-danger">{{ $summary['needs_attention_markets'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tabel Data Pengawasan -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pasar</th>
                                    <th>Kontributor</th>
                                    <th>Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $index => $item)
                                    <tr>
                                        <td>{{ $reports->firstItem() + $index }}</td>
                                        <td>{{ $item->report_date }}</td>
                                        <td>{{ $item->market->name ?? '-' }}</td>
                                        <td>{{ $item->contributor->name ?? '-' }}</td>
                                        <td>{{ $item->condition }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data pengawasan untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection