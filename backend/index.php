<?php  
require '../vendor/autoload.php';  
require '../src/models/Carte.php'; 
require '../src/models/Collection.php';
require 'src/flashcards/auth/FlashcardsAuthentification.php';
require '../src/handlers/exceptions.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

session_start();
$config = include('../src/config.php');
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
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('src/flashcards/view', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $root = dirname($_SERVER['SCRIPT_NAME'],1);
    $view->getEnvironment()->addGlobal("root", $root);
    
    // Variables globales twig avec le mail et le pseudo de l'utilisateur connecté
    if(isset($_SESSION['mail'])){
        $prof = Professeur::select()->where('mail', '=', $_SESSION['mail'])->first();
        $view->getEnvironment()->addGlobal("mail", $prof->mail);
        $view->getEnvironment()->addGlobal("nom", $prof->nom);
        $view->getEnvironment()->addGlobal("prenom", $prof->prenom);
    }

    return $view;
};

// Document root de l'application utilisé pour les redirections
$app->root = dirname($_SERVER['SCRIPT_NAME'],1);

// Route affichant le formulaire d'inscription
$app->get('/inscription[/]', function ($request, $response, $args) {
    return $this->view->render($response, 'inscription.html', $args);
});

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
               header("Location: ".$app->root."/connexion");
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
});

// Route affichant le formulaire de connexion
$app->get('/connexion[/]', function ($request, $response, $args) {
    return $this->view->render($response, 'connexion.html', $args);
});

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
            header("Location: ".$app->root."/accueil");
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
});

// Route affichant la page d'accueil de l'application backend
$app->get('/[accueil]', function ($request, $response, $args) {
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'accueil.html');
    }
    else{
        return $this->view->render($response, 'connexion.html');
    }
});

// Route permettant de se déconnecter
$app->get('/deconnexion[/]', function ($request, $response, $args) use ($app){
    $auth = new FlashcardsAuthentification();
    $auth->deconnexion();
    header("Location: ".$app->root."/accueil");
    exit();
});

// Route affichant la liste des séries
$app->get('/collections[/]', function ($request, $response, $args) use ($app){
    $lesCollections = Collection::all();
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'collections.html', array(
            'collections' => $lesCollections
            ));
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route affichant une collection et ses cartes
$app->get('/collection/{id}[/]', function ($request, $response, $args) use ($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'collection.html', array(
            'collection' => $uneCollection
            ));
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route affichant le formulaire d'ajout d'une collection
$app->get('/ajouterCollection[/]', function ($request, $response, $args) use($app){
    if(isset($_SESSION['mail'])){
        return $this->view->render($response, 'ajouterCollection.html');
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route validant l'ajout d'une collection
$app->post('/addCollection[/]', function($request, $response, $args) use ($app){
    if(isset($_SESSION['mail'])){
        $data = $request->getParsedBody();
        if(!empty($data['libelle']))
        {
            $libelle = filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS);          
            
            $collection = new Collection();
            $collection->libelle = $libelle;
            $collection->save();

            header("Location: ".$app->root."/collection/".$collection->id);
            exit();
        }
        else{
            return $this->view->render($response, 'ajouterCollection.html', [
                'error' => 'Veuillez remplir tous les champs !'
            ]);
        }
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route affichant le formulaire de modification d'une collection
$app->get('/modifierCollection/{id}[/]', function ($request, $response, $args) use($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        return $this->view->render($response, 'modifierCollection.html', array(
            'collection' => $uneCollection
            ));
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route validant la modification d'une collection
$app->post('/editCollection/{id}[/]', function($request, $response, $args) use ($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        $data = $request->getParsedBody();
        if(!empty($data['libelle']))
        {
            $libelle = filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS);
            
            $uneCollection->libelle = $libelle;
            $uneCollection->save();

            header("Location: ".$app->root."/collection/".$uneCollection->id);
            exit();
        }
        else{
            return $this->view->render($response, 'modifierCollection.html', [
                'error' => 'Veuillez remplir tous les champs !'
            ]);
        }
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route supprimant une collection
$app->get('/supprimerCollection/{id}[/]', function ($request, $response, $args) use($app){
    $uneCollection = Collection::find($args['id']);
    if(isset($_SESSION['mail']) and !is_null($uneCollection)){
        $uneCollection->cartes()->delete();
        $uneCollection->delete();
        header("Location: ".$app->root."/collections");
        exit();
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Route affichant le formulaire d'ajout d'une carte à une collection
$app->get('/ajouterCarte/{collection_id}[/]', function ($request, $response, $args) use($app){
    if(isset($_SESSION['mail'])){  
        $collection = Collection::find($args['collection_id']); 
        return $this->view->render($response, 'ajouterCarte.html', array(
            'collection' => $collection
            ));
    }
    else{
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

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
                if(file_exists($app->root."/upload/".$path)){
                    $collection = Collection::find($collection_id); 
                    return $this->view->render($response, 'ajouterCarte.html', [
                        'error' => "Erreur lors de l'upload de votre image, veuillez réessayer ultérieurement.",
                        'collection' => $collection
                    ]);
                } else{                 
                    move_uploaded_file($_FILES["url_image"]["tmp_name"], "upload/".$path.".".$ext);
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
            $carte->url_image = "upload/".$path.".".$ext;
            $carte->collection_id = $collection_id;
            $carte->save();

            header("Location: ".$app->root."/collection/".$collection_id);
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
        header("Location: ".$app->root."/accueil");
        exit();
    }
});

// Lance l'application
$app->run();