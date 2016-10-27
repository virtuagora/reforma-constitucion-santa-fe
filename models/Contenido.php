<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'titulo', 'contenible_id', 'contenible_type', 'puntos',
                          'created_at', 'link', 'autor', 'contenible'];
    protected $appends = ['link'];
    protected $with = ['autor'];

    public function contenible() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function getLinkAttribute() {
        //TODO fix
        //return $app->request->getUrl() . $app->urlFor($name, $attr);
        return 'abcdefg';
    }

    public function setTituloAttribute($value) {
        $this->attributes['titulo'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }
}
