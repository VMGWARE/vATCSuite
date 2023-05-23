<div class="modal fade" id="runway-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Runway List for {{ strtoupper($airport->icao) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><i class="fa-solid fa-road"></i></th>
                            <th scope="col"><i class="fa-solid fa-wind"></i></th>
                            <th scope="col"><i class="fa-solid fa-plus-minus"></i><i class="fa-solid fa-wind"></i>
                            </th>
                            <th scope="col"><i class="fa-solid fa-plane-arrival"></i></th>
                            <th scope="col"><i class="fa-solid fa-plane-departure"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($runways as $runway)
                            <tr>
                                <th scope="row">{{ $runway['runway'] }}</th>
                                <td>{{ $runway['wind_dir'] }}</td>
                                <td>{{ $runway['wind_diff'] }}</td>
                                <td><input class="form-check-input" type="checkbox" name="landing_runways[]"
                                        value="{{ $runway['runway'] }}"></td>
                                <td><input class="form-check-input" type="checkbox" name="departing_runways[]"
                                        value="{{ $runway['runway'] }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
