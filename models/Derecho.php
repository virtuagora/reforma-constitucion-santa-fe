<?php

class Derecho extends Contenible {
    protected $table = 'derechos';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'descripcion', 'secciones', 'video'];
    protected $with = ['secciones'];

    public function secciones() {
        return $this->hasMany('Seccion');
    }
    
    public function opiniones() {
        return $this->hasMany('Opinion');
    }

    /*public static function boot() {
        parent::boot();
        static::deleting(function($documento) {
            TagCtrl::updateTags($evento->contenido, array());
            foreach ($documento->parrafos as $parrafo) {
                $CommentIds = $parrafo->comentarios()->lists('id');
                if ($CommentIds) {
                    $AnswerIds = Comentario::where('comentable_type', 'Comentario')->whereIn('comentable_id', $CommentIds)->lists('id');
                    if ($AnswerIds) {
                        VotoComentario::whereIn('comentario_id', $AnswerIds)->delete();
                        Comentario::whereIn('id', $AnswerIds)->delete();
                    }
                    VotoComentario::whereIn('comentario_id', $CommentIds)->delete();
                    $parrafo->comentarios()->delete();
                }
                $parrafo->delete();
            }
            $documento->versiones()->delete();
            $documento->contenido->delete();
            return true;
        });
    }*/
}
