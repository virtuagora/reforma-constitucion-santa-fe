<?php use Augusthur\Validation as Validate;

class AdminCtrl extends Controller {

    public function verAdminAjustes() {
        $ajustes = Ajuste::all();
        $this->render('lpe/admin/ajustes.twig', array('ajustes' => $ajustes->toArray()));
    }

public function verExportar() {
        $this->render('lpe/admin/exportar.twig');
    }

public function verEstadisticas() {
        $usuarios = Usuario::all();
        $this->render('lpe/admin/estadisticas.twig', array('usuarios' => $usuarios->toArray()));
    }

    public function adminAjustes() {
        $vdt = new Validate\Validator();
        $vdt->addRule('tos', new Validate\Rule\MinLength(8))
            ->addRule('tos', new Validate\Rule\MaxLength(8192))
            ;
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $ajustes = Ajuste::all();
        foreach ($ajustes as $ajuste) {
            $newValue = $vdt->getData($ajuste->key);
            if (isset($newValue)) {
                $ajuste->value = $newValue;
                $ajuste->save();
                AdminlogCtrl::createLog('', 1, 'mod', $this->session->user('id'), $ajuste);
            }
        }

        $this->flash('success', 'Los ajustes se han modificado exitosamente.');
        $this->redirectTo('shwAdmAjuste');
    }
    
    public function verCrearModerador() {
        $mods = Usuario::whereNotNull('patrulla_id')->get()->toArray();
        $this->render('lpe/admin/moderadores.twig', ['moderadores' => $mods]);
    }
    
    public function crearModerardor() {
        $req = $this->request;
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($req->post('id'), new Validate\Rule\NumNatural());
        $usuario = Usuario::findOrFail($vdt->getData('id'));
        if ($usuario->patrulla_id) {
            throw new TurnbackException('Ese usuario ya es moderador.');
        }
        $usuario->patrulla_id = 1;
        $usuario->save();
        $this->flash('success', 'El usuario ya es moderador.');
        $this->redirectTo('shwCrearModerad');
    }

    public function sancUsuario($idUsu) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idUsu', new Validate\Rule\NumNatural())
            ->addRule('tipo', new Validate\Rule\InArray(array('Suspension', 'Advertencia', 'Quita')))
            ->addRule('mensaje', new Validate\Rule\MinLength(4))
            ->addRule('mensaje', new Validate\Rule\MaxLength(128))
            ->addRule('cantidad', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idUsu' => $idUsu), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::findOrFail($vdt->getData('idUsu'));
        switch ($vdt->getData('tipo')) {
            case 'Suspension':
                $usuario->suspendido = true;
                if ($vdt->getData('cantidad') > 0) {
                    $usuario->fin_suspension = Carbon\Carbon::now()->addDays($vdt->getData('cantidad'));
                } else {
                    $usuario->fin_suspension = null;
                }
                $usuario->save();
                $mensaje = "El usuario fue suspendido.";
                break;
            case 'Advertencia':
                $usuario->advertencia = $vdt->getData('mensaje');
                $usuario->fin_advertencia = Carbon\Carbon::now()->addDays($vdt->getData('cantidad'));
                $usuario->save();
                $mensaje = "El usuario ha sido advertido.";
                break;
            case 'Quita':
                $usuario->decrement('puntos', $vdt->getData('cantidad'));
                $mensaje = "Se le han quitado los puntos al usuario.";
                break;
        }
        $subclase = strtolower(substr($vdt->getData('tipo'), 0, 3));
        $log = AdminlogCtrl::createLog($vdt->getData('mensaje'), 1, $subclase, $this->session->user('id'), $usuario);
        NotificacionCtrl::createNotif($usuario->id, $log);
        $this->flash('success', $mensaje);
        $this->redirect($req->getReferrer());
    }

    public function verIndexAdmin() {
        $this->render('lpe/admin/indexAdmin.twig');
    }
    
    public function verSubirImagen() {
        $dir = __DIR__ . '/../public/img/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $files = array_diff(scandir($dir), ['..', '.']);
        $this->render('lpe/admin/subirImagenes.twig', ['imagenes' => $files]);
    }

    public function subirImagen() {
        $req = $this->request;
        $asociar = $req->post('asociar');
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($asociar, new Validate\Rule\InArray([
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'no', 'banner'
        ]));
        if (in_array($asociar, ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])) {
            $dir = __DIR__ . '/../public/img/bloque';
            $id = $asociar;
        } elseif ($asociar == 'banner') {
            $dir = __DIR__ . '/../public/img';
            $id = 'banner';
        } else {
            $dir = __DIR__ . '/../public/img/uploads';
            $id = time();
        }
        if (isset($_FILES['archivo'])) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $storage = new \Upload\Storage\FileSystem($dir, true);
            $file = new \Upload\File('archivo', $storage);
            $file->setName($id);
            $file->addValidations([
                new \Upload\Validation\Mimetype(['image/jpg', 'image/jpeg', 'image/png']),
                new \Upload\Validation\Size('2M')
            ]);
            $file->upload();
        } else {
            throw new TurnbackException('No seleccionó imagen.');
        }
        $this->flash('success', 'La imagen se cargó exitosamente.');
        $this->redirectTo('shwCrearImagen');
    }
}
