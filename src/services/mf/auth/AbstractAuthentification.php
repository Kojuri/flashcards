<?php

namespace App\services\mf\auth;

abstract class AbstractAuthentification {

    /* le login de l'utilisateur connectÃ© */ 
    protected $user_login   = null;

    /* vrai s'il est connectÃ© */
    protected $logged_in    = false;


    /* un getter et un setter + toString*/
    public function __get($attr_name) {
        if (property_exists( __CLASS__, $attr_name))
            return $this->$attr_name;
        $emess = __CLASS__ . ": unknown member $attr_name (__get)";
        throw new \Exception($emess);
    }
    
    public function __set($attr_name, $attr_val) {
        if (property_exists( __CLASS__, $attr_name)) 
            $this->$attr_name=$attr_val; 
        else{
            $emess = __CLASS__ . ": unknown member $attr_name (__set)";
            throw new \Exception($emess);
        }
    }

    public function __toString(){
        return json_encode(get_object_vars($this));
    } 
    
    abstract public function updateSession($mail);
    
    abstract public function logout();
    
    abstract protected function hashPassword($password);
    
    abstract protected function verifyPassword($password, $hash);

    
    
}
