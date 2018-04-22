<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Seccion extends Eloquent {
    protected $table = 'secciones';
    protected $visible = ['id', 'descripcion', 'comentarios','derecho'];
    protected $with = ['comentarios'];

    public function derecho() {
        return $this->belongsTo('Derecho');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function getRaizAttribute() {
        return $this->derecho;
    }
}
