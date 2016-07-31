<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoSeccion extends Eloquent {
    protected $table = 'seccion_votos';
    protected $visible = array('id', 'postura', 'created_at', 'updated_at');
    protected $fillable = array('seccion_id', 'usuario_id');

    public function seccion() {
        return $this->belongsTo('Seccion');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
