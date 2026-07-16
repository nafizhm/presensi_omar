@extends('adminlte::page')

@section('title', 'Profil Perusahaan')

@section('content_header')
    <div>
        <h1 class="m-0">Profil Perusahaan</h1>
        <small class="text-muted">Informasi ini digunakan pada aplikasi presensi karyawan.</small>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <form action="{{ route('admin.settings.profile.update') }}" method="POST" enctype="multipart/form-data" id="companyProfileForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="company_name">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="company_name" name="company_name"
                                   value="{{ old('company_name', $profile->company_name) }}"
                                   class="form-control @error('company_name') is-invalid @enderror" required>
                            @error('company_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat <span class="text-danger">*</span></label>
                            <textarea id="address" name="address" rows="4"
                                      class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $profile->address) }}</textarea>
                            @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}"
                                   class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="logo">Upload Logo</label>
                            <div class="custom-file">
                                <input type="file" id="logo" name="logo"
                                       class="custom-file-input @error('logo') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp">
                                <label class="custom-file-label" for="logo">Pilih file logo</label>
                            </div>
                            @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">JPG, PNG, atau WebP. Maksimal 2 MB.</small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary" id="saveProfileButton">
                            <span class="spinner-border spinner-border-sm d-none mr-1" role="status"></span>
                            <span class="button-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Logo Saat Ini</h3></div>
                <div class="card-body text-center">
                    @if ($profile->logo)
                        <img src="{{ asset('storage/'.$profile->logo) }}" alt="Logo {{ $profile->company_name }}"
                             class="img-fluid" style="max-height:180px">
                    @else
                        <div class="text-muted py-5">
                            <i class="far fa-image fa-4x mb-3"></i>
                            <p class="mb-0">Belum ada logo yang diunggah.</p>
                        </div>
                    @endif
                </div>
            </div>
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
    $('#logo').on('change', function () {
        const fileName = this.files.length ? this.files[0].name : 'Pilih file logo';
        $(this).next('.custom-file-label').text(fileName);
    });

    $('#companyProfileForm').on('submit', function () {
        const button = $('#saveProfileButton');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });

    $('#successToast').toast('show');
});
</script>
@stop
