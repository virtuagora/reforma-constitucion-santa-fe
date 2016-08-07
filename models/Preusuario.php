<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Preusuario extends Eloquent {
    //protected $table = 'preusuarios';
    protected $dates = ['birthday'];
    protected $visible = ['id', 'nombre', 'apellido', 'emailed_token'];
    protected $fillable = ['email'];
}
