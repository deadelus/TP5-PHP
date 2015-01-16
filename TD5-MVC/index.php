<?php
$loader = include('vendor\autoload.php');
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
    return $app['twig']->render('home.html', array('active_page' => 'home'));
})->bind('home');

// S'inscrire
$app->match('/signin', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            if($app['model']->signin($_POST['login'], $_POST['password'])){
                return $app['twig']->render('home.html', array(
                    'success' => 'Inscription reussis',
                    'active_page' => 'signin'));
            } else {
                return $app['twig']->render('signin.html', array(
                    'error' => 'Login déja pris',
                    'active_page' => 'signin'));
            }
        }
    }
    return $app['twig']->render('signin.html', array('active_page' => 'signin'));
})->bind('signin');

// Se loguer
$app->match('/login', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            $logged = $app['model']->login($_POST['login'], $_POST['password']);
            if($logged){
                $app['session']->set('user', array(
                    'username' => $logged['id'],
                    'login' => $_POST['login']));
                return $app['twig']->render('home.html', array(
                    'success' => 'Vous etes connecté',
                    'active_page' => 'home'));
            } else {
                return $app['twig']->render('login.html', array(
                    'error' => 'Login ou mot de passe incorrect',
                    'active_page' => 'login'
                    ));
            }
        }
    }
    return $app['twig']->render('login.html', array('active_page' => 'login'));
})->bind('login');

// Logout
$app->match('/logout', function() use ($app) {
    if($app['session']->has('user'))
    {
        $app['session']->remove('user');
    }

    return $app['twig']->render('home.html', array(
        'success' => 'Vous etes déconnecté',
        'active_page' => 'logout')); 
})->bind('logout');

// Sondages
$app->match('/sondage', function() use ($app) {
    return $app['twig']->render('sondages.html', array(
        'sondages' => $app['model']->getAllSondage(),
        'active_page' => 'sondages'
    ));
})->bind('sondages');

/*/ Le sondage
$app->match('/sondage/{id}', function($id) use ($app) {
    return $app['twig']->render('sondage.html', array('sondage' => $app['model']->getSondage($id), 'warning' => 'Vous devez être identifié pour participer! '));
})->bind('sondage');
*/
// Les reponses des sondage
// echo "session = > ".$app['session']->get('user')['username'].'<br>';

$app->match('/sondage/{id}', function($id) use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        
        if(!empty($_POST['answer']) && $app['session']->has('user'))
        {
            // echo "post activé - ";
            // echo "session activé - ";
            $answered = $app['model']->findAnswer($id, $app['session']->get('user')['username']);
            if ($answered) 
            {
                $answers = $app['model']->findAllAnswers($id);
                $total = $app['model']->totalAnswers($id);

                //var_dump($answers);
                //var_dump($total);
                // echo "deja rep";
                return $app['twig']->render('answers.html', array(
                    'answers' => $answers,
                    'total' => $total,
                    'active_page' => 'sondages'));
            }
            else
            {
                // echo "pas deja rep";
                if($app['model']->putAnswer($app['session']->get('user')['username'],$id,$_POST['answer']))
                {
                    $answers = $app['model']->findAllAnswers($id);
                    $total = $app['model']->totalAnswers($id);
                    return $app['twig']->render('answers.html', array(
                        'answers' => $answers,
                        'total' => $total,
                        'success' => 'Votre reponse à bien été enregistré',
                    'active_page' => 'sondages'));
                }
                return $app['twig']->render('sondage.html', array(
                    'warning' => 'Une erreur est survenue !',
                    'active_page' => 'sondages')); 
            }
        }
    }
    else if($app['session']->has('user'))
    {
        // echo "post desactivé - ";
        $answered = $app['model']->findAnswer($id, $app['session']->get('user')['username']);
        // echo $answered;
        if ($answered) 
        {
            $answers = $app['model']->findAllAnswers($id);
            $total = $app['model']->totalAnswers($id);

            //var_dump($answers);
            //var_dump($total);
            // echo "deja rep";
            return $app['twig']->render('answers.html', array(
                'answers' => $answers,
                'total' => $total,
                'active_page' => 'sondages'));
        }
        return $app['twig']->render('sondage.html', array(
            'sondage' => $app['model']->findAllAnswersId($id),
            'active_page' => 'sondages'));
    }
    return $app['twig']->render('sondage.html', array(
        'warning' => 'Vous devez être identifié pour participer!',
        'active_page' => 'sondages')); 
})->bind('sondage');



// Ajouter un sondage
$app->match('/addsondage', function() use ($app) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $app['session']->has('user')) {
        if (!empty($_POST['question']) && !empty($_POST['answer1']) && !empty($_POST['answer2']) && !empty($_POST['answer3'])) {
            if($app['model']->addsondage($_POST['question'], $_POST['answer1'], $_POST['answer2'], $_POST['answer3'], $app['session']->get('user')['login'])){
                return $app['twig']->render('addsondage.html', array(
                    'success' => 'Sondage soumis avec succès',
                    'active_page' => 'addsondages'));
            } else {
                return $app['twig']->render('addsondage.html', array(
                    'error' => 'Erreur SQL',
                    'active_page' => 'addsondages'));
            }
        }
    }
    return $app['twig']->render('addsondage.html', array(
                    'active_page' => 'addsondages'));
})->bind('addsondage');

// Fait remonter les erreurs
$app->error(function($error) {
    throw $error;
});

$app->run();
