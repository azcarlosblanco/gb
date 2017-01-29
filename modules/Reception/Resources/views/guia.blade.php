<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Example 2</title>
    {!! Html::style('assets/css/pdf.css') !!}
  </head>
  <body>
    <main>
      <div id="cabecera" style='border-style: solid; padding: 20px'>
        <div id="guia">
          <h1> Guía de Envio {{ $track_number }}</h1>
          <div class="date">Fecha de Envió: {{ $date }}</div>
        </div>
      </div>
      <div id="destinatario" style='border-right-style: solid;border-left-style: solid; padding: 20px'>
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <thead>
            <tr>
              <th colspan="2">Destinatario</th>
            </tr>
          </thead>
          <tbody>
              <tr>
                <td style="width:30%"> Nombre Destinatario: </td>
                <td >{{ $receiver_name }}</td>
              </tr>
              <tr>
                <td > Dirección Destinatario: </td>
                <td >{{ $receiver_address }}</td>
              </tr>
              <tr>
                <td > Teléfono Destinatario: </td>
                <td >{{ $receiver_phone }}</td>
              </tr>
          </tbody>
        </table>
      </div>
      <div id="destinatario" style='border-style: solid; padding: 20px'>
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <thead>
            <tr>
              <th colspan="2">Mensajero</th>
            </tr>
          </thead>
          <tbody>
              <tr>
                <td style="width:30%"> Nombre Mensajero: </td>
                <td >{{ $mensajero_name }}</td>
              </tr>
              <tr>
                <td > Mensajero ID: </td>
                <td >{{ $mensajero_id }}</td>
              </tr>
          </tbody>
        </table>
      </div>
      <div style='border-style: solid;
                  border-top-style: none;
                  padding: 20px'>
        <table border="1px" cellspacing="0" cellpadding="0" style="width:100%">
          <thead>
            <tr>
              <th class="no" style="width:10%">#</th>
              <th class="desc" style="width:80%">Descripción</th>
              <th class="unit" style="width:10%">Número Copias</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($guia_items as $index => $item)
              <tr style="text-align:center">
                <td class="no">{{ $index }}</td>
                <td class="desc" style="text-align:left">{{ $item['description'] }}</td>
                <td class="unit">{{ $item['num_copies'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      <div id="firma" style="margin-top:20px">
        <table border="0" cellspacing="0" cellpadding="0" style="width:100%">
          <tbody>
              <tr>
                <td border="0" style="width:50%;text-align:center"> ____________________ </td>
                <td border="0" style="width:50%;text-align:center"> ____________________ </td>
              </tr>
              <tr>
                <td border="0" style="width:50%;text-align:center">   Recibí Conforme    </td>
                <td border="0" style="width:50%;text-align:center">       Emisior        </td>
              </tr>
          </tbody>
        </table>
      </div>
  </body>
</html>
