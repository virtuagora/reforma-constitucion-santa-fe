<?php use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Contenible extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function getIdentidadAttribute() {
        return $this->contenido->titulo;
    }

    public function getRaizAttribute() {
        return $this;
    }
}
