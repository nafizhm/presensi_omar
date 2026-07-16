@extends('adminlte::page')

@section('title', 'Titik Lokasi')
@section('plugins.Datatables', true)

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .location-map { height: 310px; border-radius: .35rem; border: 1px solid #ced4da; }
    .leaflet-container { z-index: 1; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Titik Lokasi</h1>
            <small class="text-muted">Kelola lokasi dan radius presensi.</small>
        </div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addLocationModal">
            <i class="fas fa-plus mr-1"></i> Tambah Titik Lokasi
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="locationsTable" class="table table-bordered table-striped table-hover w-100">
                    <thead>
                    <tr>
                        <th>Nama Lokasi</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Radius</th>
                        <th>Zona Waktu</th>
                        <th>Ditandai Oleh</th>
                        <th>Status</th>
                        <th width="110">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($locations as $location)
                        <tr>
                            <td><strong>{{ $location->name }}</strong></td>
                            <td><code>{{ $location->latitude }}</code></td>
                            <td><code>{{ $location->longitude }}</code></td>
                            <td>{{ number_format($location->radius_meters) }} meter</td>
                            <td>{{ match($location->timezone) { 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT', default => 'WIB' } }}</td>
                            <td>{{ $location->markedBy?->username ?? 'Pengguna dihapus' }}</td>
                            <td>
                                <span class="badge {{ $location->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($location->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-location"
                                        data-id="{{ $location->id }}"
                                        data-name="{{ $location->name }}"
                                        data-latitude="{{ $location->latitude }}"
                                        data-longitude="{{ $location->longitude }}"
                                        data-radius="{{ $location->radius_meters }}"
                                        data-timezone="{{ $location->timezone }}"
                                        data-username="{{ $location->markedBy?->username ?? 'Pengguna dihapus' }}"
                                        data-status="{{ $location->status }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.settings.locations.destroy', $location) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus titik lokasi {{ addslashes($location->name) }}?')">
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

    <div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.settings.locations.store') }}" method="POST" class="location-form modal-content">
                @csrf
                <input type="hidden" name="_form_mode" value="add">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Tambah Titik Lokasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('_form_mode') === 'add' ? old('name') : '' }}" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Map Lokasi Perangkat</label>
                        <button type="button" class="btn btn-sm btn-outline-primary locate-device" data-target="add">
                            <i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Perangkat
                        </button>
                    </div>
                    <div id="addMap" class="location-map mb-2"></div>
                    <small id="addMapStatus" class="form-text text-muted mb-3">Klik peta atau geser marker untuk menentukan titik.</small>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Latitude <span class="text-danger">*</span></label>
                            <input id="add-latitude" type="text" name="latitude"
                                   value="{{ old('_form_mode') === 'add' ? old('latitude', '-6.2000000') : '-6.2000000' }}"
                                   class="form-control" readonly required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Longitude <span class="text-danger">*</span></label>
                            <input id="add-longitude" type="text" name="longitude"
                                   value="{{ old('_form_mode') === 'add' ? old('longitude', '106.8166660') : '106.8166660' }}"
                                   class="form-control" readonly required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Batas Radius (meter) <span class="text-danger">*</span></label>
                            <input id="add-radius" type="number" name="radius_meters" min="1" max="100000"
                                   value="{{ old('_form_mode') === 'add' ? old('radius_meters', 100) : 100 }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Username yang Menandai</label>
                            <input type="text" value="{{ auth()->user()->username }}" class="form-control" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Zona Waktu <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-control" required>
                                <option value="Asia/Jakarta" @selected(old('timezone', 'Asia/Jakarta') === 'Asia/Jakarta')>WIB (UTC+7)</option>
                                <option value="Asia/Makassar" @selected(old('timezone') === 'Asia/Makassar')>WITA (UTC+8)</option>
                                <option value="Asia/Jayapura" @selected(old('timezone') === 'Asia/Jayapura')>WIT (UTC+9)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('_form_mode') === 'add' && old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
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

    <div class="modal fade" id="editLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="location-form modal-content" id="editLocationForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="_location_id" id="edit-id" value="{{ old('_location_id') }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Titik Lokasi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lokasi <span class="text-danger">*</span></label>
                        <input id="edit-name" type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Map Lokasi</label>
                        <button type="button" class="btn btn-sm btn-outline-primary locate-device" data-target="edit">
                            <i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Perangkat
                        </button>
                    </div>
                    <div id="editMap" class="location-map mb-2"></div>
                    <small id="editMapStatus" class="form-text text-muted mb-3">Klik peta atau geser marker untuk mengubah titik.</small>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Latitude <span class="text-danger">*</span></label>
                            <input id="edit-latitude" type="text" name="latitude" value="{{ old('latitude') }}" class="form-control" readonly required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Longitude <span class="text-danger">*</span></label>
                            <input id="edit-longitude" type="text" name="longitude" value="{{ old('longitude') }}" class="form-control" readonly required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Batas Radius (meter) <span class="text-danger">*</span></label>
                            <input id="edit-radius" type="number" name="radius_meters" min="1" max="100000" value="{{ old('radius_meters') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Username yang Menandai</label>
                            <input id="edit-username" type="text" name="_marked_by_username" value="{{ old('_marked_by_username') }}" class="form-control" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Zona Waktu <span class="text-danger">*</span></label>
                            <select id="edit-timezone" name="timezone" class="form-control" required>
                                <option value="Asia/Jakarta">WIB (UTC+7)</option>
                                <option value="Asia/Makassar">WITA (UTC+8)</option>
                                <option value="Asia/Jayapura">WIT (UTC+9)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select id="edit-status" name="status" class="form-control" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
$(function () {
    $('#locationsTable').DataTable({
        responsive: true, autoWidth: false, order: [[0, 'asc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        }
    });

    const maps = {};
    const defaultPosition = [-6.2000000, 106.8166660];
    const updateUrl = @js(route('admin.settings.locations.update', '__ID__'));

    function setCoordinates(bundle, latlng) {
        const latitude = Number(latlng.lat).toFixed(7);
        const longitude = Number(latlng.lng).toFixed(7);
        $('#' + bundle.latitudeId).val(latitude);
        $('#' + bundle.longitudeId).val(longitude);
        bundle.marker.setLatLng([latitude, longitude]);
        bundle.circle.setLatLng([latitude, longitude]);
    }

    function createMap(key, lat, lng) {
        const prefix = key === 'add' ? 'add' : 'edit';
        const map = L.map(prefix + 'Map', { zoomControl: true }).setView([lat, lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([lat, lng], { draggable: true, autoPan: true }).addTo(map);
        const radius = Number($('#' + prefix + '-radius').val()) || 100;
        const circle = L.circle([lat, lng], {
            radius: radius, color: '#0D9488', fillColor: '#0D9488', fillOpacity: 0.14
        }).addTo(map);

        const bundle = {
            map, marker, circle,
            latitudeId: prefix + '-latitude',
            longitudeId: prefix + '-longitude',
            radiusId: prefix + '-radius',
            statusId: prefix + 'MapStatus'
        };

        map.on('click', event => setCoordinates(bundle, event.latlng));
        marker.on('dragend', event => setCoordinates(bundle, event.target.getLatLng()));
        $('#' + bundle.radiusId).on('input', function () {
            circle.setRadius(Math.max(1, Number(this.value) || 1));
        });

        maps[key] = bundle;
        return bundle;
    }

    function setMapPosition(bundle, lat, lng, zoom = 17) {
        setCoordinates(bundle, { lat: Number(lat), lng: Number(lng) });
        bundle.map.setView([lat, lng], zoom);
        bundle.circle.setRadius(Math.max(1, Number($('#' + bundle.radiusId).val()) || 1));
        setTimeout(() => bundle.map.invalidateSize(), 100);
    }

    function locateDevice(bundle) {
        $('#' + bundle.statusId).text('Mencari lokasi perangkat...');
        bundle.map.once('locationfound', function (event) {
            setMapPosition(bundle, event.latlng.lat, event.latlng.lng, 18);
            $('#' + bundle.statusId).text('Lokasi perangkat ditemukan. Geser marker bila perlu.');
        });
        bundle.map.once('locationerror', function () {
            $('#' + bundle.statusId).text('Lokasi perangkat tidak dapat dibaca. Pastikan izin lokasi aktif dan halaman memakai HTTPS/localhost.');
        });
        bundle.map.locate({ setView: false, enableHighAccuracy: true, timeout: 10000 });
    }

    $('#addLocationModal').on('shown.bs.modal', function () {
        const lat = Number($('#add-latitude').val()) || defaultPosition[0];
        const lng = Number($('#add-longitude').val()) || defaultPosition[1];
        const bundle = maps.add || createMap('add', lat, lng);
        setMapPosition(bundle, lat, lng);
        if (!$(this).data('location-requested')) {
            $(this).data('location-requested', true);
            locateDevice(bundle);
        }
    });

    $('#editLocationModal').on('shown.bs.modal', function () {
        const lat = Number($('#edit-latitude').val()) || defaultPosition[0];
        const lng = Number($('#edit-longitude').val()) || defaultPosition[1];
        const bundle = maps.edit || createMap('edit', lat, lng);
        setMapPosition(bundle, lat, lng);
    });

    $('.locate-device').on('click', function () {
        const key = $(this).data('target');
        if (maps[key]) locateDevice(maps[key]);
    });

    $('.edit-location').on('click', function () {
        const button = $(this);
        $('#edit-id').val(button.data('id'));
        $('#edit-name').val(button.attr('data-name'));
        $('#edit-latitude').val(button.attr('data-latitude'));
        $('#edit-longitude').val(button.attr('data-longitude'));
        $('#edit-radius').val(button.attr('data-radius'));
        $('#edit-timezone').val(button.attr('data-timezone'));
        $('#edit-username').val(button.attr('data-username'));
        $('#edit-status').val(button.attr('data-status'));
        $('#editLocationForm').attr('action', updateUrl.replace('__ID__', button.data('id')));
        $('#editLocationModal').modal('show');
    });

    $('.location-form').on('submit', function () {
        const button = $(this).find('.save-btn');
        button.prop('disabled', true);
        button.find('.spinner-border').removeClass('d-none');
        button.find('.button-label').text('Menyimpan...');
    });

    @if ($errors->any() && old('_form_mode') === 'add')
        $('#addLocationModal').modal('show');
    @elseif ($errors->any() && old('_form_mode') === 'edit')
        $('#editLocationForm').attr('action', updateUrl.replace('__ID__', @js(old('_location_id'))));
        $('#edit-status').val(@js(old('status')));
        $('#edit-timezone').val(@js(old('timezone')));
        $('#edit-username').val(@js(old('_marked_by_username')));
        $('#editLocationModal').modal('show');
    @endif

    $('#successToast').toast('show');
});
</script>
@stop
