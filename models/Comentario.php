<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Comentario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'comentarios';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'cuerpo', 'comentable_type', 'comentable_id',
                          'created_at', 'autor', 'respuestas', 'karma'];
    protected $with = ['autor', 'respuestas'];
    protected $appends = ['karma'];

    public function comentable() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function respuestas() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function votos() {
        return $this->hasMany('VotoComentario');
    }

    public function getRaizAttribute() {
        return $this->comentable->raiz;
    }

    public function getKarmaAttribute() {
        return $this->votos()->sum('valor');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($comentario) {
            $answerIds = $comentario->respuestas()->lists('id');
            if ($answerIds) {
                VotoComentario::whereIn('comentario_id', $answerIds)->delete();
                $comentario->respuestas()->delete();
            }
            $comentario->votos()->delete();
            return true;
        });
    }

}
