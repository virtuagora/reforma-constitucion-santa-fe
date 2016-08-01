<?php use Augusthur\Validation as Validate;

class DerechoCtrl extends Controller {

    public function ver($idDer) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idDoc, new Validate\Rule\NumNatural());
        $derecho = Documento::with('contenido.tags')->findOrFail($idDer);
        $contenido = $documento->contenido;
        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
        $this->render('lpe/contenido/derecho/ver.twig', [
            'derecho' => $datosDer,
            'voto' => [] // [idSeccion => postura]
        ]);
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('lpe/contenido/derecho/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDorecho($req->post());
        $autor = $this->session->getUser();
        $derecho = new Derecho;
        $derecho->descripcion = $vdt->getData('descripcion');
        $derecho->video = $vdt->getData('video');
        $derecho->imagen = false;
        $derecho->save();
        $acciones = $vdt->getData('secciones');
        foreach ($acciones as $accion) {
            $newAccion = new Seccion;
            $newAccion->descripcion = $accion;
            $newAccion->save();
        }
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($derecho);
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        UserlogCtrl::createLog('newDocumen', $autor->id, $documento);
        $autor->increment('puntos', 25);
        $this->flash('success', 'El derecho se creó exitosamente.');
        $this->redirectTo('shwDerecho', array('idDer' => $derecho->id));
    }

    private function validarDerecho($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('descripcion', new Validate\Rule\MinLength(8))
            ->addRule('descripcion', new Validate\Rule\MaxLength(2048))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('video', new Validate\Rule\MinLength(8))
            ->addRule('video', new Validate\Rule\MaxLength(16))
            ->addRule('secciones', new Validate\Rule\MinLength(4))
            ->addRule('secciones', new Validate\Rule\MaxLength(512))
            ->addFilter('secciones', FilterFactory::explode('|'))
            ->addFilter('tags', FilterFactory::explode(','));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
