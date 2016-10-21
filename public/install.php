<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asistente de instalación Virtuágora</title>
    <link rel="stylesheet" href="assets-lpe/css/lpe.css" />
</head>
<body>
<div class="container" style="margin-top:20px";>
<div class="row">
<div class="col-sm-6 col-sm-offset-3">
<div class="panel panel-primary">
<div class="panel-heading">
    <h3 class="panel-title">Instalar Virtuagora (LPE)</h3>
  </div>
  <div class="panel-body">

<?php if(isset($_POST['submit'])) {
$titulo = '¡Virtuágora se ha instalado exitosamente!';
$descrp = 'Ya puede comenzar a utilizar la plataforma, pero primero elimine este archivo para evitar inconvenientes de seguridad.';
$exito = true;
try {
    if (Capsule::schema()->hasTable('ajustes')) {
        $titulo = '¡Ha ocurrido un error!';
        $descrp = 'La plataforma parece ya estar instalada.';
        $exito = true;
    } else {
        Capsule::schema()->create('ajustes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('key')->unique();
            $table->string('value_type');
            $table->integer('int_value')->nullable();
            $table->string('str_value')->nullable();
            $table->text('txt_value')->nullable();
            $table->string('description');
            $table->timestamps();
        });
        Capsule::schema()->create('usuarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellido');
            $table->dateTime('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('title')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('extra')->nullable();
            $table->integer('img_tipo')->unsigned();
            $table->string('img_hash');
            $table->string('huella')->nullable();
            $table->integer('puntos')->default(0);
            $table->string('advertencia')->nullable();
            $table->boolean('suspendido')->default(0);
            $table->boolean('es_moderador')->default(0);
            $table->string('dni')->nullable();
            $table->string('token')->nullable()->default(null);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fin_advertencia')->nullable();
            $table->timestamp('fin_suspension')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('preusuarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('emailed_token');
            $table->dateTime('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });
        Capsule::schema()->create('contenidos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('contenible');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('huella')->nullable();
            $table->integer('puntos')->unsigned()->default(0);
            $table->integer('orden')->default(0);
            $table->integer('categoria_id')->unsigned();
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('derechos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('descripcion');
            $table->string('video');
            $table->string('imagen');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('secciones', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('descripcion');
            $table->integer('derecho_id')->unsigned();
            $table->foreign('derecho_id')->references('id')->on('derechos');
            $table->timestamps();
        });
        Capsule::schema()->create('comentarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('comentable');
            $table->text('cuerpo');
            $table->integer('votos')->default(0);
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('comentario_votos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('valor');
            $table->integer('usuario_id')->unsigned();
            $table->integer('comentario_id')->unsigned();
            $table->foreign('comentario_id')->references('id')->on('comentarios')->onDelete('cascade');
            $table->timestamps();
        });
        $ajusA = new Ajuste;
        $ajusA->key = 'tos';
        $ajusA->value_type = 'txt';
        $ajusA->value = 'Términos y condiciones de uso.';
        $ajusA->description = 'Términos y condiciones para el uso de la plataforma.';
        $ajusA->save();
        $ajusB = new Ajuste;
        $ajusB->key = 'titulo';
        $ajusB->value_type = 'str';
        $ajusB->value = 'Nueva Ley de Educación Provincial';
        $ajusB->description = 'Título que se muestra en la página de inicio.';
        $ajusB->save();
        $ajusC = new Ajuste;
        $ajusC->key = 'intro';
        $ajusC->value_type = 'txt';
        $ajusC->value = 'Texto de muestra.';
        $ajusC->description = 'Texto introductorio en la página de inicio.';
        $ajusC->save();
        $ajusD = new Ajuste;
        $ajusD->key = 'videos';
        $ajusD->value_type = 'str';
        $ajusD->value = '7uulVAHwXi0';
        $ajusD->description = 'Videos que se muestran en la página de inicio.';
        $ajusD->save();
        $usuario = new Usuario;
        $usuario->email = $_POST['usr_email'];
        $usuario->password = password_hash($_POST['usr_password'], PASSWORD_DEFAULT);
        $usuario->nombre = $_POST['usr_nombre'];
        $usuario->apellido = $_POST['usr_apellido'];
        $usuario->img_tipo = 1;
        $usuario->es_moderador = 1;
        $usuario->img_hash = md5(strtolower(trim($usuario->email)));
        $usuario->save();
    }
} catch (Exception $e) {
    $titulo = '¡Ha ocurrido un error!';
    $descrp = 'No puede establecerse conexión con la base de datos. Revise el archivo de configuracion.';
    $descrp .= '<br>'.$e->getMessage();
    $exito = false;
}?>
<h2><strong><?php echo $titulo ?></strong></h2>
<p class="lead"><?php echo $descrp ?></p>
<?php if ($exito) { ?>
        <hr>
        <a class="btn btn-primary btn-block" href='./'>Comenzar</a>
    <?php } ?>
<?php } else { ?>
    <form method="post" class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <h2><strong>¡Bienvenido!</strong></h2>
<p class="lead">Muchas gracias por elegir Virtuágora. Por favor complete los siguientes datos para crear
            la cuenta de administrador principal de la plataforma.</p>
            <hr>
            <div class="form-group">
      <label for="inputEmail" class="col-lg-2 control-label">Email</label>
      <div class="col-lg-10">
        <input type="text" name="usr_email" class="form-control" id="inputEmail" placeholder="miemail@dominio.com">
      </div>
    </div>
    <div class="form-group">
      <label for="inputNombre" class="col-lg-2 control-label">Nombre</label>
      <div class="col-lg-10">
        <input type="text" name="usr_nombre" class="form-control" id="inputNombre" placeholder="Juan">
      </div>
    </div>
    <div class="form-group">
      <label for="inputApellido" class="col-lg-2 control-label">Apellido</label>
      <div class="col-lg-10">
        <input type="text" name="usr_apellido" class="form-control" id="inputApellido" placeholder="Perez">
      </div>
    </div>
    <div class="form-group">
      <label for="inputPassword" class="col-lg-2 control-label">Contraseña</label>
      <div class="col-lg-10">
        <input type="password" name="usr_password" class="form-control" id="inputPassword" placeholder="">
      </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-primary btn-block" name="submit" value="Instalar">Instalar</button>
    </form>
<?php } ?>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
