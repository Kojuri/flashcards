<?php
namespace mf\auth;
require 'src/mf/auth/AbstractAuthentification.php';
class Authentification extends AbstractAuthentification{

	public function __construct(){
		if(isset($_SESSION['mail'])){
			$this->user_login=$_SESSION['mail'];
			$this->logged_in=true;
		}else{
			$this->user_login=null;
			$this->logged_in=false;
		}
	}

	public function updateSession($mail){
		$this->user_login=$mail;
		$_SESSION['mail']=$mail;
		$this->logged_in=true;
	}

	public function logout(){
		$_SESSION['mail']=null;
		$this->logged_in=false;
	}

	protected function hashPassword($password){
		return password_hash($password, PASSWORD_DEFAULT);
	}

	protected function verifyPassword($password, $hash){
		return password_verify($password, $hash);
	}
}
