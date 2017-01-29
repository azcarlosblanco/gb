<?php namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateRole extends Model {

    protected $table = 'affiliate_role';
    protected $fillable = [];

    public static function isSpouse($id){
      $aff_role = self::where('id', $id)
                      ->where('name', 'esposo(a)')
                      ->value('id');
      return (empty($aff_role)) ? false : true;
    }

}
