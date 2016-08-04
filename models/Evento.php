<?php

class Evento {
    protected $table = 'eventos';
    protected $dates = ['deleted_at', 'fecha'];
    protected $visible = ['id', 'titulo', 'cuerpo', 'fecha', 'lugar'];

    public function usuarios() {
        return $this->belongsToMany('Usuario', 'evento_usuario');
    }
    
    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }
    
    public function getIdentidadAttribute() {
        return $this->titulo;
    }

    public function getRaizAttribute() {
        return $this;
    }
    
    public function setTituloAttribute($value) {
        $this->attributes['titulo'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($evento) {
            foreach ($evento->comentarios as $comentario) {
                $comentario->delete();
            }
            $evento->usuarios()->detach();
            return true;
        });
    }
}
