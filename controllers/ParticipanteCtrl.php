<?php use Augusthur\Validation as Validate;

class ParticipanteCtrl extends Controller {

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('lpe/admin/participantes.twig', [
            'categorias' => $categorias->toArray()
        ]);
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarParticipante($req->post());
        $autor = $this->session->getUser();
        $partici = new Participante;
        $partici->nombre = $vdt->getData('nombre');
        $partici->descripcion = $vdt->getData('descripcion');
        $partici->save();
        $this->flash('success', 'El participante se creó exitosamente.');
        $this->redirectTo('shwListaPartici');
    }
    
    public function listar() {
        $participantes = Participante::all();
        $this->render('lpe/admin/participantes.twig', [
            'participantes' => $participantes
        ]);
    }

    private function validarParticipante($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\MinLength(1))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('descripcion', new Validate\Rule\MinLength(1))
            ->addRule('descripcion', new Validate\Rule\MaxLength(1024));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
