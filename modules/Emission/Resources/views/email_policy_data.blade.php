<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Correo Info. Poliza</title>
    {!! Html::style('assets/css/pdf.css') !!}
  </head>
  <body>
       Estimado Agente,
      <br>A continuación se mostrara el detalle de la información de la póliza que solicito:</br>
      <br>
      <table  bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" cellspacing="0" cellpadding="0" width="50%">
       <thead>
        <tr>
         <th bgcolor= "#F5ECCE" colspan="2">Datos Generales</th>
        </tr>
       </thead>
       <tbody>
        <tr>
          <td style="width:30%"> Nombre del Cliente: </td>
          <td >  {{$client_name}}  </td>
        </tr>
        <tr>
          <td > Plan/Opcion: </td>
          <td >  {{$plan}} / {{$deductibles}} </td>
        </tr>
        <tr>
          <td > Numero de Poliza: </td>
          <td > {{$policy_number}} </td>
        </tr>
      </tbody>
     </table>
     </br>
     <br> 
      <table   bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" cellspacing="0" cellpadding="0" style="width:50%">
      <thead>
        <tr>
         <th bgcolor= "#F5ECCE" colspan="4">Afiliados</th>
        </tr>
       </thead>
      <thead>
       <tr>
        <th class="name"   style="width:20%">Nombre</th>
        <th class="type"   style="width:20%">Tipo</th>
        <th class="dob"    style="width:20%">Fecha de Nacimiento</th>
        <th class="edate"  style="width:20%">Fecha de Efectividad</th>
       </tr>
      </thead>
      <tbody>
      @foreach ($affiliate as $key => $afi)
        <tr style="text-align:center">
          <td class="name">{{ $afi['full_name'] }}</td>
          <td class="type" style="text-align:left">{{ $afi['type'] }}</td>
          <td class="dob">{{ $afi['dob'] }}</td>
          <td class="edate">{{ $afi['start_date'] }}</td>
        </tr>
      @endforeach
     </tbody>
     </table>
    </br>
     <br> 
      <table  bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" cellspacing="0" cellpadding="0" style="width:50%">
      <thead>
        <tr>
         <th bgcolor= "#F5ECCE" colspan="4">Exclusiones/Enmiendas</th>
        </tr>
       </thead>
      <thead>
       <tr>
        <th class="affiliate"      style="width:20%">Afiliado</th>
        <th class="edob"           style="width:20%">Fecha de Nacimiento</th>
        <th class="etype"          style="width:20%">Tipo</th>
        <th class="edescription"   style="width:20%">Descripcion</th>
       </tr>
      </thead>
      <tbody>
      @foreach ($exclusion as $ex)
        <tr style="text-align:center">
          <td class="affiliate">{{ $ex['full_name'] }}</td>
          <td class="edob" style="text-align:left">{{ $ex['dob'] }}</td>
          <td class="etype">{{ $ex['type'] }}</td>
          <td class="edescription">{{ $ex['description'] }}</td>
        </tr>
      @endforeach
     </tbody>
     </table>
    </br>
     <br> 
      <table  bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" cellspacing="0" cellpadding="0" style="width:50%">
      <thead>
        <tr>
         <th bgcolor= "#F5ECCE" colspan="3">Anexos</th>
        </tr>
       </thead>
      <thead>
       <tr>
        <th class="aaffiliate"       style="width:20%">Afiliado</th>
        <th class="aanexo"           style="width:20%">Anexo</th>
        <th class="aedate "          style="width:20%">Fecha Efectiva</th>
      </tr>
      </thead>
      <tbody>
      @foreach ($anexo as $an)
        <tr style="text-align:center">
          <td class="aaffiliate">{{ $an['full_name'] }}</td>
          <td class=aanexo style="text-align:left">{{ $an['anexo'] }}</td>
          <td class="aedate">{{ $an['e_date'] }}</td>
        </tr>
      @endforeach
     </tbody>
     </table>
    </br>
    <br>  
      <table  bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" cellspacing="0" cellpadding="0" width="50%">
       <thead>
        <tr>
         <th bgcolor= "#F5ECCE" colspan="2">Pagos</th>
        </tr>
       </thead>
       <tbody>
         <tr>
          <td style="width:30%"> Fecha Efectiva: </td>
          <td>  {{$edate}}  </td>
        </tr>
        <tr>
          <td > Modo de Pago: </td>
          <td >  {{ $number_payments }} </td>
        </tr>
        </br>
        </tbody>
       </table>
       <br>
       <table>
        <tbody>
         <br>
             <tr colspan="3">
             
               @foreach ($pago as $pa)
               <table   bordercolordark="#819FF7" bordercolorlight="#81BEF7" border="2px" width="50%">
               <br>
                <tr>
                 <th bgcolor= "#F5ECCE" class="pcuota"         style="width:16%">Cuota {{$pa['quote_number']}} </th>
                @foreach ($pa['items'] as $it)
                   <table  border="2px" width="50%">
                   <td class="pvalor"       style="width:16%"> {{ $it['value'] }}</td>
                   <td class="pdescripcion" style="width:16%">{{ $it['concept'] }}</td>
                   </table>
                @endforeach
                <table border="2px" cellspacing="0" cellpadding="0" width="50%">
                <td class="ptotal" style="width:16%">{{ $pa['total'] }}</td>
                <td style="width:16%"> Total </td>
                </table>
                </tr>
                </br>
               </table>
            </tr>
            @endforeach
            <br>
            <tr style="width:25%"> Total a pagar: </tr>
            <td>  {{ $total_cost }}  </td> 
            </br>
          </br>
          </tbody>
      </table>
    </body>
</html>
  