<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JWTAuth;

class ClaimSettlement extends Model {

    protected $table = 'claim_settlement';
    protected $fillable = [
      'claim_file_id',
      'amount',
      'uncovered_value',
      'descuento',
      'deducible',
      'coaseguro',
      'refunded',
      'notes',
      'serv_date',
      'status',
      'expected_deduct',
      'expected_refund',
      'ic_num_claim'
    ];
    use SoftDeletes;

    public function claimFile(){
    	return $this->belongsTo('Modules\Claim\Entities\ClaimFile','claim_file_id','id');
    }

    public function files(){
      return $this->belongsToMany('App\FileEntry', 'settlement_file', 'settlement_id', 'file_entry_id');
    }

    public function registerAmount($val){

    }

    public function refunds(){
      return $this->hasMany('Modules\Claim\Entities\ClaimSettlementRefund', 'claim_settlement_id', 'id');
    }

    public function calculateRefunded(){
      $total_refunded = 0;

			foreach( $this->refunds as $refund ){
				$total_refunded += $refund->value;
			}

      return $total_refunded;
    }

}
