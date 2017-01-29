<?php namespace Modules\Menu\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Modules\Menu\Entities\MenuAction;
use Modules\Menu\Entities\Module;
use Modules\Authorization\Entities\Role;
use App\User;
use JWTAuth;

class Menu extends Model {

	protected $table='menu';
    protected $fillable = [
    					   'display_name',
    					   'is_parent',
    					   'level',
                           'order',
    					   'link',
    					   'icon',
    					   'parent_id',
    					   'module_code',
                           "display"
    					   ];

    public function permissions(){
        return $this->bellongsToMany("Modules\Authorization\Entities\Permission",
                                        "menu_permission",
                                        "menu_id",
                                        "permission_id");
    }

    public function module(){
        return $this->belongsTo("Modules\Menu\Entities\Module","module_code","code");
    }

    public static function getArrayMenu(){
    	//get user roles
    	$user = JWTAuth::parseToken()->authenticate();

        $roles=$user->roles()
                            ->with('permissions.menus')
                            ->get();

        //TODO: get only modules that are authorized for the company
        //those modules should be retrived from ldap
        //TODO: ORDER MODULES BY ORDER - ADD ORDER FIELD TO MODULES
        $modules = Self::getListCompanyAuthorizabelMenu();

        //list of menu the user have access
        $menus_availables = array();
        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                foreach ($permission->menus as $menu) {
                    if($menu->display==1){
                        //add menus in order
                        $menus_availables[$menu->module_code][$menu->id]=$menu;
                    }
                }
            }
        }

        //create the menu using th availables menus and the avialables modules
        //TODO: WHAT TO DO WITH MENU OF MORE THAN 1 LEVEL
        //TODO: ORDER MENU BY ORDER FIELD
        $menu_list = array();
        foreach ($modules as $key => $module) {
            if(isset($menus_availables[$module->code])){
                $menu_list['sections'][$key]=array();
                $menu_list['sections'][$key]['label'] = $module->display_name;
                $menu_groups = array();
                $index = 0;
                foreach ($menus_availables[$module->code] as $menuId => $menu) {
                    $menu_groups[$index]['label'] = $menu->display_name;
                    $menu_groups[$index]['notifications'] = 0;
                    $menu_groups[$index]['icon'] = $menu->icon;
                    $menu_groups[$index]['url'] = $menu->link;
                    $index++;
                }
                $menu_list['sections'][$key]['groups'] = $menu_groups; 
            }
            
        }
        return $menu_list;

        //get menus and modules from menu_actions
        /*$menus = array();
        foreach ($menuActions as $key => $value) {
            foreach ($variable as $key => $value) {
                # code...
            }
        }*/

    	//obtener menus por roles
    	
    	//Primero los de primer nivel
    	/*$listAuthMenus=MenuRole::whereIn('role_id',$listRoles)
    							->lists('menu_id');

		$moduleIDs=Menu::whereIn('id',$listAuthMenus)
							->distinct('module_id')
							->lists('module_id');

    	$module=Module::whereIn('id',$moduleIDs)
    						->pluck('display_name','id');

		$menu_display=[];
    	$index=0;
    	foreach ($moduleIDs as $moduleID) {
    		$menu_display[$index]['label']=$module[$moduleID];
			//menus del primer nivel del modulo
    		$menus1=Menu::whereIn('id',$listAuthMenus)
						->where('level',1)
						->where('module_id',$moduleID)
						->orderBy('order')
						->get();

    		foreach ($menus1 as $menu) {
    			$group=array();
    			$group['label']=$menu->display_name;
				$group['icon']=$menu->icon;
				if($menu->is_parent){
					//menu segundo nivel
					$menus2=Menu::whereIn('id',$listAuthMenus)
									->where('level',2)
									->where('parent_id',$menu->id)
									->orderBy('order')
									->get();
					//recorremos los menus del segundo nivel del menu
					foreach ($menus2 as $menu2) {
						$subMenu['label']=$menu2->display_name;
						$subMenu['url']=$menu2->link;
						$subMenu['notifications']=0;
                        $group['links'][]=$subMenu;
					}
				}else{
					$group['url']=$menu->link;
				}
    			$menu_display[$index]['groups'][]=$group;
    		}
    		$index++;
    	}*/
    }

    public static function getListCompanyAuthorizabelMenu(){
        //should try a list of medules code from ldap and retrive only modules whose codes
        //are on that list
        return Module::where("display",1)->orderBy('module_order')->get();
    }
}