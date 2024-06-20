@extends('admin.layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Shipping Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('categories.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            @include('admin.message')
            <form action="" method="POST" id="shippingForm" name="shippingForm">
                @csrf
                <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <select class="form-control" name="country" id="country" >
                                    <option value="">Select a Country</option>
                                @if ($countries->isNotEmpty())
                                    @foreach ($countries as $country )
                                        <option
                                            {{ ($shippingCharge->country_id == $country->id) ? 'selected' : '' }} 
                                            value="{{ $country->id }}">{{ $country->name }}
                                        </option>
                                    @endforeach
                                        <option 
                                        {{ ($shippingCharge->country_id == 'rest_of_world') ? 'selected' : '' }}
                                         value="rest_of_world">Rest of the World</option>
                                @endif
                                </select>
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <input value="{{ $shippingCharge->amount }}" type="text" name="amount" id="amount" class="form-control" placeholder="Amount" >
                                <p></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            </form>

        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('CustomJs')

    <script>
        /*** Form Submition with Validation ***/
        $("#shippingForm").submit(function (event) {
            event.preventDefault();
            let element = $(this);
            $("button[type=submit]").prop('disabled', true);

            $.ajax({
                url: '{{ route("shipping.update", $shippingCharge->id) }}',
                type: 'PUT',
                data: element.serializeArray(),
                dataType: 'json',
                success: function (response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response["status"] == true) {
                        /**** Redirect after creating a category ****/
                        window.location.href = "{{ route('shipping.create') }}";

                    } else {
                        // Response status is not true, handle errors
                        let errors = response['errors'];

                        if (errors['country']) {
                            $("#country").addClass('is-invalid').siblings('P').addClass('invalid-feedback').html(errors['country']);
                        } else {
                            $("#country").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        }

                        if (errors['amount']) {
                            $("#amount").addClass('is-invalid').siblings('P').addClass('invalid-feedback').html(errors['amount']);
                        } else {
                            $("#amount").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    console.log("Something went wrong.!");
                    console.log(jqXHR.responseText);
                }
            });
        });

    </script>

@endsection
