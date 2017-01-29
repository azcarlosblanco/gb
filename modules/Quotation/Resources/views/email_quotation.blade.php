<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<script src="angular-local-storage.min.js"></script>
	<script src="angular.js"></script>
	<meta name="viewport" content="width=	, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<style>
	.bor{
		border-style: ridge; 
		border-width: 1px;
		border-radius: 5px;
	}
	.alert-info {
	    color: #31708f;
	    background-color: #d9edf7;
	    border-color: #bce8f1;
	}
	.alert {
	    padding: 15px;
	    margin-bottom: 20px;
	    border: 1px solid transparent;
	    border-radius: 4px;
	}
	.quotation-section-header{
		color: #61a8cf;
	    margin-bottom: 10px;
	    text-align: center;
	    font-weight: 600;
	}
	.container-fluid{
		padding-right: 15px;
	    padding-left: 15px;
	    margin-right: auto;
	    margin-left: auto;
	}
	.table-bordered {
	    border: 1px solid #ddd;
	}
	.table {
	    width: 100%;
	    max-width: 100%;
	    margin-bottom: 20px;
	}
</style>
<body>
	<div class="container-fluid">
		<div >
			<div class="container-fluid">
				<h3 class="text-Center"></h3>
				<div class="content">
					<p>Estimado {{$client_name}}</p>
					<p>Gracias Por su interés en nuestros seguros a continuación enviamos la
					información de su cotización</p>
					<div class="container-fluid">
							<h5 class="quotation-section-header">
								<span>Información Afiliados</span>
							</h5>
							<div class="container-fluid ">
								<table class="table table-bordered">
									<tr>
										<th>Edad Propietario</th>
										<th>Edad Cónyugue</th>
										<th>Número Niños</th>
									</tr>
									<tr>
										
										<td>{{$afi['owner_age']}}</td>
										<td>{{$afi['spouse_age']}}</td>
										<td>{{$afi['number_kid']}}</td>
									</tr>
								</table>
							</div>
							<br>
							<label class="col-xs-12 control-label">Numero de Pagos: {{$number_payments}}</label>
					</div>
					<br>
					<div class="form-section" id="plansInfo">
						<h5 class="quotation-section-header">
							<span>Valores Cotización</span>
						</h5>
						<div class="container-fluid">
							@foreach ($plans as $plan)
								<div class="alert alert-info">
									<div><span style="font-weight:bold">Compañía de Seguro:</span> {{$plan['insurance_company']}}</div>
									<div><span style="font-weight:bold">Nombre Plan: </span>{{$plan['plan_name']}}</div>
									<div><span style="font-weight:bold">Opción de Deducible: </span> {{$plan['deductible_name']}}</div>
									<div><span style="font-weight:bold">Total: </span>$ {{$plan['total']}} </div>
									<br>
									<div class="container-fluid">
										<div class="row">
											@foreach ($plan['quotes'] as $quote)
												<div class="col-xs-3" style="display:inline-block; width:250px; vertical-align: top;">
													<table class="table table-bordered" style="background-color:white">
														@foreach ($quote['items'] as $item)
															<tr>
																<td>{{$item['name']}}: </td>
																<td>$ {{$item['amount']}}</td>	
															</tr>
														@endforeach
														<tr>
															<th>Total:</th>
															<td>${{$quote['total']}}</td>
														</tr>
													</table>
												</div>
											@endforeach
										</div>
									</div>
								</div>
								<br>
							@endforeach	
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>