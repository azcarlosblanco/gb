<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionButton extends Model
{
	protected $table = 'action_button';

	protected $fillable = [
							'icon',
							'link',
							'process_catalog_id',
						  ];

	/*
     * Return the Procedure Objects that are associated with the process
     * Date created: 03-05-2016
     * Cretated by : Rocio Mera S
     *               [rociom][@][novatechnology.com.ec]
     */
    public function processCatalog(){
        return $this->belongsTo('App\ProcessCatalog','process_catalog_id','id');
    }

}