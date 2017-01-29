<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

class User extends Authenticatable {

    protected $table = 'user';
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','lastname','email', 'api_token', 'password'
    ];


    protected $appends = array('full_name');

    public function roles()
    {
        return $this->belongsToMany('Modules\Authorization\Entities\Role',
                                        'role_user',
                                        'user_id',
                                        'role_id');
    }

    public function employee()
    {
        return $this->hasOne('Modules\Employee\Entities\Employee',
                                        'user_id');
    }

    public function client()
    {
        return $this->hasOne('Modules\Client\Entities\Client',
                                        'client_id');
    }

    public function ticket(){
        return $this->hasOne('Modules\ClientService\Entities\Ticket',
                                'responsible_id'
                                );
    }


    public function ticketDetail(){
        return $this->hasOne('Modules\ClientService\Entities\TicketDetail',
                                'user_id'
                                );
    }

    public function getFullNameAttribute(){
        return $this->name." ".$this->lastname;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required',
            'lastname' => 'lastname',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public static function createUser(array $data)
    {
        $user=User::create(
                array(
                    'name'       => $data['name'],
                    'lastname'   => $data['lastname'],
                    'email'      => $data['email'],
                    'password'   => \Hash::make($data['password'])
                )
            );

        //attach role to the user
        $user->roles()->sync($data['roles']);

        return $user;
    }

    public static function createUserWithRandomPssw(array $data)
    {
        $pssw=User::generatePssw();
        return User::create([
            'name'       => $data['name'],
            'lastname'   => $data['lastname'],
            'email'      => $data['email'],
            'password'   => \Hash::make($pssw),
        ]);
    }

    public static function generatePssw($length=6){
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = random_int(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public static function validatePassword($password){
        $exp = "/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/";
        if(preg_match($exp, $password)){
            return true;
        }else{
            return false;
        }
    }

    public function updatePassword($password){
        if(!self::validatePassword($password)){
            throw new \Exception("Contraseña es inválida");
        }

        $user->password = \Hash::make($password);
        $user->save();

        //TODO: ban valid tokens for the user whose password was changed

    }
}