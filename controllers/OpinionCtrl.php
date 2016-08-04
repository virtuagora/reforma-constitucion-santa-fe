<?php use Augusthur\Validation as Validate;

class OpinionCtrl extends Controller {

    public function verCrear() {
        $derechos = Derechos::all();
        $eventos = Eventos::all();
        $participantes = Participante::all();
        $this->render('lpe/contenido/opinion/crear.twig', [
            'derechos' => $derechos->toArray(),
            'eventos' => $eventos->toArray(),
            'participantes' => $participantes->toArray(),
        ]);
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $autor = $this->session->getUser();
        $opinion = new Opinion;
        $opinion->cuerpo = $vdt->getData('descripcion');
        $opinion->derecho_id = $vdt->getData('derecho');
        $opinion->evento_id = $vdt->getData('evento');
        $opinion->participante_id = $vdt->getData('participante');
        $opinion->save();
        $this->flash('success', 'La opinión se creó exitosamente.');
        $this->redirectTo('shwDerecho', array('idDer' => $opinion->derecho_id));
    }

    private function validarOpinion($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('participante', new Validate\Rule\Exists('participantes'))
            ->addRule('evento', new Validate\Rule\Exists('eventos'))
            ->addRule('derecho', new Validate\Rule\Exists('derechos'));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
