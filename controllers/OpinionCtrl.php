<?php use Augusthur\Validation as Validate;

class OpinionCtrl extends Controller {

    public function ver($idOpi) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idOpi, new Validate\Rule\NumNatural());
        $opinion = Opinion::with('derecho')->findOrFail($idOpi);
        
        $datosEven = $evento->toArray();
        $datosEven['interesados'] = $evento->usuarios()->count();
        $datosPart = $participe? $participe->toArray(): null;
        $this->render('contenido/evento/ver.twig', [
            'evento' => $datosEven,
            'comentarios' =>  $comentarios,
            'participacion' => $datosPart,
            'participantes' => $participantes
        ]);
    }

    public function verCrear() {
        $derechos = Contenido::where('contenible_type', 'Derecho')->get();
        $eventos = Evento::all();
        $participantes = Participante::all();
        $this->render('lpe/contenido/opinion/crear.twig', [
            'derechos' => $derechos->toArray(),
            'eventos' => $eventos->toArray(),
            'participantes' => $participantes->toArray(),
        ]);
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarOpinion($req->post());
        $autor = $this->session->getUser();
        $opinion = new Opinion;
        $opinion->cuerpo = $vdt->getData('cuerpo');
        $opinion->derecho_id = $vdt->getData('derecho');
        $opinion->evento_id = $vdt->getData('evento');
        $opinion->participante_id = $vdt->getData('participante');
        $opinion->save();
        $this->flash('success', 'La opinión se creó exitosamente.');
        $this->redirectTo('shwDerecho', array('idDer' => $opinion->derecho_id));
    }
    
    public function verModificar($idOpi) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idOpi, new Validate\Rule\NumNatural());
        $opinion = Opinion::findOrFail($idOpi);
        $datos = $opinion->toArray();
        $this->render('lpe/contenido/opinion/editar.twig', ['opinion' => $datos]);
    }

    public function modificar($idOpi) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idOpi, new Validate\Rule\NumNatural());
        $opinion = Opinion::findOrFail($idOpi);
        $usuario = $this->session->getUser();
        $req = $this->request;
        $opinion->cuerpo = $req->post('cuerpo');
        $opinion->save();
        $this->flash('success', 'Los datos de la opinion fueron modificados exitosamente.');
        $this->redirectTo('shwOpinion', ['idOpi' => $idOpi]);
    }

    private function validarOpinion($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('cuerpo', new Validate\Rule\MinLength(2))
            ->addRule('participante', new Validate\Rule\Exists('participantes'))
            ->addRule('evento', new Validate\Rule\Exists('eventos'))
            ->addRule('derecho', new Validate\Rule\Exists('derechos'));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
