<?php
namespace Jiny\Auth;

use Illuminate\Support\Facades\DB;

class Roles
{
    public $relations;
    public $roles;

    public function __construct($id)
    {
        $this->relations = DB::table("role_user")->where('user_id', $id)->get();

        $ids = [];
        foreach($this->relations as $item) {
            $ids []= $item->role_id;
        }

        $this->roles = DB::table("roles")->whereIn('id', $ids)->get();
    }

    public function get()
    {
        return $this->roles;
    }

    public function is($roles)
    {
        foreach($this->roles as $item) {
            if(is_string($roles)) {
                if($item->name == $roles) return true;
            } else if(is_object($roles)) {
                foreach($roles as $role) {
                    if($item->name == $role->name) return true;
                }
            } else if(is_array($roles)) {
                foreach($roles as $role) {
                    if($item->name == $role['name']) return true;
                }
            }
        }

        return false;
    }

    public function permitAll($actions)
    {
        $permit = [
            'create' => false,
            'read' => false,
            'update' => false,
            'delete' => false,
        ];

        if(isset($actions['role']) && $actions['role']) {
            $my_roles = $this->myRole($actions);
            foreach($my_roles as $my) {
                foreach($my as $key => $val) {
                    if ($val) $permit[$key] = $val;
                }
            }
        }

        return $permit;
    }

    public function permit($actions, $type)
    {
        if(isset($actions['role']) && $actions['role']) {

            $my_roles = $this->myRole($actions);
            foreach($my_roles as $my) {
                // 권환 허용
                if(isset($my[$type]) && $my[$type]) {
                    return true;
                }
            }

            // 권한 실패
            return false;
        }

        // 권한 미적용시
        return true;
    }

    private function myRole($actions)
    {
        $my_roles = [];
        foreach($this->roles as $role) {
            $name = $role->name;
            // 일치된 권한 정보가 있는지...
            if(isset($actions['roles'][$name]) &&
                is_array($actions['roles'][$name])) {

                // 권한이 허용되어 있는지...
                if(isset($actions['roles'][$name]['permit']) &&
                    $actions['roles'][$name]['permit']) {
                    $my_roles []= $actions['roles'][$name];
                }

            }
        }

        return $my_roles;
    }

}
