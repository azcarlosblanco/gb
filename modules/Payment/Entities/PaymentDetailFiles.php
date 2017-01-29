<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;

class PaymentDetailFiles extends Model {

	protected $table = "payment_detail_files";
    protected $fillable = [
    					"table_type",
    					"table_id",
    					"file_entry_id",
    						];

    public function file(){
    	return $this->belongsTo("App\FileEntry","file_entry_id");
    }

}