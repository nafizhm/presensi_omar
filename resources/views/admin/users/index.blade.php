@extends('adminlte::page')

@section('title', 'Pengguna Admin')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Pengguna Admin</h1>
            <small class="text-muted">Kelola akun yang dapat mengakses panel admin.</small>
        </div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addAdminModal">
            <i class="fas fa-plus mr-1"></i> Tambah Pengguna
        </button>
    </div>
@stop

@section('content')
    @if ($errors->has('delete'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $errors->first('delete') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="adminUsersTable" class="table table-bordered table-striped table-hover w-100">
                    <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th width="110">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $adminUser)
                        <tr>
                            <td>
                                @if ($adminUser->photo)
                                    <img src="{{ asset('storage/'.$adminUser->photo) }}" alt="{{ $adminUser->name }}"
                                         class="img-circle elevation-1" width="42" height="42" style="object-fit:cover">
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center bg-secondary img-circle"
                                          style="width:42px;height:42px"><i class="fas fa-user-shield"></i></span>
                                @endif
                            </td>
                            <td><strong>{{ $adminUser->username }}</strong></td>
                            <td>
                                {{ $adminUser->name }}
                                @if ($adminUser->is(auth()->user()))
                                    <span class="badge badge-info ml-1">Akun Anda</span>
                                @endif
                            </td>
                            <td>{{ $adminUser->email }}</td>
                            <td>
                                <span class="badge {{ $adminUser->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($adminUser->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-admin"
                                        data-id="{{ $adminUser->id }}"
                                        data-username="{{ $adminUser->username }}"
                                        data-name="{{ $adminUser->name }}"
                                        data-email="{{ $adminUser->email }}"
                                        data-status="{{ $adminUser->status }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.settings.users.destroy', $adminUser) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus pengguna admin {{ addslashes($adminUser->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                            @disabled($adminUser->is(auth()->user()))>
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

    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.settings.users.store') }}" method="POST" enctype="multipart/form-data" class="admin-form modal-content">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Tambah Pengguna Admin</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" value="{{ old('_form_mode') === 'add' ? old('username') : '' }}"
                                   class="form-control" required minlength="3" maxlength="50">
                            <small class="text-muted">Huruf kecil, angka, titik, garis bawah, atau tanda minus.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('_form_mode') === 'add' ? old('name') : '' }}" class="form-control" required>
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
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('_form_mode') === 'add' && old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
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
                        <span class="spinner-border spinner-border-sm d-none mr-1"></span>
                        <span class="button-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data" class="admin-form modal-content" id="editAdminForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="_admin_id" id="edit-id" value="{{ old('_admin_id') }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Pengguna Admin</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Username <span class="text-danger">*</span></label>
                            <input id="edit-username" type="text" name="username" value="{{ old('username') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input id="edit-name" type="text" name="name" value="{{ old('name') }}" class="form-control" required>
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
                            <label>Status <span class="text-danger">*</span></label>
                            <select id="edit-status" name="status" class="form-control" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
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
                        <span class="spinner-border spinner-border-sm d-none mr-1"></span>
                        <span class="button-label">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div id="successToast" class="toast bg-success text-white" data-delay="3500"
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
    $('#adminUsersTable').DataTable({
        responsive: true, autoWidth: false, order: [[1, 'asc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        }
    });

    const updateUrl = @js(route('admin.settings.users.update', '__ID__'));
    const setEditAction = id => $('#editAdminForm').attr('action', updateUrl.replace('__ID__', id));

    $('.edit-admin').on('click', function () {
        const button = $(this);
        $('#edit-id').val(button.data('id'));
        $('#edit-username').val(button.attr('data-username'));
        $('#edit-name').val(button.attr('data-name'));
        $('#edit-email').val(button.attr('data-email'));
        $('#edit-status').val(button.attr('data-status'));
        setEditAction(button.data('id'));
        $('#editAdminModal').modal('show');
    });

    $('.admin-form').on('submit', function () {
        const button = $(this).find('.save-btn');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });

    @if ($errors->any() && old('_form_mode') === 'add')
        $('#addAdminModal').modal('show');
    @elseif ($errors->any() && old('_form_mode') === 'edit')
        setEditAction(@js(old('_admin_id')));
        $('#edit-status').val(@js(old('status')));
        $('#editAdminModal').modal('show');
    @endif

    $('#successToast').toast('show');
});
</script>
@stop
