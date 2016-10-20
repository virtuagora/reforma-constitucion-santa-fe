<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoComentario extends Eloquent {
    protected $table = 'comentario_votos';
    protected $visible = ['id', 'cantidad', 'created_at'];
    protected $fillable = ['comentario_id', 'usuario_id'];

    public function comentario() {
        return $this->belongsTo('Comentario');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }
}
