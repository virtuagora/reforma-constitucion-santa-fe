<?php use Augusthur\Validation as Validate;

class DerechoCtrl extends Controller {

    public function ver($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido.tags')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
        
        $votosUsr = [];
        if ($this->session->user('id')) {
            $votos = $derecho->votos()->where('usuario_id', $this->session->user('id'))->get();
            foreach ($votos as $voto) {
                $votosUsr[$voto->seccion_id] = $voto->postura;
            }
        }
        
        $this->render('lpe/contenido/derecho/ver.twig', [
            'derecho' => $datosDer,
            'voto' => $votosUsr
        ]);
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('lpe/contenido/derecho/crear.twig', [
            'categorias' => $categorias->toArray()
        ]);
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $autor = $this->session->getUser();
        $derecho = new Derecho;
        $derecho->descripcion = $vdt->getData('descripcion');
        $derecho->video = $vdt->getData('video');
        $derecho->imagen = $_FILES['archivo'];
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
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($derecho);
        $contenido->save();
        
        if (isset($_FILES['archivo'])) {
            $dir = __DIR__ . '/../public/img';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $storage = new \Upload\Storage\FileSystem($dir, true);
            $file = new \Upload\File('archivo', $storage);
            $file->setName($derecho->id);
            $file->addValidations([
                new \Upload\Validation\Mimetype(['image/jpg', 'image/jpeg']),
                new \Upload\Validation\Size('2M')
            ]);
            $file->upload();
        }
        
        $this->flash('success', 'El derecho se creó exitosamente.');
        $this->redirectTo('shwDerecho', ['idDer' => $derecho->id]);
    }
    
    public function votar($idSec) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray([1,2,3]))
            ->addRule('idSec', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(['idSec' => $idSec], $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $seccion = Seccion::with('derecho')->findOrFail($idSec);
        $voto = VotoSeccion::firstOrNew([
            'seccion_id' => $seccion->id,
            'usuario_id' => $usuario->id
        ]);
        $voto->postura = $vdt->getData('postura');
        $voto->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwDerecho', ['idDer' => $seccion->derecho->id]);
    }

    private function validarDerecho($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('descripcion', new Validate\Rule\MinLength(8))
            // ->addRule('descripcion', new Validate\Rule\MaxLength(2048))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('video', new Validate\Rule\MinLength(8))
            ->addRule('video', new Validate\Rule\MaxLength(16))
            ->addRule('secciones', new Validate\Rule\MinLength(4))
            ->addRule('secciones', new Validate\Rule\MaxLength(1024))
            ->addFilter('secciones', FilterFactory::explode('&&'))
            ->addFilter('tags', FilterFactory::explode(','));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
