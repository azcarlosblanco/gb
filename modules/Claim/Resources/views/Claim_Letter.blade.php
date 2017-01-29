<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	    <title>Carta de Envió de Póliza</title>
	    <style>
	    	.letter_salute{
	    					margin-top: 80px;
	    					margin-bottom: 40px;
	    					}
	    	.letter_cname {
	    					font-weight: 900;
	    					}
	    	.letter_ref {
	    					font-weight: 900;
	    					margin-bottom: 40px;
	    				}
	    	.letter_itmes{
	    					margin-top:20px;
	    					margin-bottom: 20px;	
	    				}
	    	

			body {
					margin-top: 100px;
					margin-left: 40px;
					margin-right: 40px;
					font-size:18px;
				}
			p   {
					text-align: justify;
					margin: 2px;
				}
		</style>
	</head>
	<body>
	  	<div>
			

			<p class="letter_date">Guayaquil, {{ $date }}</p>

			<div class="letter_salute">
				<p class="letter_ctitle">Señores</p>
				<p class="letter_cname"><b>BEST DOCTORS</b></p>
				<p>Ciudad.-</p>
			</div>

			<p class="letter_ref">Atte. </p>

			<p >
				De mis consideraciones.
				<br>
				<br>
				Por medio de la presente adjunto el siguiente reclamo:
			</p>
			<br>
			<table border="1px" cellspacing="0" cellpadding="0" style="width:100%">
				<thead>
					<tr style="font-size:x-small;">
						<th style="width:15%;">FECHA ENVIADO</th>
						<th style="width:7%"># GB</th>
						<th style="width:18%">ASEGURADO</th>
						<th style="width:10%"># PÓLIZA</th>
						<th style="width:20%">PACIENTE</th>
						<th style="width:20%">DIAGNÓSTICO</th>
						<th style="width:10%">MONTO</th>
					</tr>
				</thead>
				<tbody>
					<tr style="text-align:center;font-size:x-small;">
						<td >{{ $date }}</td>
						<td >{{ $num_claim }}</td>
						<td >{{ $client_name }}</td>
						<td >{{ $policy }}</td>
						<td >{{ $affiliate }}</td>
						<td >{{ $diagnosis }}</td>
						<td >{{ $amount }}</td>
					</tr>
				</tbody>
	        </table>
	        <br>
			<p >
				Detalle de Facturas.
			</p>

			<table border="1px" cellspacing="0" cellpadding="0" style="width:100%">
				<thead>
					<tr style="font-size:x-small;">
						<th style="width:10%">FACTURAS</th>
						<th style="width:10%">PROVEEDOR</th>
						<th style="width:20%">VALOR</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($invoices as $index => $invoice)
						<tr style="text-align:center;font-size:x-small;">
							<td >{{ $invoice['num_invoice'] }}</td>
							<td >{{ $invoice['provider'] }}</td>
							<td >{{ $invoice['amount'] }}</td>
						</tr>
					@endforeach
				</tbody>
	        </table>
	        <br>
	        <br>
	        <p>
	        	Se adjunta los siguientes documentos:
	        </p>
	        <ul >
	        	@foreach ($listdocuments as $index => $dodument)
	        		<li>{{ $dodument['category'] }} : {{ $dodument['description'] }}, Proveedor: {{ $dodument['provider'] }} </li>
	        	@endforeach
	        </ul>

	        <br>
	        <br>
			<p style="margin-top:20px; margin-bottom: 20px">Sin otro particular por el momento, subscripbo con un cordial saludo .</p>
			
			<p>Atentamente,</p>

			<div id="letter-sign" style="margin-top:40px; margin-bottom: 20px">
				<p>Flor Maridueña A.</p>
				<p>Jefe Operativo – Comercial</p>
			</div>
		</div>
	</body>
</html>