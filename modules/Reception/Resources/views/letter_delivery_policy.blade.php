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
				<p class="letter_ctitle">{{ $customer_title }}</p>
				<p class="letter_cname">{{ $customer_name }}</p>
				<p>Ciudad.-</p>
			</div>

			<p class="letter_ref">Ref: Póliza Best Doctors – {{ $plan_name }} ID. {{ $policy_num }}</p>


			<p >
				Gracias por habernos elegido como sus asesores en un tema tan delicado como su salud y la de sus seres queridos.
				<br>
				<br>
				Hemos procesado su aplicación con la compañía Best Doctors S.A., la misma que ha sido aprobada y en consecuencia emitida. Por este motivo, adjunto a la presente encontrará la siguiente información:
			</p>


			<ul >
				<li> Factura electrónica enviada a su email.</li>
				<li> Anexo B, Cuadro de beneficios máximos (Original y Copia)</li>
				<li> Cobertura (Original y Copia)</li>
				<li> Anexo A, Condiciones Particulares (Original y Copia)</li>
				<li> Condiciones Generales de Afiliación (Original y Copia)</li>
				<li> Tarjeta de Membresía ( {{ $num_cards }} )</li>
			</ul>


			<p >
				Le solicitamos remitirnos las copias de estos documentos debidamente firmados para la compañía aseguradora.
			</p>


			<p style="margin-top:20px; margin-bottom: 20px">No dude en comunicarse con nosotros ante cualquier inquietud.</p>
			
			<p>Atentamente,</p>

			<div id="letter-sign" style="margin-top:40px; margin-bottom: 20px">
				<p>Flor Maridueña A.</p>
				<p>Jefe Operativo – Comercial</p>
			</div>
		</div>
	</body>
</html>