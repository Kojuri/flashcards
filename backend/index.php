<?php

require __DIR__.'/../vendor/autoload.php';

use App\models\Carte;
use App\models\Collection;
use App\models\Professeur;
use App\services\auth\FlashcardsAuthentification;
use App\handlers\Handler;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

session_start();

$config = include(__DIR__.'/../src/config.php');
$app = new \Slim\App(['settings'=> $config]);
$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$capsule->getContainer()->singleton(
  Illuminate\Contracts\Debug\ExceptionHandler::class,
  App\Exceptions\Handler::class
);

// Register component on container

//Image files upload directory base url

$container['public_url'] = 'http://web.flashcards.local:10085';
$container['public_path'] = __DIR__.'/../web';

//Flash messages

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};


$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/../src/views', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $root = dirname($_SERVER['SCRIPT_NAME'],1);
    $view->getEnvironment()->addGlobal("root", $root);
    $view->getEnvironment()->addGlobal("public_url", $container['public_url']);
    
    // Variables globales twig avec le mail et le pseudo de l'utilisateur connecté
    if(isset($_SESSION['mail'])){
        $prof = Professeur::select()->where('mail', '=', $_SESSION['mail'])->first();
        $view->getEnvironment()->addGlobal("mail", $prof->mail);
        $view->getEnvironment()->addGlobal("nom", $prof->nom);
        $view->getEnvironment()->addGlobal("prenom", $prof->prenom);
    }

    //Adding flash messages to Twig
    $view->addExtension(new \Knlv\Slim\Views\TwigMessages(
        $container['flash']
    ));

    return $view;
};

//Controllers

$container['CollectionController'] = function($c){
    return new App\controllers\CollectionController($c);
};

$container['GameController'] = function($c){
    return new App\controllers\GameController($c);
};

//Default route

$app->get('/', function ($request, $response, $args) use ($app) {
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'accueil.html');
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
    
    header("Location: ".$this->router->pathFor('connexion'));
    exit();

})->setName('get_default');

// Document root de l'application utilisé pour les redirections
$app->root = dirname($_SERVER['SCRIPT_NAME'],1);

// Route affichant le formulaire d'inscription
$app->get('/inscription[/]', function ($request, $response, $args) {
    return $this->view->render($response, 'inscription.html', $args);
})->setName('get_register_page');

// Route validant l'inscription
$app->post('/register[/]', function($request, $response, $args) use ($app){
    $data = $request->getParsedBody();
    if(!empty($data['nom']) and !empty($data['prenom']) and !empty($data['mail']) and !empty($data['mdp'])and !empty($data['remdp']))
    {
        $mail = filter_var($data['mail'], FILTER_SANITIZE_SPECIAL_CHARS);
        $nom = filter_var($data['nom'], FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_var($data['prenom'], FILTER_SANITIZE_SPECIAL_CHARS);
        $mdp = filter_var($data['mdp'], FILTER_SANITIZE_SPECIAL_CHARS);
        $remdp = filter_var($data['remdp'], FILTER_SANITIZE_SPECIAL_CHARS);
        $auth = new FlashcardsAuthentification();
        if($mdp == $remdp)
        {
            $co = $auth->createUtilisateur($mdp, $mail, $nom, $prenom);
            if(empty($co))
            {                
               header("Location: ".$this->router->pathFor('connexion'));
               exit();
            }
            else
            {
               return $this->view->render($response, 'inscription.html', [
                    'error' => $co
                ]);
            }
        }
        else 
        {
            return $this->view->render($response, 'inscription.html', [
                'error' => 'Les deux mots de passe ne sont pas identiques !'
            ]);
        }
    }
    else{
        return $this->view->render($response, 'inscription.html', [
            'error' => 'Veuillez remplir tous les champs !'
        ]);
    }
})->setName('register_user');

// Route affichant le formulaire de connexion
$app->get('/connexion[/]', function ($request, $response, $args) {
    return $this->view->render($response, 'connexion.html', $args);
})->setName('connexion');

// Route validant la connexion
$app->post('/login[/]', function($request, $response, $args) use ($app){
    $data = $request->getParsedBody();
    if(!empty($data['mail']) and !empty($data['mdp']))
    {
        $mail = filter_var($data['mail'], FILTER_SANITIZE_SPECIAL_CHARS);
        $mdp = filter_var($data['mdp'], FILTER_SANITIZE_SPECIAL_CHARS);
        $auth = new FlashcardsAuthentification();
        $co = $auth->login($mail, $mdp);
        if(empty($co))
        {
            header("Location: ".$this->router->pathFor('accueil'));
            exit();
        }
        else
        {
            return $this->view->render($response, 'connexion.html', [
                'error' => $co
            ]);
        }
    }
    else{
        return $this->view->render($response, 'connexion.html', [
            'error' => 'Veuillez remplir tous les champs !'
        ]);
    }
})->setName('login');

// Route affichant la page d'accueil de l'application backend
$app->get('/accueil[/]', function ($request, $response, $args) {
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'accueil.html');
    }
    else{
        return $this->view->render($response, 'connexion.html');
    }
})->setName('accueil');

// Route permettant de se déconnecter
$app->get('/deconnexion[/]', function ($request, $response, $args) use ($app){
    $auth = new FlashcardsAuthentification();
    $auth->deconnexion();
    header("Location: ".$this->router->pathFor('accueil'));
    exit();
})->setName('deconnexion');

// Route affichant la liste des séries
$app->get('/collections[/]', function ($request, $response, $args) use ($app){
    $lesCollections = Collection::all();
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'collections.html', array(
            'collections' => $lesCollections
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('get_collections');

// Route affichant une collection et ses cartes
$app->get('/collection/{id}[/]', function ($request, $response, $args) use ($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'collection.html', array(
            'collection' => $uneCollection
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('get_collection');

// Route affichant le formulaire d'ajout d'une collection
$app->get('/ajouterCollection[/]', function ($request, $response, $args) use($app){
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'ajouterCollection.html');
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('add_collection_page');

// Route validant l'ajout d'une collection
$app->post('/addCollection[/]', function($request, $response, $args) use ($app){
    if(isset($_SESSION['mail'])){

        try{

            $prof = Professeur::where('mail', '=', $_SESSION['mail'])->firstOrFail();

            $data = $request->getParsedBody();

            if(!empty($data['libelle']) && !empty($_FILES['image']) && !empty($_FILES['image']['tmp_name']))
            {
                $libelle = filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS);          
                $collection = new Collection();
                $collection->libelle = $libelle;
                $collection->professeur_id = $prof->id;

                //Collection image processing

                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["image"]["name"];
                $filetype = $_FILES["image"]["type"];
                $filesize = $_FILES["image"]["size"];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!array_key_exists($ext, $allowed)) {
                    return $this->view->render($response, 'ajouterCollection.html', [
                        'error' => 'Erreur, le type de votre fichier ne correspond pas à une image !'
                    ]);
                }

                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if($filesize > $maxsize) {
                    return $this->view->render($response, 'ajouterCollection.html', [
                        'error' => 'Erreur, la taille de votre image est trop importante. 5MB maximum'
                    ]);
                }

                $uuid4 = Uuid::uuid4();
                $path = $uuid4->toString();

                // Verify MYME type of the file
                if(in_array($filetype, $allowed)){
                    // Check whether file exists before uploading it
                    if(file_exists($this->public_path."/uploads/".$path)){
                        return $this->view->render($response, 'ajouterCollection.html', [
                            'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.'
                        ]);
                    } else{                 
                        move_uploaded_file($_FILES["image"]["tmp_name"], $this->public_path."/uploads/".$path.".".$ext);
                    } 
                } else{
                    return $this->view->render($response, 'ajouterCollection.html', [
                        'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.'
                    ]);
                }

                $collection->image = 'uploads'.DIRECTORY_SEPARATOR.$path.".".$ext;

                //Saving the new collection model
                $collection->save();

                header("Location: ".$this->router->pathFor('get_collection', array('id' => $collection->id)));
                exit();
            }
            else{
                return $this->view->render($response, 'ajouterCollection.html', [
                    'error' => 'Veuillez remplir tous les champs !'
                ]);
            }

        }
        catch(ModelNotFoundException $ex){
            return $this->view->render($response, 'ajouterCollection.html', [
                'error' => 'Professeur non reconnu'
            ]);
        }
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('add_collection');

// Route affichant le formulaire de modification d'une collection
$app->get('/modifierCollection/{id}[/]', function ($request, $response, $args) use($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        return $this->view->render($response, 'modifierCollection.html', array(
            'collection' => $uneCollection
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('edit_collection_page');

// Route validant la modification d'une collection
$app->post('/editCollection/{id}[/]', function($request, $response, $args) use ($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        $data = $request->getParsedBody();
        if(!empty($data['libelle']))
        {
            $libelle = filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS);
            
            $uneCollection->libelle = $libelle;

            //A variable to store the old filename in case the user attempts to edit the collection image

            $old_file = null;

            //Collection image processing

            if(!empty($_FILES['image']) && !empty($_FILES['image']['tmp_name'])){

                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["image"]["name"];
                $filetype = $_FILES["image"]["type"];
                $filesize = $_FILES["image"]["size"];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!array_key_exists($ext, $allowed)) {
                    return $this->view->render($response, 'modifierCollection.html', [
                        'error' => 'Erreur, le type de votre fichier ne correspond pas à une image !',
                        'collection' => $uneCollection
                    ]);
                        
                }

                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if($filesize > $maxsize) {
                    return $this->view->render($response, 'modifierCollection.html', [
                        'error' => 'Erreur, la taille de votre image est trop importante. 5MB maximum',
                        'collection' => $uneCollection
                    ]);
                }

                $uuid4 = Uuid::uuid4();
                $path = $uuid4->toString();

                // Verify MYME type of the file
                if(in_array($filetype, $allowed)){
                    // Check whether file exists before uploading it
                    if(file_exists($this->public_path."/uploads/".$path)){
                        return $this->view->render($response, 'modifierCollection.html', [
                            'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.',
                            'collection' => $uneCollection
                        ]);
                    } else{                 
                        move_uploaded_file($_FILES["image"]["tmp_name"], $this->public_path."/uploads/".$path.".".$ext);
                    } 
                } else{
                    return $this->view->render($response, 'modifierCollection.html', [
                        'error' => 'Erreur lors de l\'upload de votre image, veuillez réessayer ultérieurement.',
                        'collection' => $uneCollection
                    ]);
                }

                $old_file = $this->public_path.DIRECTORY_SEPARATOR.$uneCollection->image;

                $uneCollection->image = 'uploads'.DIRECTORY_SEPARATOR.$path.".".$ext;


            }

            if(!is_null($old_file) && file_exists($old_file) && is_file($old_file)){
                unlink($old_file);
            }

            $uneCollection->save();

            header("Location: ".$this->router->pathFor('get_collection', array('id' => $uneCollection->id)));
            exit();
        }
        else{
            return $this->view->render($response, 'modifierCollection.html', [
                'error' => 'Veuillez remplir tous les champs !',
                'collection' => $uneCollection
            ]);
        }
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('edit_collection');

// Route supprimant une collection
$app->get('/supprimerCollection/{id}[/]', function ($request, $response, $args) use($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        $uneCollection->cartes()->delete();
        $uneCollection->delete();
        header("Location: ".$this->router->pathFor('get_collections'));
        exit();
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('remove_collection');

// Route affichant le formulaire d'ajout d'une carte à une collection
$app->get('/ajouterCarte/{collection_id}[/]', function ($request, $response, $args) use($app){
    if(isset($_SESSION['mail'])){  
        $collection = Collection::find($args['collection_id']); 
        return $this->view->render($response, 'ajouterCarte.html', array(
            'collection' => $collection
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('add_card_page');

// Route validant l'ajout d'une carte à une collection
$app->post('/addCarte/{collection_id}[/]', function($request, $response, $args) use ($app){
    $collection_id = $args['collection_id'];
    if(isset($_SESSION['mail'])){
        $data = $request->getParsedBody();
        if(!empty($data['description']))
        {
            $description = filter_var($data['description'], FILTER_SANITIZE_SPECIAL_CHARS);
            
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["url_image"]["name"];
            $filetype = $_FILES["url_image"]["type"];
            $filesize = $_FILES["url_image"]["size"];

            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!array_key_exists($ext, $allowed)) {
                $collection = Collection::find($collection_id); 
                return $this->view->render($response, 'ajouterCarte.html', [
                    'error' => "Erreur, le type de votre fichier ne correspond pas à une image !",
                    'collection' => $collection
                ]);
            }

            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) {
                $collection = Collection::find($collection_id); 
                return $this->view->render($response, 'ajouterCarte.html', [
                    'error' => "Erreur, la taille de votre image est trop importante.",
                    'collection' => $collection
                ]);
            }

            $uuid4 = Uuid::uuid4();
            $path = $uuid4->toString();

            // Verify MYME type of the file
            if(in_array($filetype, $allowed)){
                // Check whether file exists before uploading it
                if(file_exists($this->public_path."/uploads/".$path)){
                    $collection = Collection::find($collection_id);
                    return $this->view->render($response, 'ajouterCarte.html', [
                        'error' => "Erreur lors de l'upload de votre image, veuillez réessayer ultérieurement.",
                        'collection' => $collection
                    ]);
                } else{                 
                    move_uploaded_file($_FILES["url_image"]["tmp_name"], $this->public_path."/uploads/".$path.".".$ext);
                } 
            } else{
                $collection = Collection::find($collection_id); 
                return $this->view->render($response, 'ajouterCarte.html', [
                    'error' => "Erreur lors de l'upload de votre image, veuillez réessayer ultérieurement.",
                    'collection' => $collection
                ]);
            }

            $carte = new Carte();
            $carte->description = $description;
            $carte->url_image = "uploads/".$path.".".$ext;
            $carte->collection_id = $collection_id;
            $carte->save();

            header("Location: ".$this->router->pathFor('get_collection', array('id' => $collection_id)));
            exit();
        }
        else{
            $collection = Collection::find($collection_id); 
            return $this->view->render($response, 'ajouterCarte.html', [
                'error' => 'Veuillez remplir tous les champs !',
                'collection' => $collection
            ]);
        }
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('add_card');

// Route affichant le formulaire de modification d'une carte
$app->get('/modifierCarte/{id}[/]', function ($request, $response, $args) use($app){
    $uneCarte = Carte::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCarte)){
        return $this->view->render($response, 'modifierCarte.html', array(
            'carte' => $uneCarte
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('edit_card_page');

// Route validant la modification d'une carte
$app->post('/editCarte/{id}[/]', function($request, $response, $args) use ($app){
    $uneCarte = Carte::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCarte)){
        $data = $request->getParsedBody();
        if(!empty($data['description']))
        {
            $description = filter_var($data['description'], FILTER_SANITIZE_SPECIAL_CHARS);
            $uneCarte->description = $description;
            if($_FILES["url_image"]["size"] > 0)
            {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["url_image"]["name"];
                $filetype = $_FILES["url_image"]["type"];
                $filesize = $_FILES["url_image"]["size"];
    
                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!array_key_exists($ext, $allowed)) {
                    return $this->view->render($response, 'modifierCarte.html', [
                        'error' => "Erreur, le type de votre fichier ne correspond pas à une image !",
                        'carte' => $uneCarte
                    ]);
                }
    
                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if($filesize > $maxsize) {
                    return $this->view->render($response, 'modifierCarte.html', [
                        'error' => "Erreur, la taille de votre image est trop importante.",
                        'carte' => $uneCarte
                    ]);
                }
    
                $uuid4 = Uuid::uuid4();
                $path = $uuid4->toString();
    
                // Verify MYME type of the file
                if(in_array($filetype, $allowed)){
                    // Check whether file exists before uploading it
                    if(file_exists($this->public_path."/uploads/".$path)){
                        return $this->view->render($response, 'modifierCarte.html', [
                            'error' => "Erreur lors de l'upload de votre image, veuillez réessayer ultérieurement.",
                            'carte' => $uneCarte
                        ]);
                    } else{                 
                        move_uploaded_file($_FILES["url_image"]["tmp_name"], $this->public_path."/uploads/".$path.".".$ext);
                        $uneCarte->url_image = "uploads/".$path.".".$ext;
                    } 
                } else{
                    return $this->view->render($response, 'modifierCarte.html', [
                        'error' => "Erreur lors de l'upload de votre image, veuillez réessayer ultérieurement.",
                        'carte' => $uneCarte
                    ]);
                }            
            }
            $uneCarte->save();

            header("Location: ".$this->router->pathFor('edit_collection_page', array('id' => $uneCarte->collection()->first()->id)));

            exit();
        }     
        else{
            return $this->view->render($response, 'modifierCarte.html', [
                'error' => 'La description de la carte est obligatoire !',
                'carte' => $uneCarte
            ]);
        }
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('edit_card');

// Route supprimant une carte
$app->get('/supprimerCarte/{id}[/]', function ($request, $response, $args) use($app){
    $uneCarte = Carte::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCarte)){
        $collection_id = $uneCarte->collection_id;
        $uneCarte->delete();
        header("Location: ".$this->router->pathFor('edit_collection_page', array('id' => $collection_id)));
        exit();
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('remove_card');


// Route affichant le tableau de gestion du prof
$app->get('/dashboard[/]', function ($request, $response, $args) use ($app){
    if(isset($_SESSION['mail'])){
	$prof = Professeur::select()->where('mail', '=', $_SESSION['mail'])->first();
	$lesCollections = Collection::select()->where('professeur_id', '=', $prof->id)->get();
        return $this->view->render($response, 'dashboard.html', array(
            'collections' => $lesCollections
            ));
    }
    else{
        header("Location: ".$this->router->pathFor('accueil'));
        exit();
    }
})->setName('dashboard');

//Route affichant le formulaire de paramétrage des règles de la collection

$app->get('/collections/{id: [0-9]+}/rules[/]', 'CollectionController:editRulesPage')->setName('edit_rules_page');

$app->post('/collections/{id: [0-9]+}/rules[/]', 'CollectionController:editRules')->setName('edit_rules');

$app->get('/collection/{id: [0-9]+}/games[/]', 'GameController:getGamesCollection')->setName('get_games_collection');

$app->get('/collection/{collection_id: [0-9]+}/game/{game_id: [0-9]+}[/]', 'GameController:getGameCollection')->setName('get_game_collection');

$app->get('/duplicate/{id: [0-9]+}[/]', 'CollectionController:duplicateCollectionPage')->setName('duplicate_collection_page');

$app->post('/duplicate/{id: [0-9]+}[/]', 'CollectionController:duplicateCollection')->setName('duplicate_collection');

// Lance l'application
$app->run();
