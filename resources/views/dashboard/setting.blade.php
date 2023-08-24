@extends('layouts.master')

@section('title', 'Setting')

@section('container')
    <section class="content-header">
        <h1>
            Dashboard Setting
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header">
                        <label for="" class="text-muted">Asal Pengiriman Barang : </label>
                        <h3>Kabupaten/Kota <b>{{ $CityName }}</b></h3>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <form id="form" method="POST" action="{{ route('setting.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="province">Province:</label>
                                <select class="form-control select2" name="province" id="province" required>
                                    <option value="" disabled selected>Pilih Provinsi</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province['province_id'] }}">{{ $province['province'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="city">City:</label>
                                <select class="form-control select2" name="city" id="city" required></select>
                            </div>
                            <div class="form-group">
                                <label for="email">Email :</label>
                                <input class="form-control" type="text" name="email" id="" value="{{ $email }}">
                            </div>
                            <div class="form-group">
                                <label for="telp">Telp :</label>
                                <input class="form-control" type="text" name="telp" id="" value="{{ $telp }}">
                            </div>
                            <div class="form-group">
                                <label for="telp">Address :</label>
                                <input class="form-control" type="text" name="address" id="" value="{{ $address }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#city').prop('disabled', true);
            $('#province').on('change', function() {
                const provinceId = $(this).val();

                $.get(`/cities?province_id=${provinceId}`, function(data) {
                    $('#city').empty();
                    $('#city').prop('disabled', false);

                    $('#city').append(`<option value="" disabled selected>Pilih Kota</option>`);
                    data.forEach(city => {
                        $('#city').append(`<option value="${city.city_id}">${city.type} ${city.city_name}</option>`);
                    });
                });
            });
            // $('#city').on('change', function() { const cityId = $(this).val(); $('#form').submit(); });
        });
    </script>
@endsection
