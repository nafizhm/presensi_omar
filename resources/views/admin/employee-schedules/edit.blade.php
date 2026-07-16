@extends('adminlte::page')

@section('title', 'Update Jadwal Karyawan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Update Jadwal</h1>
            <small class="text-muted">{{ $employee->employee_code }} · {{ $employee->name }}</small>
        </div>
        <a href="{{ route('admin.attendance.employee-schedules.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.attendance.employee-schedules.edit', $employee) }}" method="GET" class="form-inline">
                <label for="month" class="mr-2">Bulan</label>
                <select id="month" name="month" class="form-control mr-3">
                    @foreach (range(1, 12) as $monthNumber)
                        <option value="{{ $monthNumber }}" @selected($month === $monthNumber)>
                            {{ \Carbon\Carbon::create(2000, $monthNumber)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <label for="year" class="mr-2">Tahun</label>
                <input id="year" type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="form-control mr-3" required>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Tampilkan</button>
            </form>
        </div>
    </div>

    <form action="{{ route('admin.attendance.employee-schedules.update', $employee) }}" method="POST" id="monthlyScheduleForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <div class="card">
            <div class="card-header d-flex align-items-center">
                <strong>{{ \Carbon\Carbon::create($year, $month)->locale('id')->translatedFormat('F Y') }}</strong>
                <span class="text-muted ml-3">Shift utama: {{ $employee->shift?->name ?? 'Jadwal Default' }}</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th style="width:70px">No</th><th>Tanggal</th><th>Shift</th></tr></thead>
                    <tbody>
                    @foreach ($dates as $date)
                        @php
                            $dateString = $date->toDateString();
                            $selectedShift = old('assignments.'.$dateString, $assignments->get($dateString));
                        @endphp
                        <tr class="{{ $date->isWeekend() ? 'table-light' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $date->copy()->locale('id')->translatedFormat('l, d F Y') }}</strong>
                            </td>
                            <td>
                                <select name="assignments[{{ $dateString }}]" class="form-control">
                                    <option value="">Ikuti Shift Utama ({{ $employee->shift?->name ?? 'Jadwal Default' }})</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}" @selected((string) $selectedShift === (string) $shift->id)>{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-success" id="saveScheduleButton">
                    <span class="spinner-border spinner-border-sm d-none mr-1"></span>
                    <span class="button-label">Simpan Jadwal Bulanan</span>
                </button>
            </div>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if (session('success'))
        <div id="successToast" class="toast bg-success text-white" data-delay="3500" style="position:fixed;right:20px;bottom:20px;z-index:1060;min-width:330px">
            <div class="toast-header"><i class="fas fa-check-circle text-success mr-2"></i><strong class="mr-auto">Berhasil</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast"><span>&times;</span></button></div>
            <div class="toast-body">{{ session('success') }}</div>
        </div>
    @endif
@stop

@section('js')
<script>
$(function () {
    $('#monthlyScheduleForm').on('submit', function () {
        const button = $('#saveScheduleButton');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });
    $('#successToast').toast('show');
});
</script>
@stop
