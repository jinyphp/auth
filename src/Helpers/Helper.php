<?php
if(!function_exists("authRoles")) {
    function authRoles($id)
    {
        return new \Jiny\Auth\Roles($id);
    }
}
