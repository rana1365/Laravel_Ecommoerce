@extends('admin.layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Sub-Category</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('sub-categories.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <form action="" method="POST" id="subCategoryForm" name="subCategoryForm">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="name">Category</label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">Select a Category</option>
                                        @if($categories->isNotEmpty())
                                            @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Name" />
                                    <p></p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug">Slug</label>
                                    <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" />
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Block</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="show_home">Appearance</label>
                                    <select name="show_home" id="show_home" class="form-control">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('sub-categories.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
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
        $("#subCategoryForm").submit(function (event) {
            event.preventDefault();
            let element = $(this);
            $("button[type=submit]").prop('disabled', true);

            $.ajax({
                url: '{{ route("sub-categories.store") }}',
                type: 'POST',
                data: element.serializeArray(),
                dataType: 'json',
                success: function (response) {

                    $("button[type=submit]").prop('disabled', false);

                    if (response["status"] == true) {

                        /**** Redirect after creating a category ****/

                        window.location.href = "{{ route('sub-categories.index') }}";

                        // Successful response, clear any previous error messages
                        $("#name").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        $("#slug").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        $("#category").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");

                    } else {
                        // Response status is not true, handle errors
                        let errors = response['errors'];

                        if (errors['name']) {
                            $("#name").addClass('is-invalid').siblings('P').addClass('invalid-feedback').html(errors['name']);
                        } else {
                            $("#name").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        }

                        if (errors['slug']) {
                            $("#slug").addClass('is-invalid').siblings('P').addClass('invalid-feedback').html(errors['slug']);
                        } else {
                            $("#slug").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        }

                        if (errors['category']) {
                            $("#category").addClass('is-invalid').siblings('P').addClass('invalid-feedback').html(errors['category']);
                        } else {
                            $("#category").removeClass('is-invalid').siblings('P').removeClass('invalid-feedback').html("");
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    console.log("Something went wrong.!");
                    console.log(jqXHR.responseText);
                }
            });
        });

        /******Slug auto fill ajax call ********/
        $("#name").change(function () {

            $("button[type=submit]").prop('disabled', true);

            let element = $(this);

            $.ajax({
                url: '{{ route("getSlug") }}',
                type: 'get',
                data: {title: element.val()},
                dataType: 'json',
                success: function (response) {
                    $("button[type=submit]").prop('disabled', false);
                    if(response["status"] == true) {
                        $("#slug").val(response["slug"]);
                    }
                }
            });

        });

    </script>

@endsection

