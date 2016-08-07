<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Opinion extends Eloquent {
    protected $table = 'opiniones';
    protected $visible = array('id', 'cuerpo', 'participante');
    protected $with = ['participante'];

    public function derecho() {
        return $this->belongsTo('Derecho');
    }
    
    public function participante() {
        return $this->belongsTo('Participante');
    }
    
    public function evento() {
        return $this->belongsTo('Evento');
    }
}
