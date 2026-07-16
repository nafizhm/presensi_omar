@extends('adminlte::page')

@section('title', 'Set Jadwal Karyawan')

@section('content_header')
    <div>
        <h1 class="m-0">Set Jadwal Karyawan</h1>
        <small class="text-muted">Atur shift berbeda untuk setiap tanggal dalam satu bulan.</small>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                <tr>
                    <th style="width:70px">No</th>
                    <th>Kode Karyawan</th>
                    <th>Nama Karyawan</th>
                    <th>Shift Utama</th>
                    <th style="width:170px">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $employee->employee_code }}</strong></td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->shift?->name ?? 'Jadwal Default' }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.employee-schedules.edit', $employee) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-calendar-alt mr-1"></i> Update Jadwal
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
