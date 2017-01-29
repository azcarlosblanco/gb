@extends('layouts.master')

@section('novatech_module_title')
    <div class="col-lg-10">
    	<div>
	    	<a href="{{ action('\Modules\User\Http\Controllers\UserController@create') }}" class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Create
			</a>
		</div>
	</div>
@stop

@section('novatech_content')
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
        	<div class="ibox-title">
				Usuarios
			</div>
            <div class="ibox-content">
            	<table class="table table-hover">
					<thead>
						<tr>
							<td><strong>Nombres</strong></td>
							<td><strong>Correo electrónico</strong></td>
							<td><strong>Editar</strong></td>
							<td><strong>Eliminar</strong></td>
						</tr>
						
						@foreach ($users as $usuario)
							<tr>
								<td>{{ $usuario->name  }} {{ $usuario->lastname }}</td>
								<td>{{ $usuario->email }}</td>
								<td>
								<!--if (Auth::user()->can(['editar-usuarios']))!-->
									
									<a href="{{ action('\Modules\Users\Http\Controllers\UsersController@edit', $usuario->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Editar</a>

								
								</td>
								<td>
								<!-- if (Auth::user()->can(['eliminar-usuarios']))!-->

									<a href="#" data-href="{{ action('\Modules\Users\Http\Controllers\UsersController@delete', $usuario->id) }}" class="btn btn-danger btn-xs btn-borrar"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Eliminar</a>

								
								</td>
							</tr>
						@endforeach
					</thead>
				</table>
            </div>
        </div>
    </div>
@stop

@section('scripts')
	{!! Html::script('assets/js/plugins/sweetalert/sweetalert.min.js') !!}
	<script type="text/javascript">
		$(function () {
			$(".btn-borrar").on("click", function (event) {
				event.preventDefault()
				var href = $(this).attr('data-href');
				swal({
				  title: "¿Está seguro de eliminar este usuario?",
				  text: "Está a punto de eliminar un usuario. Esta acción no se puede deshacer.",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonColor: "#DD6B55",
				  confirmButtonText: "Si, eliminar",
				  cancelButtonText: "No eliminar",
				  closeOnConfirm: true
				},
				function(isConfirm){
					if (isConfirm) {
						window.location.href=href
					};
				});

			})
		})
	</script>
@stop