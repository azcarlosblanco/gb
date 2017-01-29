<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Best Doctors PDF</title>
    <style>
        page[size="A4"] {
              height: 21cm;
              width: 29.7cm;
              display: block;
              margin: 0 auto;
              margin-bottom: 0.5cm;
              box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
            }
        .pdf-container {
            width: 720px;
            margin: 0px;
        }
        .pdf-container header{
            background: #155196;
            color: #FFFFFF;
        }
        .pdf-container header h1{
            padding-top: 0px;
            display: inline-block;
            margin: 0px;
            width: 50%
        }
        .pdf-container header img{
            float: right;
            margin: 20px;
            display: inline-block;
        }
        .wrapper {
            padding: 20px;
            box-sizing: border-box;
        }
        .recommendations-list p{

        }
        .recommendations-list ul{
            padding: 0px;
            width: 45%;
            display: inline-block;
            vertical-align: top;
            margin-left: 3%;
        }
        .recommendations-list li{
            list-style: circle;
            vertical-align: top;
            margin: .5%;
        }
        .head-input {
            background: #155196;
            color: #FFFFFF; 
        }
        .input input {
            width: 100%;
            border-width: 0px;
            background: rgb(241,244,255);
            min-height: 16px;
            border-bottom: 1px solid;
            border-color: black;
        }
        .input label,
        .input-radios label,
        .statement strong,
        .signature strong {
            font-size: 12px;
            font-weight: bold;
        }
        .input label span,
        .input-radios label span {
            font-weight: normal;
        }
        .input label strong,
        .input label span{
            line-height: .8em;
        }
        .pair-input .input {
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
        }
        .pair-input .input:nth-child(odd){
            width: 70%;
        }
        .pair-input .input:nth-child(even){
            width: 29%;
        }
        .group-fields {
            width: 75%;
            display: inline-block;
            vertical-align: top;
        }
        .group-signature {
            width: 24%;
            display: inline-block;
            vertical-align: top;
        }
        .input-radios input[type="radio"]+label{
            display: none;
        }
        .input-radios input[type="radio"]{
            
        }
        .input-radios input[type="text"]{
            box-sizing: border-box;
            border-width: 0px;
            background: rgb(241,244,255);
            border-bottom: 1px solid;
        }
        .input-radios input[type="text"]+label{
            margin-left: 0px;
        }
        .signature {
            margin-top: 10px;
            border: 3px #155196 solid;
            padding: 4px;
            box-sizing: border-box;
            height: 130px;
        }
        .signature hr{
            margin-top: 70px;
            border: 1px solid #000;
        }
        .pdf-container table {
            border: 1px solid #000;
            border-collapse: separate;
            border-spacing: 0px;
        }
        .pdf-container table thead th,
        .pdf-container table tbody td,
        .pdf-container table tfoot td{
            border: 1px solid #000;
        }
        .pdf-container table tfoot td{
            text-align:right;
        }
    </style>
</head>
<body>
    <page size="A4">
    <div class="pdf-container">
        <header>
            <h1>Formulario <br /> <strong>de Reclamación</strong></h1>
            <img src="{{ asset('assets/img/db_logo.png') }}" alt="Best ">
        </header>
        <section class="wrapper">
            <div class="recommendations-list">
                <p>Por favor asegurese de:</p>
                <ul >
                    <li>Completar un formulario por evento, por miembro familiar que solicita un reembolso.</li>
                    <li>Enviar las facturas originales detallando todos los servicios recibidos asi como los recibos de pagos por los gastos incurridos</li>
                </ul>
                <ul >
                    <li>Enviar y completar este formulario en su totalidad junto con la información correspondiente dentro de los 180 dias desde la fecha de servicio</li>
                    <li>Para reclamos por servicios dentro de los EEUU se debe enviar el formulario para solicitar información médica directamente al proveedor</li>
                </ul>
            </div>
            <h3 class="head-input">DATOS PERSONALES DEL PACIENTE</h3>
            <div class="pair-input">
                <div class="input">
                    <input type="text" name="patientName" id="patientName" value="{{$affiliate_name}}">
                    <label for="patientName">Nombre completo del Paciente</label>
                </div>
                <div class="input">
                    <input type="text" name="birthDate" id="birthDate" value="{{$aff_dob}}">
                    <label for="birthDate"><strong>FECHA DE NACIMIENTO </strong> <span>(MM/DD/AAAA)</span></label>
                </div>
            </div>
            <div class="pair-input">
                <div class="input">
                    <input type="text" name="affilliateName" id="affilliateName" value="{{ $client_name }}">
                    <label for="affilliateName">Nombre completo del afiliado principal</label>
                </div>
                <div class="input">
                    <input type="text" name="contractNumber" id="contractNumber" value="{{ $policy }}">
                    <label for="contractNumber">Número de Contrado</label>
                </div>
            </div>
            <h3 class="head-input">DETALLES DEL DIAGNÓSTICO / ACCIDENTE </h3>
            <p>Este formulario será válido como Reporte Médico si incluye la información médica completa y es firmado y sellado por el médico tratante. <br />  Si se requiere de espacio adicional, por favor envíe un Reporte Médico completo.</p>
            <div class="group-fields">
                <div class="pair-input">
                    <div class="input">
                        <input type="text" name="accidentType" id="accidentType" value="{{$diagnosis}}">
                        <label for="accidentType">
                            <strong>DIAGNÓSTICO O TIPO DE ACCIDENTE</strong> <br/>
                            <span>(EN CASO DE ACCIDENTE, INCLUIR UN REPORTE POLICIAL)</span>
                        </label>
                    </div>
                    <div class="input">
                        <input type="text" name="sypmtomDate" id="sypmtomDate" value="v" style="color:rgb(241,244,255)">
                        <label for="sypmtomDate">
                            <strong>FECHA DEL PRIMER SÍNTOMA O ACCIDENTE</strong>
                            <span>(MM/DD/AAAA)</span>
                        </label>
                    </div>
                </div>
                <div class="input">
                    <input type="text" name="beforeInformation" id="beforeInformation" id="sypmtomDate" value="v" style="color:rgb(241,244,255)">
                    <label for="beforeInformation">TRATAMIENTO RECIBIDO E INFORMACIÓN DE ANTECEDENTES MÉDICOS ANTERIORES</label>
                    <input type="text" name="beforeInformation2" id="beforeInformation" value="v" id="sypmtomDate" style="color:rgb(241,244,255)">
                    <label for="beforeInformation2"></label>
                </div>
                <div class="pair-input">
                    <div class="input">
                        <input type="text" name="hospitalName" id="hospitalName" value="v" style="color:rgb(241,244,255)">
                        <label for="hospitalName">EN CASO DE HOSPITALIZACIÓN, POR FAVOR PROVEA EL NOMBRE DEL HOSPITAL</label>
                    </div>
                    <div class="input">
                        <input type="text" name="drName" id="drName" value="v" style="color:rgb(241,244,255)">
                        <label for="drName">NOMBRE DEL MÉDICO TRATANTE</label>
                    </div>
                </div>
                <div class="pair-input">
                    <div class="input">
                        <input type="text" name="addressSupplier" id="addressSupplier" value="v" style="color:rgb(241,244,255)">
                        <label for="addressSupplier">DIRECCIÓN DEL PROVEEDOR</label>
                    </div>
                    <div class="input">
                        <input type="text" name="phoneSupplier" id="phoneSupplier" value="v" style="color:rgb(241,244,255)">
                        <label for="phoneSupplier">TELÉFONO DEL PROVEEDOR</label>
                    </div>    
                </div>
            </div>
            <div class="group-signature">
                <div class="statement">
                    <strong>HA TENIDO SíNTOMAS SIMILARES CON ANTERIORIDAD?</strong>
                </div>
                <div class="input-radios">
                    <input type="radio" name="symptom" value="yes" style="width:20px"> 
                    <input type="radio" name="symptom" value="no" style="width:20px"> 
                    <div class="input">
                        <input type="text" name="date" id="date" style="color:rgb(241,244,255)">
                        <label for="date">
                            <strong>FECHA</strong><span>(MM/DD/AAAA)</span>
                        </label>
                    </div>
                </div>
                <div class="signature">
                    <strong>SELLO Y FIRMA DEL MÉDICO TRATANTE</strong>
                    <hr />
                </div>
            </div>
            <h3 class="head-input">DATOS SOBRE LOS SERVICIOS PRESTADOS</h3>
            <table>
                <thead>
                    <tr>
                        <th colspan="2">FECHAS DE SERVICIOS</th>
                        <th colspan="3"></th>
                    </tr>
                    <tr>
                        <th>FECHA</th>
                        <th>DETALLES SOBRE TRATAMIENTOS, ASISTENCIA PRESTADA E INSUMOS MÉDICOS</th>
                        <th>MONEDA</th>
                        <th>IMPORTE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $index => $invoice)
                    <tr>
                        <td>{{ $invoice['date_invoice'] }}</td>
                        <td>{{ $invoice['num_invoice'] }} {{ $invoice['concept'] }}</td>
                        <td>{{ $invoice['currency'] }}</td>
                        <td>{{ $invoice['amount'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td ></td>
                        <td>IMPORTE TOTAL</td>
                        <td></td>
                        <td>{{ $total }}</td>
                    </tr>
                    <tr>
                        <td ></td>
                        <td>IMPORTE PAGADO POR EL AFILIADO</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </div>
    </page>
</body>
</html>