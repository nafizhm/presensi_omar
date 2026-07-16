@extends('adminlte::page')

@section('title', 'Data Departemen')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="m-0">Data Departemen</h1><small class="text-muted">Kelola master departemen karyawan</small></div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addDepartmentModal"><i class="fas fa-plus mr-1"></i> Tambah Departemen</button>
    </div>
@stop

@section('content')
    <div class="card"><div class="card-body"><div class="table-responsive">
        <table id="departmentsTable" class="table table-bordered table-striped table-hover w-100">
            <thead><tr><th>Nama Departemen</th><th>Keterangan</th><th>Jumlah Karyawan</th><th width="110">Aksi</th></tr></thead>
            <tbody>
            @foreach ($departments as $department)
                <tr>
                    <td><strong>{{ $department->name }}</strong></td>
                    <td>{{ $department->description ?: '-' }}</td>
                    <td>{{ $department->employees_count }} orang</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning edit-department" title="Edit"
                                data-id="{{ $department->id }}" data-name="{{ $department->name }}"
                                data-description="{{ $department->description }}"><i class="fas fa-edit"></i></button>
                        <form action="{{ route('admin.master-data.departments.destroy', $department) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus departemen {{ addslashes($department->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div></div>

    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog">
        <form action="{{ route('admin.master-data.departments.store') }}" method="POST" class="department-form modal-content">
            @csrf <input type="hidden" name="_form_mode" value="add">
            <div class="modal-header bg-primary"><h5 class="modal-title">Tambah Departemen</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Nama Departemen <span class="text-danger">*</span></label><input type="text" name="name" value="{{ old('_form_mode') === 'add' ? old('name') : '' }}" class="form-control" required maxlength="255"></div>
                <div class="form-group mb-0"><label>Keterangan</label><textarea name="description" class="form-control" rows="4" maxlength="2000">{{ old('_form_mode') === 'add' ? old('description') : '' }}</textarea></div>
                @if ($errors->any() && old('_form_mode') === 'add')<div class="alert alert-danger mt-3 mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary save-btn">Simpan</button></div>
        </form>
    </div></div>

    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog">
        <form method="POST" id="editDepartmentForm" class="department-form modal-content">
            @csrf @method('PUT') <input type="hidden" name="_form_mode" value="edit"><input type="hidden" name="_department_id" id="edit-department-id" value="{{ old('_department_id') }}">
            <div class="modal-header bg-warning"><h5 class="modal-title">Edit Departemen</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Nama Departemen <span class="text-danger">*</span></label><input id="edit-department-name" type="text" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255"></div>
                <div class="form-group mb-0"><label>Keterangan</label><textarea id="edit-department-description" name="description" class="form-control" rows="4" maxlength="2000">{{ old('description') }}</textarea></div>
                @if ($errors->any() && old('_form_mode') === 'edit')<div class="alert alert-danger mt-3 mb-0"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning save-btn">Simpan Perubahan</button></div>
        </form>
    </div></div>

    @if (session('success'))<div id="successToast" class="toast bg-success text-white" data-delay="3500" style="position:fixed;right:20px;bottom:20px;z-index:1060;min-width:320px"><div class="toast-body">{{ session('success') }}</div></div>@endif
@stop

@section('js')
<script>
$(function () {
    $('#departmentsTable').DataTable({ responsive: true, autoWidth: false, order: [[0, 'asc']], language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_–_END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } } });
    const updateUrl = @js(route('admin.master-data.departments.update', '__ID__'));
    function setEditAction(id) { $('#editDepartmentForm').attr('action', updateUrl.replace('__ID__', id)); }
    $('.edit-department').on('click', function () { const button = $(this); $('#edit-department-id').val(button.data('id')); $('#edit-department-name').val(button.attr('data-name')); $('#edit-department-description').val(button.attr('data-description')); setEditAction(button.data('id')); $('#editDepartmentModal').modal('show'); });
    $('.department-form').on('submit', function () { $(this).find('.save-btn').prop('disabled', true).text('Menyimpan...'); });
    @if ($errors->any() && old('_form_mode') === 'add') $('#addDepartmentModal').modal('show');
    @elseif ($errors->any() && old('_form_mode') === 'edit') setEditAction(@js(old('_department_id'))); $('#editDepartmentModal').modal('show'); @endif
    $('#successToast').toast('show');
});
</script>
@stop
