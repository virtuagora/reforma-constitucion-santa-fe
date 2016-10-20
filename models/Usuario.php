<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Usuario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'usuarios';
    protected $dates = ['deleted_at', 'fin_advertencia', 'fin_suspension', 'birthday'];
    protected $hidden = ['password', 'emailed_token', 'updated_at', 'deleted_at'];

    public function contenidos() {
        return $this->hasMany('Contenido', 'autor_id');
    }

    public function comentarios() {
        return $this->hasMany('Comentario', 'autor_id');
    }

    public function getIdentidadAttribute() {
        return $this->attributes['nombre'].' '.$this->attributes['apellido'];
    }

    public function setNombreAttribute($value) {
        $this->attributes['nombre'] = $value;
        $apellido = isset($this->attributes['apellido'])? ' '.$this->attributes['apellido']: '';
        $this->attributes['huella'] = FilterFactory::calcHuella($value.$apellido);
    }

    public function setApellidoAttribute($value) {
        $this->attributes['apellido'] = $value;
        $nombre = isset($this->attributes['nombre'])? $this->attributes['nombre'].' ': '';
        $this->attributes['huella'] = FilterFactory::calcHuella($nombre.$value);
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($usuario) {
            foreach ($usuario->contenidos as $contenido) {
                $contenido->contenible->delete();
            }
            foreach ($usuario->comentarios as $comentario) {
                $comentario->delete();
            }
            return true;
        });
    }

}
