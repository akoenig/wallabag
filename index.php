<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define ('POCHE', '1.8.0');
require 'check_setup.php';
require_once 'inc/poche/global.inc.php';

// Start session
Session::$sessionName = 'wallabag';
Session::init();

// Let's rock !
$wallabag = new Poche();
//$wallabag->run();


$authenticateForRole = function() {
    return function() {
        if (!Session::isLogged()) {
            $app = \Slim\Slim::getInstance();
            $app->flash('error', 'Login required');
            $app->redirect($app->urlFor('login'));
        }
    };
};

$app = new \Slim\Slim();

$app->post('/login', function () use ($app, $wallabag) {
    $wallabag->login($app->request->post('login'), $app->request->post('password'));
    $app->redirect($app->urlFor('homepage'));
});

$app->get('/login', function () use ($wallabag) {
    $vars = array(
        'referer' => $wallabag->referer,
        'view' => $wallabag->view,
        'poche_url' => Tools::getPocheUrl(),
        'title' => _('wallabag, a read it later open source system'),
        'token' => Session::getToken(),
        'theme' => $wallabag->tpl->getTheme(),
        'http_auth' => 0
    );

    echo $wallabag->tpl->render('login.twig', $vars);
})->name('login');

$app->get('/logout', function () use ($app, $wallabag) {
    $wallabag->logout();
    $app->redirect($app->urlFor('homepage'));
});

$app->get('/', $authenticateForRole(), function () use ($wallabag) {
    $vars = array(
        'referer' => $wallabag->referer,
        'view' => $wallabag->view,
        'poche_url' => Tools::getPocheUrl(),
        'title' => _('wallabag, a read it later open source system'),
        'token' => Session::getToken(),
        'theme' => $wallabag->tpl->getTheme(),
        'http_auth' => 0
    );

    echo $wallabag->tpl->render('home.twig', array_merge($vars, $wallabag->displayView('home', 0)));
})->name('homepage');

$app->run();