<?php
namespace App;

use Modules\Reception\Entities\ProcessInitialDocumentation;
use Modules\Emission\Entities\ProcessUploadPolicyRequest;
use Modules\Reception\Entities\ProcessSendCheckIC;
use Modules\Emission\Entities\ProcessChangeEffectiveDate;
use Modules\Emission\Entities\ProcessRequestAppNewPolicyBD;
use Modules\Reception\Entities\ProcessReceivePolicyBD;
use Modules\Emission\Entities\ProcessReviewProspectPolicy;
use Modules\Emission\Entities\ProcessRegisterCustomerResponse;
use Modules\Emission\Entities\ProcessRegisterInvoice;
use Modules\Emission\Entities\ProcessRegisterCustomerPayment;
use Modules\Emission\Entities\ProcessSendDocsReception;
use Modules\Reception\Entities\ProcessSendPolicyCustomer;
use Modules\Reception\Entities\ProcessUploadSignedPolicy;
use Modules\Reception\Entities\ProcessSendDocumentsBD;
use Modules\Reception\Entities\ProcessUploadReceipt;

//claims
use Modules\Reception\Entities\ProcessClaimsInit;
use Modules\Claim\Entities\ProcessClaimsReviewDocuments;
use Modules\Claim\Entities\ProcessClaimsPrintLetter;
use Modules\Reception\Entities\ProcessClaimsSendDocsBD;
use Modules\Reception\Entities\ProcessClaimsReceiveReceipt;
use Modules\Reception\Entities\ProcessSettlementInit;
use Modules\Reception\Entities\ProcessSettlementUploadFiles;
use Modules\Claim\Entities\ProcessSettlementRegister;
use Modules\Claim\Entities\ProcessSettlementRefund;
use Modules\Claim\Entities\ProcessSettlementFinish;

//clientService
use Modules\ClientService\Entities\ProcessCSInputData;
use Modules\ClientService\Entities\ProcessCSWarrantyLetter;
use Modules\ClientService\Entities\ProcessInputDataHospitalizacion;
use Modules\ClientService\Entities\ProcessWarrantyLetterHospitalization;

class ProcessFactory {

	public static function createProcess($nameProcess){
        $process=null;
        switch ($nameProcess) {
            case 'InitialDocumentation': //reception
                $process = new ProcessInitialDocumentation();
                break;
            case 'SendCheckIC': //reception
                $process = new ProcessSendCheckIC();
                break;
            case 'UploadPolicyRequest': //emission
                $process = new ProcessUploadPolicyRequest();
                break;
            case 'ChangeEffectiveDate': //emission
                $process = new ProcessChangeEffectiveDate();
                break;
            case 'RequestAppNewPolicyBD': //emission
                $process = new ProcessRequestAppNewPolicyBD();
                break;
            case 'ReceivePolicyBD': //reception
                $process = new ProcessReceivePolicyBD();
                break;
            case 'ReviewProspectPolicy': //emission
                $process = new ProcessReviewProspectPolicy();
                break;
            case 'RegisterCustomerResponse': //emission
                $process = new ProcessRegisterCustomerResponse();
                break;
            case 'ResgisterCustomerPayment': //emission
                $process = new ProcessRegisterCustomerPayment();
                break;
            case 'RegisterInvoice': //emission
                $process = new ProcessRegisterInvoice();
                break;
            case 'SendDocsReception': //emission
                $process = new ProcessSendDocsReception();
                break;
            case 'SendPolicyCustomer': //reception
                $process = new ProcessSendPolicyCustomer();
                break;
            case 'UploadSignedPolicy': //reception
                $process = new ProcessUploadSignedPolicy();
                break;
            case 'SendDocumentsBD': //reception
                $process = new ProcessSendDocumentsBD();
                break;
            case 'UploadReceipt': //reception
                $process = new ProcessUploadReceipt();
                break;

            //claims
            case 'ClaimsInit': //reception
                $process = new ProcessClaimsInit();
                break;
            case 'ClaimsReviewDocuments': //claim
                $process = new ProcessClaimsReviewDocuments();
                break;
            case 'ClaimsPrintLetter': //reception
                $process = new ProcessClaimsPrintLetter();
                break;
            case 'ClaimsSendDocsBD': //reception
                $process = new ProcessClaimsSendDocsBD();
                break;
            case 'ClaimsReceiveReceipt': //reception
                $process = new ProcessClaimsReceiveReceipt();
                break;
            case 'SettlementInit': //reception
                $process = new ProcessSettlementInit();
                break;
            case 'SettlementUploadFiles': //reception
                $process = new ProcessSettlementUploadFiles();
                break;
            case 'SettlementRegister': //claim
                $process = new ProcessSettlementRegister();
                break;
            case 'SettlementRefund': //claim
                $process = new ProcessSettlementRefund();
                break;
            case 'SettlementFinish': //claim
                $process = new ProcessSettlementFinish();
                break;

            //client service
            case 'CSInputData': //clientService
                $process = new ProcessCSInputData();
                break;
            case 'CSWarratyLetter': //clientService
                $process = new ProcessCSWarrantyLetter();
                break;                
            case 'InputDataHospitalizacion': //Hospitalization
                 $process = new ProcessInputDataHospitalizacion();
                 break;
            case 'WarrantyLetterHospitalization': //Hospitalization
                  $process = new ProcessWarrantyLetterHospitalization();
                  break;

        }
        return $process;
    }
}
