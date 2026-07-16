@foreach ($days as $dayNumber => $dayName)
    @php
        $value = $values[$dayNumber - 1] ?? [];
        $isWorkday = array_key_exists('is_workday', $value) ? (bool) $value['is_workday'] : true;
    @endphp
    <tr id="{{ $prefix }}-schedule-{{ $dayNumber }}">
        <td class="align-middle font-weight-bold">
            {{ $dayName }}
            <input type="hidden" name="schedules[{{ $dayNumber - 1 }}][day_of_week]" value="{{ $dayNumber }}">
        </td>
        <td class="align-middle">
            <div class="custom-control custom-switch">
                <input id="{{ $prefix }}-workday-{{ $dayNumber }}" type="checkbox" name="schedules[{{ $dayNumber - 1 }}][is_workday]" value="1"
                       class="custom-control-input workday-toggle" @checked($isWorkday)>
                <label class="custom-control-label" for="{{ $prefix }}-workday-{{ $dayNumber }}">Aktif</label>
            </div>
        </td>
        <td><input type="time" data-field="check_in_time" name="schedules[{{ $dayNumber - 1 }}][check_in_time]" value="{{ $value['check_in_time'] ?? '08:00' }}" class="form-control" required></td>
        <td><input type="time" data-field="middle_time" name="schedules[{{ $dayNumber - 1 }}][middle_time]" value="{{ $value['middle_time'] ?? '12:00' }}" class="form-control" required></td>
        <td><input type="time" data-field="check_out_time" name="schedules[{{ $dayNumber - 1 }}][check_out_time]" value="{{ $value['check_out_time'] ?? '17:00' }}" class="form-control" required></td>
    </tr>
@endforeach
