<?php namespace Modules\Email\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Email\Entities\EmailByReason;

class EmailDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		//emission
		$data['reason']='requestPolicyInsuranceCompany';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Solicitud <CUSTOMER>';
		$data['template']='Favor procesar la solicitud adjunta, vigencia <EFFECTIVE_DATE>.';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);

		$data['reason']='sendProspectPolicyAgent';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Confirmacion Poliza: <CUSTOMER>';
		$data['template']='Estimado agente le adjunto los datos de la nueva póliza del cliente <CUSTOMER>, por favor indicar si el cliente desea la póliza para continuar con el pago y probación de la misma.';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);

		$data['reason']='sendPaymentData';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Forma de Pago: <CUSTOMER> <POLICY_NUMBER>';
		$data['template']='Estimado,
		El cliente <CUSTOMER> de la póliza con ID <POLICY_NUMBER> nos informa que va a pagar mediante <PAYMENT_METHOD> en forma <NUMBER_PAYMENTS>';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);

		$data['reason']='sendInvoiceAgent';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Factura: <CUSTOMER> <POLICY_NUMBER>';
		$data['template']='Estimado Agente,
		Le adjunto la factura de la póliza para ser enviada al cliente.';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);


		$data['reason']='notifyAgentPolicyIsReady';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Poliza lista: <CUSTOMER>';
		$data['template']='Estimado <AGENT_NAME>
		El presente correo es para informarle que la póliza del cliente <CUSTOMER> del plan <PLAN> / <DEDUCIBLE> está lista para ser retirada. ';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);

		$data['reason']='notifySentPolicyIC';
		$data['sender']='emission@gb.com';
		$data['subject']='[<TRAMITE_ID>] Poliza Firmada: <CUSTOMER> <POLICY_NUMBER>';
		$data['template']='Estimado,
		La póliza firmada del cliente <CUSTOMER> del plan <PLAN> / <DEDUCIBLE> ha sido enviada. Por favor notificar cuando reciba los papeles. ';
		$data['template_html']='emission';
		$email=EmailByReason::create($data);

		$data['reason']='notifyClaimSent';
		$data['sender']='reclamos@gb.com';
		$data['subject']='[<TRAMITE_ID>] Reclamo enviado: <CUSTOMER> <POLICY_NUMBER>';
		$data['template']="Estimado,\n\nResumen de reclamos:\n\nPOLIZA #: <POLICY_NUMBER>\nTITULAR: <CUSTOMER>\nPLAN: <PLAN> / <DEDUCIBLE>\n\nDetalle de reclamos por afiliado:\n\n<DETAILS>";
		$data['template_html']='reclamo';
		$email=EmailByReason::create($data);

		$data['reason']='notifyFinishedSettlement';
		$data['sender']='reclamos@gb.com';
		$data['subject']='Liquidacion Finalizada #<TRAMITE_ID>';
		$data['template']="Estimado <USER>, a continuacion se le presenta el monto liquidado. \n\n Monto: <AMOUNT>";
		$data['template_html']='reclamo';
		$email=EmailByReason::create($data);

		Model::reguard();
	}

}
