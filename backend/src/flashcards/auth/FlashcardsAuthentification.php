<?php

require 'src/flashcards/model/Professeur.php';
require 'src/mf/auth/Authentification.php';


class FlashcardsAuthentification extends \mf\auth\Authentification {

    /* constructeur */
    public function __construct(){
        parent::__construct();
    }

    public function createUtilisateur($mdp, $mail, $nom, $prenom) 
    {  
        $requete = Professeur::select()->where('mail', '=', $mail);
        $unUtilisateur = $requete->first();
        if(!is_null($unUtilisateur)){
            return "Email déjà utilisé";
        }
        else{
            $u = new Professeur();
            $u->nom = $nom;
            $u->prenom = $prenom;
            $u->mail = $mail;
            $hash = $this->hashPassword($mdp);
            $u->mdp = $hash;
            $u->save();
        }
    }

    public function login($mail, $mdp)
    { 
        $requete = Professeur::select()->where('mail', '=', $mail);
        $unUtilisateur = $requete->first();
        if(is_null($unUtilisateur))
        {
            return "Utilisateur inconnu";
        }
        else{
            $requete = Professeur::select()
            ->where('mail', '=', $mail)
            ;
            $p = $requete->first();
            $check = $this->verifyPassword($mdp, $p->mdp);
              
            if($check == false){
                return "Mot de passe erroné !";
            }
            else{
                $this->updateSession($mail);
            }
        }
    }
    public function deconnexion(){
        $this->logout();
    }
}
