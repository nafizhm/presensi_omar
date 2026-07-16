@extends('adminlte::page')

@section('title', 'Setting Presensi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Setting Presensi</h1>
            <small class="text-muted">Kelola shift dan jadwal presensi Senin sampai Minggu.</small>
        </div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addShiftModal">
            <i class="fas fa-plus mr-1"></i> Tambah Shift
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                <tr>
                    <th>Nama Shift</th>
                    <th>Jadwal</th>
                    <th>Karyawan</th>
                    <th>Status</th>
                    <th style="width:110px">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($shifts as $shift)
                    <tr>
                        <td><strong>{{ $shift->name }}</strong></td>
                        <td>
                            @foreach ($days as $dayNumber => $dayName)
                                @php
                                    $schedule = $shift->schedules->firstWhere('day_of_week', $dayNumber);
                                @endphp
                                <div class="small mb-1">
                                    <span class="d-inline-block font-weight-bold" style="width:65px">{{ $dayName }}</span>
                                    @if ($schedule?->is_workday)
                                        {{ substr($schedule->check_in_time, 0, 5) }} –
                                        {{ substr($schedule->middle_time, 0, 5) }} –
                                        {{ substr($schedule->check_out_time, 0, 5) }}
                                    @else
                                        <span class="text-muted">Libur</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $shift->employees_count }} orang</td>
                        <td>
                            <span class="badge {{ $shift->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($shift->status) }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning edit-shift" data-id="{{ $shift->id }}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.attendance.settings.destroy', $shift) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus shift {{ addslashes($shift->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada shift. Tambahkan shift pertama.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $renderScheduleRows = function (string $prefix, array $values = []) use ($days) {
            return view('admin.shifts.partials.schedule-rows', compact('prefix', 'values', 'days'))->render();
        };
    @endphp

    <div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="{{ route('admin.attendance.settings.store') }}" method="POST" class="shift-form modal-content">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Tambah Shift</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-8">
                            <label>Nama Shift <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('_form_mode') === 'add' ? old('name') : '' }}" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('_form_mode') === 'add' && old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">Batas tengah menentukan jenis absen: sebelum batas tengah = masuk, setelah batas tengah = pulang.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered schedule-table mb-0">
                            <thead><tr><th>Hari</th><th>Hari Kerja</th><th>Jam Masuk</th><th>Batas Tengah</th><th>Jam Pulang</th></tr></thead>
                            <tbody>{!! $renderScheduleRows('add', old('_form_mode') === 'add' ? old('schedules', []) : []) !!}</tbody>
                        </table>
                    </div>
                    @if ($errors->any() && old('_form_mode') === 'add')
                        <div class="alert alert-danger mt-3 mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary save-btn"><span class="spinner-border spinner-border-sm d-none mr-1"></span><span class="button-label">Simpan</span></button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" id="editShiftForm" class="shift-form modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="_shift_id" id="edit-shift-id" value="{{ old('_shift_id') }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Shift</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-8">
                            <label>Nama Shift <span class="text-danger">*</span></label>
                            <input id="edit-shift-name" type="text" name="name" value="{{ old('name') }}" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Status <span class="text-danger">*</span></label>
                            <select id="edit-shift-status" name="status" class="form-control" required>
                                <option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">Jadwal melewati tengah malam didukung, misalnya 22:00 – 01:00 – 06:00.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered schedule-table mb-0">
                            <thead><tr><th>Hari</th><th>Hari Kerja</th><th>Jam Masuk</th><th>Batas Tengah</th><th>Jam Pulang</th></tr></thead>
                            <tbody>{!! $renderScheduleRows('edit', old('_form_mode') === 'edit' ? old('schedules', []) : []) !!}</tbody>
                        </table>
                    </div>
                    @if ($errors->any() && old('_form_mode') === 'edit')
                        <div class="alert alert-danger mt-3 mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning save-btn"><span class="spinner-border spinner-border-sm d-none mr-1"></span><span class="button-label">Simpan Perubahan</span></button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div id="successToast" class="toast bg-success text-white" data-delay="3500" style="position:fixed;right:20px;bottom:20px;z-index:1060;min-width:320px">
            <div class="toast-header"><i class="fas fa-check-circle text-success mr-2"></i><strong class="mr-auto">Berhasil</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast"><span>&times;</span></button></div>
            <div class="toast-body">{{ session('success') }}</div>
        </div>
    @endif
@stop

@section('js')
<script>
$(function () {
    const updateUrl = @js(route('admin.attendance.settings.update', '__ID__'));
    const shiftData = @json($shiftData);

    function toggleScheduleRow(checkbox) {
        $(checkbox).closest('tr').find('input[type="time"]').prop('disabled', !checkbox.checked).prop('required', checkbox.checked);
    }
    $('.workday-toggle').each(function () { toggleScheduleRow(this); }).on('change', function () { toggleScheduleRow(this); });

    function fillEditForm(id, data) {
        $('#edit-shift-id').val(id);
        $('#edit-shift-name').val(data.name);
        $('#edit-shift-status').val(data.status);
        $('#editShiftForm').attr('action', updateUrl.replace('__ID__', id));
        for (let day = 1; day <= 7; day++) {
            const schedule = data.schedules[day] || { is_workday: false };
            const row = $('#edit-schedule-' + day);
            const checkbox = row.find('.workday-toggle').prop('checked', Boolean(schedule.is_workday))[0];
            row.find('[data-field="check_in_time"]').val(schedule.check_in_time || '08:00');
            row.find('[data-field="middle_time"]').val(schedule.middle_time || '12:00');
            row.find('[data-field="check_out_time"]').val(schedule.check_out_time || '17:00');
            toggleScheduleRow(checkbox);
        }
    }

    $('.edit-shift').on('click', function () {
        const id = $(this).data('id');
        fillEditForm(id, shiftData[id]);
        $('#editShiftModal').modal('show');
    });

    $('.shift-form').on('submit', function () {
        const button = $(this).find('.save-btn');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });

    @if ($errors->any() && old('_form_mode') === 'add')
        $('#addShiftModal').modal('show');
    @elseif ($errors->any() && old('_form_mode') === 'edit')
        $('#editShiftForm').attr('action', updateUrl.replace('__ID__', @js(old('_shift_id'))));
        $('#edit-shift-status').val(@js(old('status')));
        $('#editShiftModal').modal('show');
    @endif
    $('#successToast').toast('show');
});
</script>
@stop
