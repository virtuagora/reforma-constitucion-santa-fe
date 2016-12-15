<?php
// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => false,
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true,
);
$app->view->parserExtensions = array(new ExtendedTwig());

// Prepare singletons
$app->container->singleton('session', function () use ($app) {
    return new SessionManager($app->getMode());
});

$app->container->singleton('translator', function () {
    $trans = new Symfony\Component\Translation\Translator('es');
    $trans->addLoader('php', new Symfony\Component\Translation\Loader\PhpFileLoader());
    $trans->addResource('php', __DIR__.'/../locales/es.php', 'es');
    return $trans;
});

$app->api = false;

// Prepare error handler
$app->error(function (Exception $e) use ($app) {
    if ($app->api) {
        // TODO setar codigo de error correcto.
        $msg = array('code' => $e->getCode(), 'message' => $e->getMessage());
        if ($e instanceof TurnbackException) {
            $msg['errors'] = $e->getErrors();
        }
        echo json_encode($msg);
    } else {
        if ($e instanceof TurnbackException) {
            $app->flash('errors', $e->getErrors());
            ob_end_clean();
            $app->redirect($app->request->getReferrer());
        } else if ($e instanceof BearableException) {
            $app->render('ref/misc/error.twig', array('mensaje' => $e->getMessage()), $e->getCode());
        } else if ($e instanceof Illuminate\Database\Eloquent\ModelNotFoundException) {
            $app->notFound();
        } else if ($e instanceof Illuminate\Database\QueryException && $e->getCode() == 23000) {
            $app->render('ref/misc/error.twig', array('mensaje' => 'La información ingresada es inconsistente.'), 400);
        } else {
            //$app->render('misc/fatal-error.twig', array('type' => get_class($e), 'exception' => $e));
            $app->render('ref/misc/error.twig', ['mensaje' => 'Ocurrió un error interno.'], 500);
        }
    }
});

// Prepare hooks
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('user' => $app->session->user()));
});

// Prepare middlewares
$checkNoSession = function () use ($app) {
    if ($app->session->check()) {
        $app->redirectTo('shwPortal');
    }
};

$checkRole = function ($role) use ($app) {
    return function () use ($role, $app) {
        if (is_array($role)) {
            $userRoles = $app->session->grantedRoles($role);
            $rejected = empty($userRoles);
        } else {
            $rejected = !$app->session->hasRole($role);
        }
        if ($rejected) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
};

// Prepare dispatcher
$app->get('/test', function () use ($app) {
    // $req = $app->request;
    // $uri = $req->headers->get('x-forwarded-host')?: $req->getUrl();
    // var_dump($uri);
    $app->render('ref/test.twig');
});

$app->group('/eje', function () use ($app, $checkRole) {
    $app->get('/crear', $checkRole('mod'), 'DerechoCtrl:verCrear')->name('shwCrearDerecho');
    $app->post('/crear', $checkRole('mod'), 'DerechoCtrl:crear')->name('runCrearDerecho');
    $app->get('/:idDer', 'DerechoCtrl:ver')->name('shwDerecho');
    $app->get('/:idDer/modificar', $checkRole('mod'), 'DerechoCtrl:verModificar')->name('shwModifDerecho');
    $app->post('/:idDer/modificar', $checkRole('mod'), 'DerechoCtrl:modificar')->name('runModifDerecho');
});
$app->group('/admin', function () use ($app, $checkRole) {
    $app->get('/', $checkRole('mod'), 'AdminCtrl:verIndexAdmin')->name('shwIndexAdmin');    
    $app->get('/upload', $checkRole('mod'), 'AdminCtrl:verSubirImagen')->name('shwCrearImagen');
    $app->post('/upload', $checkRole('mod'), 'AdminCtrl:subirImagen')->name('runCrearImagen');
    $app->get('/imagen/:idEve', 'AdminCtrl:verImagen')->name('shwImagen');
    $app->post('/sancionar/:idUsu', $checkRole('mod'), 'AdminCtrl:sancUsuario')->name('runSanUsuario');
    $app->get('/ajustes', $checkRole('mod'), 'AdminCtrl:verAdminAjustes')->name('shwAdmAjuste');
    $app->post('/ajustes', $checkRole('mod'), 'AdminCtrl:adminAjustes')->name('runAdmAjuste');
    $app->get('/estadisticas', $checkRole('mod'), 'AdminCtrl:verEstadisticas')->name('shwEstadi');
    $app->get('/exportar', $checkRole('mod'), 'AdminCtrl:verExportar')->name('shwExportar');
    $app->get('/emails', $checkRole('mod'), 'AdminCtrl:verEmails')->name('shwEmails');
    $app->get('/ejes', $checkRole('mod'), 'AdminCtrl:verModificarEjes')->name('shwModificarEjes');    
    $app->get('/comments/:idDer', $checkRole('mod'), 'AdminCtrl:verComments')->name('shwComments');
    $app->get('/moderador/crear', $checkRole('mod'), 'AdminCtrl:verCrearModerador')->name('shwCrearModerad');
    $app->post('/moderador/crear', $checkRole('mod'), 'AdminCtrl:crearModerador')->name('runCrearModerad');
});

$app->group('/comentario', function () use ($app, $checkRole) {
    $app->get('', 'ComentarioCtrl:listar')->name('shwListaComenta');
    $app->post('/comentar/:tipoRaiz/:idRaiz', $checkRole('usr'), 'ComentarioCtrl:comentar')->name('runComentar');
    $app->get('/:idCom', 'ComentarioCtrl:ver')->name('shwComenta');
    $app->post('/:idCom/votar', $checkRole('usr'), 'ComentarioCtrl:votar')->name('runVotarComenta');
    $app->post('/eliminar', $checkRole('usr'), 'ComentarioCtrl:eliminar')->name('runElimiComenta');
});

$app->get('/', 'PortalCtrl:verIndex')->name('shwIndex');
$app->get('/portal', 'PortalCtrl:verPortal')->name('shwPortal');
$app->get('/tos', 'PortalCtrl:verTos')->name('shwTos');;
$app->get('/login', $checkNoSession, 'PortalCtrl:verLogin')->name('shwLogin');
$app->post('/login', $checkNoSession, 'PortalCtrl:login')->name('runLogin');
$app->post('/logout', 'PortalCtrl:logout')->name('runLogout');
$app->get('/registro', $checkNoSession, 'PortalCtrl:verRegistrar')->name('shwCrearUsuario');
$app->post('/registro', $checkNoSession, 'PortalCtrl:registrar')->name('runCrearUsuario');
$app->get('/validar/:idUsu/:token', 'PortalCtrl:verificarEmail')->name('runValidUsuario');
$app->get('/recuperar-clave', $checkNoSession, 'PortalCtrl:verRecuperarClave')->name('shwRecuperarClave');
$app->post('/recuperar-clave', $checkNoSession, 'PortalCtrl:recuperarClave')->name('runRecuperarClave');
$app->get('/reiniciar-clave/:idUsu/:token', $checkNoSession, 'PortalCtrl:verReiniciarClave')->name('shwReiniciarClave');
$app->post('/reiniciar-clave/:idUsu/:token', $checkNoSession, 'PortalCtrl:reiniciarClave')->name('runReiniciarClave');
$app->get('/reiniciar-clave', $checkNoSession, 'PortalCtrl:finReiniciarClave')->name('endReiniciarClave');

$app->get('/contenido/:idCon', 'ContenidoCtrl:ver')->name('shwConteni');
$app->get('/contenido', 'ContenidoCtrl:listar')->name('shwListaConteni');

$app->get('/usuario/:idUsu', 'UsuarioCtrl:ver')->name('shwUsuario');
$app->get('/usuario/:idUsu/imagen/:res', 'UsuarioCtrl:verImagen')->name('shwImgUsuario');
$app->get('/usuario', 'UsuarioCtrl:listar')->name('shwListaUsuario');

$app->group('/perfil', function () use ($app, $checkRole) {
    $app->get('/modificar', $checkRole('usr'), 'UsuarioCtrl:verModificar')->name('shwModifUsuario');
    $app->post('/modificar', $checkRole('usr'), 'UsuarioCtrl:modificar')->name('runModifUsuario');
    $app->get('/cambiar-clave', $checkRole('usr'), 'UsuarioCtrl:verCambiarClave')->name('shwModifClvUsuario');
    $app->post('/cambiar-clave', $checkRole('usr'), 'UsuarioCtrl:cambiarClave')->name('runModifClvUsuario');
});
