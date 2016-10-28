<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'titulo', 'descripcion', 'contenible_id', 'contenible_type',
                          'created_at', 'link', 'autor', 'contenible', 'puntos', 'orden'];
    protected $appends = ['link'];
    protected $with = ['autor'];

    public function contenible() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function getLinkAttribute() {
        $name = 'shwDerecho';
        $attr = ['idDer' => $this->attributes['contenible_id']];
        $app = Slim\Slim::getInstance();
        return $app->request->getUrl() . $app->urlFor($name, $attr);
    }

    public function setTituloAttribute($value) {
        $this->attributes['titulo'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }
}
