<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JWTAuth;

class Claim extends Model {
  protected $table='claim';

  protected $fillable = ['affiliate_policy_id', 'status', 'diagnosis_id'];
  use SoftDeletes;

  /*public function procedures()
  {
      return $this->belongsToMany('App\ProcedureEntry','claim_id','procedure_entry_id');
  }*/

  public function affiliatePolicy(){
    return $this->belongsTo('Modules\Affiliate\Entities\AffiliatePolicy','affiliate_policy_id','id');
  }

  public function files(){
    return $this->hasMany('Modules\Claim\Entities\ClaimFile','claim_id','id');
  }

  public function readableSummary(){
    try{
      $data['id'] = $this->id;
      $data['affiliate'] = $this->affiliatePolicy->affiliate->full_name;
      $files = $this->files;

      if( is_null($files) ){
        throw new \Exception('no files');
      }

      $total = 0;
      $list = array();
      foreach( $files as $file ){
        $tmp = array();
        $tmp['filename'] = $file->fileEntry->original_filename;
        $tmp['description'] = $file->description;
        $tmp['currency'] = (!is_null($file->currency)) ? $file->currency->display_name : '';
        $tmp['amount'] = $file->amount;
        $tmp['concept'] = (!is_null($file->getConcept)) ? $file->getConcept->display_name : '';
        $tmp['type_name'] = \App\ProcedureDocument::find($file->procedure_document_id)->description;

        $list[] = $tmp;

        $amount = floatval($tmp['amount']);
        if( $amount > 0 ){
          $total += $amount;
        }
      }

      $claimprocedure = $this->claimProcedures()->first();
      //add info about files that have errors
      $fes = \App\FileEntry::where("table_type","procedure_entry")
                              ->where("table_id",$claimprocedure->procedure_entry_id)
                              ->get();

      $listInvalid = array();
      foreach( $fes as $file ){
        $fdata = (array)json_decode($file->data);
        if(isset($fdata['valid'])){
          if($fdata['valid']==0){
            $tmp = array();
            $tmp['filename'] = $file->original_filename;
            $tmp['type_name'] = \App\ProcedureDocument::find($fdata['procedure_document_id'])->description;
            $tmp['description'] = $file->description;
            $tmp['amount'] = (isset($fdata['amount'])) ? $fdata['amount']: 0;
            $listInvalid[] = $tmp;
          }
        }
      }

      $data['files'] = $list;
      $data['invalidfiles'] = $listInvalid;
      $data['total'] = $total;
    }catch( \Exception $e ){
      throw $e;
    }
    return $data;
  }

  //get the list of all files that were associated with the claim
  public function getClaimFiles(){
      $total = 0;
      $list = array();

      $files = $this->files;
      if( is_null($files) ){
        throw new \Exception('no files');
      } 

      foreach( $files as $file ){
        $tmp = array();
        $tmp['filename'] = $file->fileEntry->id;
        $tmp['filename'] = $file->fileEntry->original_filename;
        $tmp['description'] = $file->description;
        $tmp['currency'] = (!is_null($file->currency)) ? $file->currency->display_name : '';
        $tmp['amount'] = $file->amount;
        $tmp['concept'] = (!is_null($file->getConcept)) ? $file->getConcept->display_name : '';
        $tmp['category'] = \App\ProcedureDocument::find($file->procedure_document_id)->description;
        $list[] = $tmp;

        $amount = floatval($tmp['amount']);
        if( $amount > 0 ){
          $total += $amount;
        }
      }

      //get file uploaded in the settelement process
      
  }

  public function claimProcedures(){
    return $this->hasMany('Modules\Claim\Entities\ClaimProcedure','claim_id');
  }

  public function hasActiveSettlement($return_obj=false){
    $claim_procedures = $this->claimProcedures;

    if( is_null($claim_procedures) ){
      return false;
    }

    $catalog_id = \App\ProcedureCatalog::where('name', 'settlement')->value('id');
    if( empty($catalog_id) ){
      return false;
    }

    foreach($claim_procedures as $cp ){
      //search for 1 claim procedure with a procedure type 'settlement'
      $procedure = $cp->procedureEntry;
      if( ($procedure->procedure_catalog_id == $catalog_id) && $procedure->isActive() ){
        return ( $return_obj ) ? $procedure : true;
      }
    }

    return false;
  }

  public function diagnosis(){
    return $this->belongsTo('App\Diagnosis','diagnosis_id','id');
  }


}
