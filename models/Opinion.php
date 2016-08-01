<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Opinion extends Eloquent {
    protected $table = 'opiniones';
    protected $visible = array('id', 'cuerpo');

    public function Derecho() {
        return $this->belongsTo('Derecho');
    }
}
