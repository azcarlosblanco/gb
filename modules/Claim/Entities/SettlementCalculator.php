<?php namespace Modules\Claim\Entities;

use Modules\Policy\Entities\Policy;
use Modules\Affiliate\Entities\AffiliatePolicyDeducible;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Plan\Entities\PlanDeducibleType;

class SettlementCalculator{

    protected $fillable = [];

    public static function updateDeductibleValuesBD(ClaimSettlement &$s, Policy $p){
      $top_total_vals = $top_unit_vals = $curr_vals = array();
  		$multiply = ( $p->planType->name == 'Individual' ) ? 1 : 2;
  		$file = $s->claimFile;
      $ap = $file->claim->affiliatePolicy;
      $deductibles = array();
      $deductibles_total = $p->getDeductiblesTotalsBD();

      foreach( $ap->deducibles as $i=>$deduct ){
        $deductibles[$deduct->plan_deducible_type_id] = $deduct->amount;
      }

  		//policy ref deducibles
  		$ref_deducibles = $total_deduc = $single_deduct = array();
  		$policy_deducibles = $p->deducibles;
  		foreach( $policy_deducibles as $pd ){
  			$top_total_vals[$pd->plan_deducible_type_id] = $multiply * $pd->amount;
  			$top_unit_vals[$pd->plan_deducible_type_id] = $pd->amount;
  		}

  		$local_type = PlanDeducibleType::where('name', 'local')->firstOrFail();
  		$inter_type = PlanDeducibleType::where('name', 'usa')->firstOrFail();
      $pd_type = ($file->usa) ? $inter_type : $local_type;

  		$amount = $s->amount - ( $s->uncovered_value + $s->descuento + $s->coaseguro );
  		$dsct_concept = $s->amount * $file->getConcept->deduct_discount / 100;
  		$real_amount = $amount - $dsct_concept;

  		$go = true;
  		//check if personal deductible is complete
  		if( $go ){
        if( $file->usa ){
          $max_deduct = $top_unit_vals[$inter_type->id];
    			$curr_deduct = $deductibles[$inter_type->id] + $deductibles[$local_type->id];
        }
        else{
          $max_deduct = $top_unit_vals[$local_type->id];
    			$curr_deduct = $deductibles[$local_type->id];
        }

  			//calculate vals
  			$res = $max_deduct - ($curr_deduct + $real_amount);

  			if( $res < 0 ){
  				$to_deduct = $max_deduct - $curr_deduct;
  				$to_refund = $real_amount - $to_deduct;
  				$go = false;
  			}
  		}

  		//else check for familiar deductible
  		if( $go ){
        if( $file->usa ){
          $max_deduct = $top_total_vals[$inter_type->id];
    			$curr_deduct = $deductibles_total[$inter_type->id] + $deductibles_total[$local_type->id];
        }
        else{
          $max_deduct = $top_total_vals[$local_type->id];
    			$curr_deduct = $deductibles_total[$local_type->id];
        }

  			//calculate vals
  			$res = $max_deduct - ($curr_deduct + $real_amount);

  			if( $res < 0 ){
  				$to_deduct = $max_deduct - $curr_deduct;
  				$to_refund = $real_amount - $to_deduct;
  				$go = false;
  			}
  		}//end familiar deductible check

  		//send all to deductible
  		if( $go ){
  			$to_deduct = $real_amount;
  			$to_refund =  0;
  		}

  		//save expected values for settlement
      $s->expected_deduct = $to_deduct;
      $s->expected_refund = $to_refund;
      $s->save();

      //update affiliate deductible
      if( $to_deduct > 0 ){
        $apd_obj = $ap->deducibles()->where('plan_deducible_type_id', $pd_type->id)->first();
        if( !empty($apd_obj) ){
          //update affiliate policy deductible
          $apd_obj->amount = $apd_obj->amount + $to_deduct;
          $apd_obj->save();
        }
      }

    }//end function updateDeductibleValuesBD

}
