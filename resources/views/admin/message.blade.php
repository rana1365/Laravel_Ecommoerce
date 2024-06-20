
@if(Session::has('error'))
<div class="alert alert-danger alert-dismissible">

    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h6>
        <i class="icon fa fa-ban"></i> Error!<br/> {{ Session::get('error') }}
    </h6>

</div>

@endif

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">

    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h6>
        <i class="icon fa fa-check"></i> Success!<br/><br/> {{ Session::get('success') }}
    </h6>

</div>
@endif
