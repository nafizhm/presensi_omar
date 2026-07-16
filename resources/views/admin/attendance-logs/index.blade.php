@extends('adminlte::page')

@section('title', 'Log Presensi')

@section('css')
<style>
    .attendance-time { font-size: 1rem; font-weight: 600; color: #343a40; }
    .attendance-detail-btn { border-radius: 20px; padding: 2px 11px; font-size: .75rem; }
    .attendance-photo-wrap { min-height: 350px; background: #f4f6f9; border-radius: .5rem; overflow: hidden; }
    .attendance-photo { width: 100%; height: 350px; object-fit: contain; }
    .attendance-map { width: 100%; height: 350px; border: 0; border-radius: .5rem; }
    .detail-placeholder { min-height: 350px; color: #6c757d; }
    .coordinate-badge { font-family: monospace; font-size: .82rem; }
    @media (max-width: 767.98px) { .attendance-photo, .attendance-map, .attendance-photo-wrap, .detail-placeholder { height: 260px; min-height: 260px; } }
</style>
@stop

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
                        <td>
                            <div class="attendance-time">{{ $attendance->jam_masuk ? substr($attendance->jam_masuk, 0, 5) : '-' }}</div>
                            @if ($attendance->jam_masuk)
                                <button type="button" class="btn btn-outline-primary attendance-detail-btn mt-1 detail-attendance"
                                        data-toggle="modal" data-target="#attendanceDetailModal"
                                        data-employee="{{ $attendance->user?->name ?? 'Karyawan dihapus' }}"
                                        data-type="Jam Masuk" data-time="{{ substr($attendance->jam_masuk, 0, 5) }}"
                                        data-date="{{ $attendance->tanggal->translatedFormat('d F Y') }}"
                                        data-lat="{{ $attendance->lokasi_masuk_lat }}" data-lng="{{ $attendance->lokasi_masuk_lng }}"
                                        data-photo="{{ $attendance->foto_masuk ? asset('storage/'.$attendance->foto_masuk) : '' }}">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                            @endif
                        </td>
                        <td>
                            <div class="attendance-time">{{ $attendance->jam_pulang ? substr($attendance->jam_pulang, 0, 5) : '-' }}</div>
                            @if ($attendance->jam_pulang)
                                <button type="button" class="btn btn-outline-primary attendance-detail-btn mt-1 detail-attendance"
                                        data-toggle="modal" data-target="#attendanceDetailModal"
                                        data-employee="{{ $attendance->user?->name ?? 'Karyawan dihapus' }}"
                                        data-type="Jam Pulang" data-time="{{ substr($attendance->jam_pulang, 0, 5) }}"
                                        data-date="{{ $attendance->tanggal->translatedFormat('d F Y') }}"
                                        data-lat="{{ $attendance->lokasi_pulang_lat }}" data-lng="{{ $attendance->lokasi_pulang_lng }}"
                                        data-photo="{{ $attendance->foto_pulang ? asset('storage/'.$attendance->foto_pulang) : '' }}">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                            @endif
                        </td>
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

    <div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <div>
                        <h5 class="modal-title" id="attendanceDetailTitle"><i class="fas fa-map-marked-alt mr-2"></i>Detail Presensi</h5>
                        <small id="detailSubtitle" class="d-block mt-1"></small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-5 mb-4 mb-md-0">
                            <h6 class="font-weight-bold"><i class="fas fa-camera text-primary mr-2"></i>Foto Absen</h6>
                            <div class="attendance-photo-wrap border d-flex align-items-center justify-content-center">
                                <img id="detailPhoto" class="attendance-photo d-none" src="" alt="Foto presensi">
                                <div id="photoPlaceholder" class="detail-placeholder d-flex flex-column align-items-center justify-content-center text-center p-4">
                                    <i class="fas fa-image fa-3x mb-3 text-muted"></i><span>Foto absen tidak tersedia.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-map-marker-alt text-danger mr-2"></i>Lokasi Absen</h6>
                                <span id="detailCoordinates" class="badge badge-light border coordinate-badge mb-2"></span>
                            </div>
                            <iframe id="detailMap" class="attendance-map d-none" title="Peta lokasi presensi" loading="lazy" allowfullscreen></iframe>
                            <div id="mapPlaceholder" class="detail-placeholder border rounded d-flex flex-column align-items-center justify-content-center text-center p-4">
                                <i class="fas fa-map-marker-alt fa-3x mb-3 text-muted"></i><span>Koordinat lokasi tidak tersedia.</span>
                            </div>
                            <a id="openMapLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm mt-3 d-none">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    $('.detail-attendance').on('click', function () {
        const button = $(this);
        const employee = button.attr('data-employee');
        const type = button.attr('data-type');
        const time = button.attr('data-time');
        const date = button.attr('data-date');
        const photo = button.attr('data-photo');
        const lat = button.attr('data-lat');
        const lng = button.attr('data-lng');
        const hasCoordinates = lat !== '' && lng !== '' && Number.isFinite(Number(lat)) && Number.isFinite(Number(lng));

        $('#attendanceDetailTitle').html('<i class="fas fa-map-marked-alt mr-2"></i>Detail ' + type);
        $('#detailSubtitle').text(employee + ' • ' + date + ' pukul ' + time + ' ' + @js($timezoneLabel));

        $('#detailPhoto').toggleClass('d-none', !photo).attr('src', photo || '');
        $('#photoPlaceholder').toggleClass('d-none', Boolean(photo));

        if (hasCoordinates) {
            const coordinates = lat + ',' + lng;
            const mapUrl = 'https://maps.google.com/maps?q=' + encodeURIComponent(coordinates) + '&z=17&output=embed';
            $('#detailCoordinates').text(lat + ', ' + lng).removeClass('d-none');
            $('#detailMap').attr('src', mapUrl).removeClass('d-none');
            $('#mapPlaceholder').addClass('d-none');
            $('#openMapLink').attr('href', 'https://www.google.com/maps?q=' + encodeURIComponent(coordinates)).removeClass('d-none');
        } else {
            $('#detailCoordinates, #detailMap, #openMapLink').addClass('d-none');
            $('#detailMap').attr('src', '');
            $('#mapPlaceholder').removeClass('d-none');
        }
    });

    $('#attendanceDetailModal').on('hidden.bs.modal', function () {
        $('#detailMap').attr('src', '');
        $('#detailPhoto').attr('src', '');
    });
});
</script>
@stop
