<?php use Augusthur\Validation as Validate;

class PortalCtrl extends Controller {

    public function verIndex() {
        // $derechos = Contenido::where('contenible_type', 'Derecho')->get()->toArray();
        // $ajustes = Ajuste::whereIn('key', ['titulo', 'intro', 'videos'])->get();
        // foreach ($ajustes as $aju) {
        //     if ($aju->key == 'titulo') {
        //         $titulo = $aju->value;
        //     } elseif ($aju->key == 'intro') {
        //         $intro = $aju->value;
        //     } elseif ($aju->key == 'videos') {
        //         $videos = explode('&&', $aju->value);
        //     }
        // }
        // $this->render('ref/portal/inicio.twig',  [
        //     'derechos' => $derechos,
        //     'titulo' => $titulo,
        //     'intro' => $intro,
        //     'videos' => $videos,
        // ]);
        $derechos = Contenido::where('contenible_type', 'Derecho')->orderBy('orden')->get()->toArray();
        $this->render('ref/portal/inicio.twig', ['ejes' => $derechos]);
    }

    public function verPortal() {
        $this->render('ref/portal/inicio.twig');
    }

    public function verLogin() {
        $this->render('ref/registro/login-static.twig');
    }

// public function verAntecedentes() {
//         $this->render('lpe/contenido/static/antecedentes.twig');
//     }

//     public function verPropuesta() {
//         $this->render('lpe/contenido/static/propuesta.twig');
    // }

     public function verHistoria() {
        $this->render('lpe/contenido/static/historia.twig');
    }

    public function verTutorial() {
        $this->render('lpe/contenido/static/tutorial.twig');
    }


 public function verFundamentos() {
        $this->render('lpe/contenido/static/fundamentos.twig');
    }


    public function verTos() {
        $tos = Ajuste::where('key', 'tos')->firstOrFail();
        $this->render('lpe/contenido/static/tos.twig', ['tos' => $tos->toArray()]);
    }

    public function login() {
        $vdt = new Validate\Validator();
        $vdt->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\MaxLength(128));
        $req = $this->request;
        if ($vdt->validate($req->post()) && $this->session->login($vdt->getData('email'), $vdt->getData('password'))) {
            $this->redirectTo('shwIndex');
        } else {
            $this->flash('errors', array('Datos de ingreso incorrectos. Por favor vuelva a intentarlo.'));
            $this->redirectTo('shwLogin');
        }
    }

    public function logout() {
        $this->session->logout();
        $this->redirectTo('shwIndex');
    }

    public function verRegistrar() {
        $this->render('ref/registro/registro.twig', [
            'localidades' => ['Rosario','La Capital','General López','Castellanos','General Obligado',
                'San Lorenzo','Las Colonias','Constitución','Caseros','San Jerónimo','San Cristóbal',
                'Iriondo','San Martín','Vera','Belgrano','San Justo','San Javier','9 de Julio','Garay'],
            'ocupaciones' => ['Estudiante','Docente Nivel Inicial','Docente Nivel Primario',
                'Docente Nivel Secundario','Docente Nivel Terciario','Docente Universitario','Asistente escolar',
                'Representante gremial','Profesional','Empleado/a en relación de dependencia','Comerciante',
                'Funcionario/a, legislador/a y autoridad gubernamental','Representante de organización social',
                'Trabajador/a doméstico/a no remunerado/a','Otro']
        ]);
    }

    public function registrar() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(1))
            ->addRule('nombre', new Validate\Rule\MaxLength(32))
            ->addRule('apellido', new Validate\Rule\Alpha(array(' ')))
            ->addRule('apellido', new Validate\Rule\MinLength(1))
            ->addRule('apellido', new Validate\Rule\MaxLength(32))
            ->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\Matches('password2'))
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('email', new Validate\Rule\Unique('usuarios'))
            ->addRule('birthday', new Validate\Rule\Date('Y-m-d'))
            ->addRule('address', new Validate\Rule\InArray(['Rosario','La Capital','General López','Castellanos',
                'General Obligado','San Lorenzo','Las Colonias','Constitución','Caseros','San Jerónimo','San Cristóbal',
                'Iriondo','San Martín','Vera','Belgrano','San Justo','San Javier','9 de Julio','Garay']))
            ->addRule('title', new Validate\Rule\InArray(['Estudiante','Docente Nivel Inicial','Docente Nivel Primario',
                'Docente Nivel Secundario','Docente Nivel Terciario','Docente Universitario','Asistente escolar',
                'Representante gremial','Profesional','Empleado/a en relación de dependencia','Comerciante',
                'Funcionario/a, legislador/a y autoridad gubernamental','Representante de organización social',
                'Trabajador/a doméstico/a no remunerado/a','Otro']))
            ->addFilter('email', 'strtolower')
            ->addFilter('email', 'trim');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $recaptcha = new \ReCaptcha\ReCaptcha('6LcF-CYTAAAAACfCi_a60IK5E57PGC0xDp4Ko5ex');
        $resp = $recaptcha->verify($vdt->getData('g-recaptcha-response'));
        if (!$resp->isSuccess()) {
            throw new TurnbackException('El CAPTCHA es inválido.');
        }
        $cumple = Carbon\Carbon::parse($vdt->getData('birthday'));
        $limInf = Carbon\Carbon::create(1900, 1, 1, 0, 0, 0);
        $limSup = Carbon\Carbon::now();
        if (!$cumple->between($limInf, $limSup)) {
            throw new TurnbackException('Fecha inválida.');
        }
        $preuser = Preusuario::firstOrNew(['email' => $vdt->getData('email')]);
        $preuser->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $preuser->nombre = $vdt->getData('nombre');
        $preuser->apellido = $vdt->getData('apellido');
        $preuser->emailed_token = bin2hex(openssl_random_pseudo_bytes(16));
        $preuser->birthday = $cumple;
        $preuser->title = $vdt->getData('title');
        $preuser->address = $vdt->getData('address');
        $preuser->save();
        
        $to = $preuser->email;
        $subject = 'Confirma tu registro - Ley del Arbol - Santa Fe';
        $message = $this->view->fetch('email/registro.twig', [
            'id' => $preuser->id,
            'token' => $preuser->emailed_token
        ]);
        mail($to, $subject, $message);
        
        $this->render('ref/registro/registro-exito.twig', array('email' => $preuser->email));
    }

    public function verificarEmail($idPre, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPre, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $preuser = Preusuario::findOrFail($idPre);
        if ($token == $preuser->emailed_token) {
            $usuario = new Usuario;
            $usuario->email = $preuser->email;
            $usuario->password = $preuser->password;
            $usuario->nombre = $preuser->nombre;
            $usuario->apellido = $preuser->apellido;
            $usuario->puntos = 0;
            $usuario->suspendido = false;
            // Saque el campo de es_jefe y cambie es_funcionario por es_moderador
            $usuario->es_moderador = false;
            $usuario->img_tipo = 1;
            $usuario->img_hash = md5($preuser->email);
            $usuario->birthday = $preuser->birthday;
            $usuario->title = $preuser->title;
            $usuario->address = $preuser->address;
            $usuario->save();
            $preuser->delete();
            $this->render('ref/registro/validar-correo.twig', array('usuarioValido' => true,
                                                                'email' => $usuario->email));
        } else {
            $this->render('ref/registro/validar-correo.twig', array('usuarioValido' => false));
        }
    }

    public function verRecuperarClave() {
        $this->render('ref/registro/recuperar-clave.twig');
    }
    
    public function recuperarClave() {
        $vdt = new Validate\Validator();
        $vdt->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addFilter('email', 'strtolower')
            ->addFilter('email', 'trim');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::where('email', $vdt->getData('email'))->first();
        if (is_null($usuario)) {
            throw new TurnbackException('Email inválido. ¿Estás seguro de que te registraste?');
        }
        $usuario->token = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->save();
        
        $to = $usuario->email;
        $subject = 'Reiniciar clave - Ley del Arbol - Santa Fe';
        $message = $this->view->fetch('email/recuperar.twig', [
            'id' => $usuario->id,
            'token' => $usuario->token
        ]);
        mail($to, $subject, $message);
        
        $this->redirectTo('shwRecuperarClave');
    }
    
    public function verReiniciarClave($idUsu, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idUsu, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $this->render('ref/registro/reiniciar-clave.twig', ['idUsu' => $idUsu, 'token' => $token]);
    }
    
    public function reiniciarClave($idUsu, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idUsu, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $vdt = new Validate\Validator();
        $vdt->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\Matches('password2'));
        if (!$vdt->validate($this->request->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::findOrFail($idUsu);
        if ($token != $usuario->token) {
            throw new TurnbackException('El link ha expirado o es inválido. Recordá que solamente es válido por una hora.');
        }
        $ahora = Carbon\Carbon::now();
        if ($ahora->gt($usuario->updated_at->addHour())) {
            throw new TurnbackException('El link ha expirado o es inválido. Recordá que solamente es válido por una hora.');
        }
        $usuario->token = null;
        $usuario->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $usuario->save();
        $this->redirectTo('endReiniciarClave');
    }
    
    public function finReiniciarClave() {
        $this->render('ref/registro/reiniciar-completo.twig');
    }
}
