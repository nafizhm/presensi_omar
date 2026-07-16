@extends('adminlte::page')

@section('title', 'Data Karyawan')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Data Karyawan</h1>
            <small class="text-muted">Kelola akun dan data karyawan</small>
        </div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addEmployeeModal">
            <i class="fas fa-plus mr-1"></i> Tambah Karyawan
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="employeesTable" class="table table-bordered table-striped table-hover w-100">
                    <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Departemen</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Izin Titik Lokasi</th>
                        <th width="110">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>
                                @if ($employee->photo)
                                    <img src="{{ asset('storage/'.$employee->photo) }}" alt="{{ $employee->name }}"
                                         class="img-circle elevation-1" width="42" height="42" style="object-fit: cover">
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary img-circle"
                                          style="width:42px;height:42px">
                                        <i class="fas fa-user"></i>
                                    </span>
                                @endif
                            </td>
                            <td><strong>{{ $employee->employee_code }}</strong></td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ ucfirst($employee->gender) }}</td>
                            <td>{{ $employee->phone }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->department?->name ?? '-' }}</td>
                            <td>{{ $employee->shift?->name ?? 'Jadwal default' }}</td>
                            <td>
                                <span class="badge {{ $employee->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td><span class="badge {{ $employee->can_manage_location_points ? 'badge-info' : 'badge-light' }}">{{ $employee->can_manage_location_points ? 'Diizinkan' : 'Tidak' }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-employee"
                                        title="Edit"
                                        data-id="{{ $employee->id }}"
                                        data-code="{{ $employee->employee_code }}"
                                        data-name="{{ $employee->name }}"
                                        data-gender="{{ $employee->gender }}"
                                        data-phone="{{ $employee->phone }}"
                                        data-address="{{ $employee->address }}"
                                        data-email="{{ $employee->email }}"
                                        data-department="{{ $employee->department_id }}"
                                        data-shift="{{ $employee->shift_id }}"
                                        data-status="{{ $employee->status }}"
                                        data-location-permission="{{ $employee->can_manage_location_points ? 1 : 0 }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.karyawan.destroy', $employee) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus karyawan {{ addslashes($employee->name) }}? Data presensinya juga akan terhapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.karyawan.store') }}" method="POST" enctype="multipart/form-data" class="employee-form modal-content">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Tambah Karyawan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Kode Karyawan <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" value="{{ old('_form_mode') === 'add' ? old('employee_code') : '' }}"
                                   class="form-control text-uppercase" required maxlength="50">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama Karyawan <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('_form_mode') === 'add' ? old('name') : '' }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="">Pilih jenis kelamin</option>
                                <option value="laki-laki" @selected(old('_form_mode') === 'add' && old('gender') === 'laki-laki')>Laki-laki</option>
                                <option value="perempuan" @selected(old('_form_mode') === 'add' && old('gender') === 'perempuan')>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="phone" value="{{ old('_form_mode') === 'add' ? old('phone') : '' }}" class="form-control" required>
                        </div>
                        <div class="form-group col-12">
                            <label>Alamat <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="3" required>{{ old('_form_mode') === 'add' ? old('address') : '' }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('_form_mode') === 'add' ? old('email') : '' }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-muted">Minimal 8 karakter.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Departemen <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control" required>
                                <option value="">Pilih departemen</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('_form_mode') === 'add' && (string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Shift</label>
                            <select name="shift_id" class="form-control">
                                <option value="">Jadwal default (08:00–12:00–17:00)</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(old('_form_mode') === 'add' && (string) old('shift_id') === (string) $shift->id)>
                                        {{ $shift->name }}{{ $shift->status !== 'aktif' ? ' (Nonaktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('_form_mode') === 'add' && old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Izin Menambahkan Titik Lokasi</label>
                            <select name="can_manage_location_points" class="form-control">
                                <option value="0" @selected(old('_form_mode') === 'add' && old('can_manage_location_points', '0') === '0')>Tidak diizinkan</option>
                                <option value="1" @selected(old('_form_mode') === 'add' && old('can_manage_location_points') === '1')>Diizinkan</option>
                            </select>
                            <small class="text-muted">Jika diizinkan, menu pengaturan titik lokasi akan muncul di profil karyawan.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Upload Foto</label>
                            <input type="file" name="photo" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG, atau WebP. Maksimal 2 MB.</small>
                        </div>
                    </div>
                    @if ($errors->any() && old('_form_mode') === 'add')
                        <div class="alert alert-danger mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary save-btn">
                        <span class="spinner-border spinner-border-sm d-none mr-1" role="status"></span>
                        <span class="button-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data" class="employee-form modal-content" id="editEmployeeForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="_employee_id" id="edit-id" value="{{ old('_employee_id') }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Karyawan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Kode Karyawan <span class="text-danger">*</span></label>
                            <input id="edit-code" type="text" name="employee_code" value="{{ old('employee_code') }}" class="form-control text-uppercase" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama Karyawan <span class="text-danger">*</span></label>
                            <input id="edit-name" type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin <span class="text-danger">*</span></label>
                            <select id="edit-gender" name="gender" class="form-control" required>
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>No. Telepon <span class="text-danger">*</span></label>
                            <input id="edit-phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-12">
                            <label>Alamat <span class="text-danger">*</span></label>
                            <textarea id="edit-address" name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email <span class="text-danger">*</span></label>
                            <input id="edit-email" type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Password Baru</label>
                            <input type="password" name="password" class="form-control" minlength="8">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Departemen <span class="text-danger">*</span></label>
                            <select id="edit-department" name="department_id" class="form-control" required>
                                <option value="">Pilih departemen</option>
                                @foreach ($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Shift</label>
                            <select id="edit-shift" name="shift_id" class="form-control">
                                <option value="">Jadwal default (08:00–12:00–17:00)</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}{{ $shift->status !== 'aktif' ? ' (Nonaktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select id="edit-status" name="status" class="form-control" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Izin Menambahkan Titik Lokasi</label>
                            <select id="edit-location-permission" name="can_manage_location_points" class="form-control">
                                <option value="0">Tidak diizinkan</option><option value="1">Diizinkan</option>
                            </select>
                            <small class="text-muted">Mengatur akses menu titik lokasi pada profil karyawan.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ganti Foto</label>
                            <input type="file" name="photo" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Kosongkan untuk mempertahankan foto lama.</small>
                        </div>
                    </div>
                    @if ($errors->any() && old('_form_mode') === 'edit')
                        <div class="alert alert-danger mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning save-btn">
                        <span class="spinner-border spinner-border-sm d-none mr-1" role="status"></span>
                        <span class="button-label">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div id="successToast" class="toast bg-success text-white" role="alert" data-delay="3500"
             style="position:fixed;right:20px;bottom:20px;z-index:1060;min-width:300px">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success mr-2"></i>
                <strong class="mr-auto">Berhasil</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast"><span>&times;</span></button>
            </div>
            <div class="toast-body">{{ session('success') }}</div>
        </div>
    @endif
@stop

@section('js')
<script>
$(function () {
    $('#employeesTable').DataTable({
        responsive: true,
        autoWidth: false,
        order: [[1, 'asc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        }
    });

    const updateUrl = @js(route('admin.karyawan.update', '__ID__'));

    function setEditAction(id) {
        $('#editEmployeeForm').attr('action', updateUrl.replace('__ID__', id));
    }

    $('.edit-employee').on('click', function () {
        const button = $(this);
        $('#edit-id').val(button.data('id'));
        $('#edit-code').val(button.attr('data-code'));
        $('#edit-name').val(button.attr('data-name'));
        $('#edit-gender').val(button.attr('data-gender'));
        $('#edit-phone').val(button.attr('data-phone'));
        $('#edit-address').val(button.attr('data-address'));
        $('#edit-email').val(button.attr('data-email'));
        $('#edit-department').val(button.attr('data-department'));
        $('#edit-shift').val(button.attr('data-shift'));
        $('#edit-status').val(button.attr('data-status'));
        $('#edit-location-permission').val(button.attr('data-location-permission'));
        setEditAction(button.data('id'));
        $('#editEmployeeModal').modal('show');
    });

    $('.employee-form').on('submit', function () {
        const button = $(this).find('.save-btn');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });

    @if ($errors->any() && old('_form_mode') === 'add')
        $('#addEmployeeModal').modal('show');
    @elseif ($errors->any() && old('_form_mode') === 'edit')
        setEditAction(@js(old('_employee_id')));
        $('#edit-gender').val(@js(old('gender')));
        $('#edit-status').val(@js(old('status')));
        $('#edit-shift').val(@js(old('shift_id')));
        $('#edit-department').val(@js(old('department_id')));
        $('#edit-location-permission').val(@js(old('can_manage_location_points', '0')));
        $('#editEmployeeModal').modal('show');
    @endif

    $('#successToast').toast('show');
});
</script>
@stop
