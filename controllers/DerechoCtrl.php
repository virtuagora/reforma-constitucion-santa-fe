<?php use Augusthur\Validation as Validate;

class DerechoCtrl extends Controller {

    public function ver($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
        $this->render('lpe/contenido/derecho/ver.twig', [
            'derecho' => $datosDer,
        ]);
    }

    public function verCrear() {
        $this->render('lpe/contenido/derecho/crear.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $autor = $this->session->getUser();
        $derecho = new Derecho;
        $derecho->descripcion = $vdt->getData('descripcion');
        $derecho->video = $vdt->getData('video');
        if ($vdt->getData('orden')) {
            $derecho->orden = $vdt->getData('orden');
        }
        $derecho->save();
        $acciones = $vdt->getData('secciones');
        foreach ($acciones as $accion) {
            $newAccion = new Seccion;
            $newAccion->descripcion = $accion;
            $newAccion->derecho()->associate($derecho);
            $newAccion->save();
        }
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = 1;
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($derecho);
        $contenido->save();
        $this->flash('success', 'El derecho se creó exitosamente.');
        $this->redirectTo('shwDerecho', ['idDer' => $derecho->id]);
    }
    
    public function verModificar($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datos = array_merge($contenido->toArray(), $derecho->toArray());
        $this->render('lpe/contenido/derecho/editar.twig', [
            'derecho' => $datos
        ]);
    }

    public function modificar($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $derecho->descripcion = $vdt->getData('descripcion');
        $derecho->video = $vdt->getData('video');
        if ($vdt->getData('orden')) {
            $derecho->orden = $vdt->getData('orden');
        }
        $derecho->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->save();
        $accNew = $vdt->getData('secciones');
        $accOld = $derecho->secciones;
        $i = 0;
        foreach ($accOld as $accion) {
            if (isset($accNew[$i])) {
                $accion->descripcion = $accNew[$i];
                $i++;
                $accion->save();
            }
        }
        $this->flash('success', 'Los datos del derecho fueron modificados exitosamente.');
        $this->redirectTo('shwDerecho', array('idDer' => $idDer));
    }

    private function validarDerecho($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(1))
            ->addRule('titulo', new Validate\Rule\MaxLength(255))
            ->addRule('descripcion', new Validate\Rule\MinLength(4))
            ->addRule('video', new Validate\Rule\MinLength(8))
            ->addRule('video', new Validate\Rule\MaxLength(16))
            ->addRule('secciones', new Validate\Rule\MinLength(4))
            ->addFilter('secciones', FilterFactory::explode('&&'))
            ->addRule('orden', new Validate\Rule\NumNatural())
            ->addOptional('video')
            ->addOptional('orden');
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
