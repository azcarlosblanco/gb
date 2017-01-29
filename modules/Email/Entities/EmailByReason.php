<?php namespace Modules\Email\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailByReason extends Model
{
	protected $table = 'email_by_reason';

	protected $fillable = [
							'reason',
							'sender',
							'subject',
							'template',
							'template_html',
							'company_id'
						  ];

	public function scopeReason($query,$reason){
		return $query->where('reason',$reason);
	}

	public function renderTemplate($param){
		//this function must retun an html format of email
		$template=$this->template;
		foreach ($param as $key => $value) {
			$template=str_replace($key, $value, $template);
		}
		return $template;
	}

	/*
	 * Pass as parameter the varibles in the subject that want to be replaced
	 */
	public function renderSubject($param){
		//this function must retun an html format of email
		$subject=$this->subject;
		foreach ($param as $key => $value) {
			$subject=str_replace($key, $value, $subject);
		}
		return $subject;
	}

}