@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Dashboard Presensi</h1>
            <small class="text-muted">Ringkasan {{ now()->translatedFormat('l, d F Y') }}</small>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button class="btn btn-outline-danger btn-sm" type="submit">
                <i class="fas fa-sign-out-alt mr-1"></i> Keluar
            </button>
        </form>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $totalKaryawan }}</h3><p>Total Karyawan</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $hadirHariIni }}</h3><p>Hadir Hari Ini</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $terlambatHariIni }}</h3><p>Terlambat Hari Ini</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Presensi Terbaru Hari Ini</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead><tr><th>Karyawan</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th><th>Lokasi</th></tr></thead>
                <tbody>
                @forelse ($presensiTerbaru as $presensi)
                    <tr>
                        <td>{{ $presensi->user?->name ?? '-' }}</td>
                        <td>{{ $presensi->jam_masuk ?? '-' }}</td>
                        <td>{{ $presensi->jam_pulang ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $presensi->status === 'telat' ? 'badge-warning' : 'badge-success' }}">
                                {{ str_replace('_', ' ', ucfirst($presensi->status)) }}
                            </span>
                        </td>
                        <td>{{ $presensi->keterangan_lokasi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada presensi hari ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
