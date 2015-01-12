<?php

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'\src\Sondage\Views',
));

$app['model'] = new Sondage\Model\Model(
    'localhost',  // Hôte
    'sondage',    // Base de données
    'root',    // Utilisateur
    ''     // Mot de passe
);

// Page d'accueil
$app->match('/', function() use ($app) {
    return $app['twig']->render('home.html');
})->bind('home');

// S'inscrire
$app->match('/signin', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            if($app['model']->signin($_POST['login'], $_POST['password'])){
                return $app['twig']->render('home.html', array('success' => 'Inscription reussis'));
            } else {
                return $app['twig']->render('signin.html', array('error' => 'Login déja pris'));
            }
        }
    }
    return $app['twig']->render('signin.html', array());
})->bind('signin');

// Se loguer
$app->match('/login', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            if($app['model']->login($_POST['login'], $_POST['password'])){
                $app['session']->set('user', array('username' => $_POST['login']));
                return $app['twig']->render('home.html', array('success' => 'Vous etes connecté'));
            } else {
                return $app['twig']->render('login.html', array('error' => 'Login ou mot de passe incorrect'));
            }
        }
    }
    return $app['twig']->render('login.html', array());
})->bind('login');

// Logout
$app->match('/logout', function() use ($app) {
    if($app['session']->has('user'))
    {
        $app['session']->remove('user');
    }

    return $app['twig']->render('home.html', array('success' => 'Vous etes déconnecté')); 
})->bind('logout');

// Sondages
$app->match('/sondage', function() use ($app) {
    return $app['twig']->render('sondages.html', array(
        'sondages' => $app['model']->getAllSondage()
    ));
})->bind('sondages');

// Le sondage
$app->match('/sondage/{id}', function($id) use ($app) {
    return $app['twig']->render('sondage.html', array('sondage' => $app['model']->getSondage($id), 'warning' => 'Vous devez être identifié pour participer! '));
})->bind('sondage');

// Ajouter un sondage
$app->match('/addsondage', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['question']) && !empty($_POST['answer1']) && !empty($_POST['answer2']) && !empty($_POST['answer3'])) {
            if($app['model']->addsondage($_POST['question'], $_POST['answer1'], $_POST['answer2'], $_POST['answer3'])){
                return $app['twig']->render('addsondage.html', array('success' => 'Sondage soumis avec succès'));
            } else {
                return $app['twig']->render('addsondage.html', array('error' => 'Erreur SQL'));
            }
        }
    }
    return $app['twig']->render('addsondage.html', array());
})->bind('addsondage');

// Fait remonter les erreurs
$app->error(function($error) {
    throw $error;
});

$app->run();
