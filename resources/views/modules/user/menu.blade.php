@section('novatech_module_title')
    <div class="col-lg-10">
    <h2>Usuarios</h2>
    <ol class="breadcrumb">
        <li>
            <a href="href={{ action('\Modules\Users\Http\Controllers\UsersController@create') }}">Create</a>
        </li>
        <li>
            <a>Forms</a>
        </li>
    </ol>
</div>
@stop
