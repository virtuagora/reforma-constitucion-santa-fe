<?php

class Derecho extends Contenible {
    protected $table = 'derechos';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'descripcion', 'secciones', 'video', 'opiniones', 'imagen'];
    protected $with = ['secciones', 'opiniones'];

    public function secciones() {
        return $this->hasMany('Seccion');
    }
    
    public function opiniones() {
        return $this->hasMany('Opinion');
    }
    
    public function votos() {
        return $this->hasManyThrough('VotoSeccion', 'Seccion', 'derecho_id', 'seccion_id');
    }
}
