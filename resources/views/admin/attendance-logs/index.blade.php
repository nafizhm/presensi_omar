@extends('adminlte::page')

@section('title', 'Log Presensi')

@section('content_header')
    <div>
        <h1 class="m-0">Log Presensi</h1>
        <small class="text-muted">Data presensi karyawan berdasarkan tanggal.</small>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.attendance.logs.index') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="tanggal" class="mr-2">Tanggal</label>
                    <input id="tanggal" type="date" name="tanggal" value="{{ $selectedDate }}"
                           class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i> Tampilkan
                </button>
                <span class="text-muted ml-3">Zona waktu {{ $timezoneLabel }}</span>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                <tr>
                    <th style="width:70px">No</th>
                    <th>Nama</th>
                    <th style="width:140px">Jam Masuk</th>
                    <th style="width:140px">Jam Pulang</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($attendanceLogs as $attendance)
                    @php
                        $status = match ($attendance->status) {
                            'tepat_waktu' => ['label' => 'Tepat waktu', 'class' => 'badge-success'],
                            'telat' => ['label' => 'Telat', 'class' => 'badge-warning'],
                            'izin' => ['label' => 'Izin', 'class' => 'badge-info'],
                            default => ['label' => 'Absen', 'class' => 'badge-secondary'],
                        };
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->user?->name ?? 'Karyawan dihapus' }}</td>
                        <td>{{ $attendance->jam_masuk ?? '-' }}</td>
                        <td>{{ $attendance->jam_pulang ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            @if ($attendance->shift_name)
                                <small class="d-block font-weight-bold mt-1">Shift: {{ $attendance->shift_name }}</small>
                            @endif
                            @if ($attendance->keterangan)
                                <small class="d-block mt-1">{{ $attendance->keterangan }}</small>
                            @endif
                            @if ($attendance->keterangan_lokasi)
                                <small class="d-block text-muted mt-1">{{ $attendance->keterangan_lokasi }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Tidak ada data presensi pada tanggal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
