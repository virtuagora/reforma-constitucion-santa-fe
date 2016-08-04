<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Participante extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'partidos';
    protected $visible = array('id', 'nombre', 'descripcion');

    public function opiniones() {
        return $this->hasMany('Opinion');
    }

    public function getIdentidadAttribute() {
        return $this->attributes['nombre'];
    }

    public function setNombreAttribute($value) {
        $this->attributes['nombre'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }

/*
    public static function boot() {
        parent::boot();
        static::created(function($partido) {
            Usuario::where('id', $partido->creador_id)->update(array('es_jefe' => true,
                                                                     'partido_id' => $partido->id));
        });
        static::deleting(function($partido) {
            Usuario::where('partido_id', $partido->id)->update(array('partido_id' => null,
                                                                     'es_jefe' => false));
            $partido->contacto->delete();
            return true;
        });
    }
*/
}
