<?php namespace Modules\Agente\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agente extends Model 
{
	protected $table='agente';

	//type determine if the agent is a 'agent' or a 'subagent'
	protected $fillable=['name',
                        'lastname',
                        'identity_document',
                        'dob',
                        'email',
						'skype',
                        'mobile',
                        'phone',
                        'country_id',
                        'province_id',
                        'city_id',
						'address',
                        'subagent',
                        'comision',
                        'leader'];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * Adjuntar accessors a toArray()
     * @var array
     */
    protected $appends = array('full_name');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

     /**
     * Function Model:subagentes
     * Description: Define the relationship one to many between an Agent and agents that are under him
     * Date created: 27-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompanyOffice
     */
    public function subagentes() 
    {
        return $this->hasMany('Modules\Agente\Entities\Agente','id','leader'); 
    }

    /**
     * Function Model:padre
     * Description: retrive an agent that is the leader of the current agent
     * Date created: 27-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompanyEmails
     */
    public function leaderRel() 
    {
        return $this->belongsTo('Modules\Agente\Entities\Agente','leader','id'); 
    } 

    public function getFullNameAttribute()
    {
        return $this->name . " " . $this->lastname;
    }

    public static function rellocateSubagents($idOldAgent,$idNewAgent)
    {
    	try{
    		\DB::table('agente')
				->where('leader', $idOldAgent)
				->update(['leader'=> $idNewAgent]);
    		return true;
    	}catch(Exception $e){
    		return false;
    	}
	}

    public function getDobAttribute()
    {
        $fecha = \DateTime::createFromFormat('Y-m-d', $this->attributes['dob']);
        return $fecha->format('d/m/Y');
    }

    public function setDobAttribute($value)
    {
        $this->attributes['dob'] = date('Y-m-d',strtotime($value));
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function setName($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function getLastName()
    {
        return $this->attributes['lastname'];
    }

    public function setLastName($value)
    {
        $this->attributes['lastname'] = strtoupper($value);
    }

    public function scopeFullName($query,$value){
        $value = strtoupper($value);
        return $query->whereRaw('CONCAT(name," ",lastname) LIKE ?',['%'.$value.'%']);
    }

    public function scopeIdentityDocument($query,$identityDocument){
        return $query->where('identity_document', $identityDocument);
    }
}
