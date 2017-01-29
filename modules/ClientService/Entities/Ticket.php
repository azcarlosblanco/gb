<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Policy\Entities\Policy;
use Modules\ClientService\Entities\TicketCat;
use Modules\ClientService\Entities\TicketDetail;
use Validator;
use Carbon\Carbon;
use JWTAuth;
use Modules\Authorization\Entities\Role;


class Ticket extends Model {

    protected $table="ticket";

    protected $fillable = [ 'table_type', 
    						'table_id',
    						'ticket_cat_id',
    						'policy_id',
    						'start_date',
    						'end_date',
    						'short_desc',
    						'responsible_id',
    						];

     use SoftDeletes;

     public function ticket_cat(){
		return $this->belongsTo('Modules\ClientService\Entities\TicketCat',
										'ticket_cat_id',
										'id');
	  }

	public function policy(){
		return $this->belongsTo('Modules\Policy\Entities\Policy',
										'policy_id'
										);
      }
	public function responsible(){
		return $this->belongsTo('App\User',
								'responsible_id',
	 							'id');
      }
	public function ticket_detail(){
		return $this->hasMany('Modules\ClientService\Entities\TicketDetail',
								'ticket_id');
     
	  }

    public function emergency(){
        return $this->hasOne('Modules\ClientService\Entities\Emergency',
                             'ticket_id',
                             'id');
     }

     public function hospitalization(){
        return $this->hasOne('Modules\ClientService\Entities\Hospitalization',
                             'ticket_id',
                             'id');
     }
    

    //Services

	// 
    public function validatecreation(array $data){

        $code  = null;
        $rules = array(
                        "policy_id"         => "required",
                        "short_desc"        => "required",
                      );

        $vresult = Validator::make($data,$rules,array());
            if($vresult->fails()){
            	$code = 422;
                 throw new \Exception("Falta ingresar datos",422);
            }
    }

    //
    public function getresponsible(){
    	try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            $user = null;
        }
        if($user==null){
            $user=Role::where("name","administracion")
                                ->first()
                                ->users()
                                ->first();
        }

        $current=false;
        $department = 1;
  		$tickeCatList=TicketCat::pluck('name','id');
    	if($tickeCatList[$this->ticket_cat_id]=="claim" || 
    		$tickeCatList[$this->ticket_cat_id]=="settlement"){
    		$department = Role::where('name','claims')->first()->id;
    	}elseif($tickeCatList[$this->ticket_cat_id]=="emission"){
    		$department = Role::where('name','emision')->first()->id;
    	}elseif($tickeCatList[$this->ticket_cat_id]=="general"){
    		$current=true;
    	}

    	if($current){
            return $user->id;
        }else{
            $users=Role::find($department)->users->toArray();
	        if(count($users)==0){
	            return $user->id;
	        }else{
	            $index=rand(0,count($users)-1);
	            return $users[$index]['id'];
	        }
        }
    }
    
    //
    public function creationticket(array $data){
    	try {
            $this->validatecreation($data);

	        $policy=Policy::find($data['policy_id']);
         	if($policy==null){
                $code=422;
                throw new \Exception("La poliza no existe",422);
            }

            $category=TicketCat::find($data['type_ticket']);
     	    if($category==null){
             	$code=422;
             	throw new \Exception("La categoria no existe",422);
            }
       
         	$date = Carbon::now();
            //$date = date("Y-m-d H:i:s");
            //$enddate = $date->addYear();

            $this->table_type               =$data['table_type'];
            $this->table_id                 =$data['table_id'];
            $this->ticket_cat_id            =$data['type_ticket'];
            $this->policy_id                =$data['policy_id'];
            $this->table_id                 =$data['table_id'];
            $this->start_date               =$date;
            $this->end_date                 =null;
            $this->short_desc               =$data['short_desc'];

            $responsible= $this->getresponsible();
            $this->responsible_id           =$responsible;
             
            $this->save();
            $code=200;
		}catch(\Exception $e){
    	   throw new \Exception($e->getMessage(),$e->getCode());	
		}
	}

	      
}

