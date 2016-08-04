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
            $table->integer('img_tipo')->unsigned();
            $table->string('img_hash');
            $table->string('huella')->nullable();
            $table->integer('puntos')->default(0);
            $table->string('advertencia')->nullable();
            $table->boolean('suspendido')->default(0);
            $table->boolean('es_funcionario')->default(0);
            $table->boolean('es_jefe')->default(0);
            $table->string('dni')->nullable();
            $table->string('token')->nullable()->default(null);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fin_advertencia')->nullable();
            $table->timestamp('fin_suspension')->nullable();
            $table->integer('partido_id')->unsigned()->nullable();
            $table->integer('patrulla_id')->unsigned()->nullable();
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
            $table->timestamps();
        });
        Capsule::schema()->create('partidos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre')->unique();
            $table->string('acronimo');
            $table->text('descripcion');
            $table->string('huella')->nullable();
            $table->string('fundador')->nullable();
            $table->date('fecha_fundacion')->nullable();
            $table->integer('creador_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('participantes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre')->unique();
            $table->text('descripcion');
            $table->string('huella')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('organismos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->text('descripcion');
            $table->integer('cupo')->unsigned();
            $table->string('huella')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('funcionarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('usuario_id')->unsigned();
            $table->integer('organismo_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('contactos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('contactable');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('web')->nullable();
            $table->timestamps();
        });
        Capsule::schema()->create('acciones', function($table) {
            $table->engine = 'InnoDB';
            $table->string('id', 10)->primary();
            $table->string('nombre');
        });
        Capsule::schema()->create('userlogs', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('objeto');
            $table->string('accion_id', 10);
            $table->integer('actor_id')->unsigned();
            $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('notificaciones', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('notificable');
            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->softDeletes();
        });
        Capsule::schema()->create('patrullas', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->text('descripcion');
            $table->timestamps();
        });
        Capsule::schema()->create('poderes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->string('descripcion');
        });
        Capsule::schema()->create('patrulla_poder', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('patrulla_id')->unsigned();
            $table->integer('poder_id')->unsigned();
            $table->foreign('patrulla_id')->references('id')->on('patrullas')->onDelete('cascade');
            $table->foreign('poder_id')->references('id')->on('poderes')->onDelete('cascade');
        });
        Capsule::schema()->create('adminlogs', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('descripcion');
            $table->string('subclase');
            $table->morphs('objeto');
            $table->integer('poder_id')->unsigned();
            $table->integer('actor_id')->unsigned();
            $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('categorias', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('contenidos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('contenible');
            $table->string('titulo');
            $table->string('huella')->nullable();
            $table->integer('puntos')->unsigned()->default(0);
            $table->integer('impulsor_id')->unsigned()->nullable();
            $table->integer('referido_id')->unsigned()->nullable();
            $table->integer('categoria_id')->unsigned();
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('tags', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->string('huella')->nullable();
            $table->integer('menciones')->unsigned()->default(0);
            $table->timestamps();
        });
        Capsule::schema()->create('taggables', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('taggable');
            $table->integer('tag_id')->unsigned();
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
        Capsule::schema()->create('derechos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('descripcion');
            $table->string('video');
            $table->boolean('imagen');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('secciones', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('descripcion', 1024);
            $table->integer('derecho_id')->unsigned();
            $table->foreign('derecho_id')->references('id')->on('derechos');
            $table->timestamps();
        });
        Capsule::schema()->create('seccion_votos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('postura');
            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->integer('seccion_id')->unsigned();
            $table->foreign('seccion_id')->references('id')->on('secciones')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('propuestas', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('cuerpo');
            $table->integer('votos_favor')->unsigned()->default(0);
            $table->integer('votos_contra')->unsigned()->default(0);
            $table->integer('votos_neutro')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('propuesta_votos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('postura');
            $table->boolean('publico');
            $table->integer('usuario_id')->unsigned();
            $table->integer('propuesta_id')->unsigned();
            $table->foreign('propuesta_id')->references('id')->on('propuestas')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('problematicas', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('cuerpo');
            $table->integer('afectados_directos')->unsigned()->default(0);
            $table->integer('afectados_indirectos')->unsigned()->default(0);
            $table->integer('afectados_indiferentes')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('problematica_votos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('postura')->unsigned();
            $table->integer('usuario_id')->unsigned();
            $table->integer('problematica_id')->unsigned();
            $table->foreign('problematica_id')->references('id')->on('problematicas')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('documentos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('ultima_version')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('documento_versiones', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('version')->unsigned();
            $table->integer('documento_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('documento_parrafos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('cuerpo');
            $table->integer('ubicacion')->unsigned();
            $table->integer('version_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('novedades', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('cuerpo');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('eventos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo');
            $table->string('huella')->nullable();
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->text('cuerpo');
            $table->string('lugar');
            $table->string('coordenadas');
            $table->dateTime('fecha');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('evento_usuario', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('usuario_id')->unsigned();
            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
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
        Capsule::schema()->create('opiniones', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('cuerpo');
            $table->integer('participante_id')->unsigned();
            $table->integer('derecho_id')->unsigned();
            $table->integer('evento_id')->unsigned()->nullable();
            $table->foreign('participante_id')->references('id')->on('participantes');
            $table->foreign('derecho_id')->references('id')->on('derechos');
            $table->foreign('evento_id')->references('id')->on('eventos');
            $table->timestamps();
            $table->softDeletes();
        });
        $ajuste = new Ajuste;
        $ajuste->key = 'tos';
        $ajuste->value_type = 'txt';
        $ajuste->value = 'Términos y condiciones de uso.';
        $ajuste->description = 'Términos y condiciones para el uso de la plataforma.';
        $ajuste->save();
        $categoria = new Categoria;
        $categoria->nombre = 'General';
        $categoria->save();
        $usuario = new Usuario;
        $usuario->email = $_POST['usr_email'];
        $usuario->password = md5($_POST['usr_password']);
        $usuario->nombre = $_POST['usr_nombre'];
        $usuario->apellido = $_POST['usr_apellido'];
        $usuario->img_tipo = 1;
        $usuario->img_hash = md5(strtolower(trim($usuario->email)));
        $patrulla = new Patrulla;
        $patrulla->nombre = 'Aministrador';
        $patrulla->descripcion = 'Admnistrador que instaló la plataforma.';
        $patrulla->save();
        $poderes = [
            ['nombre' => 'Moderar', 'descripcion' => 'Moderar en la plataforma.'],
            ['nombre' => 'Configurar plataforma', 'descripcion' => 'Configurar parámetros de Virtugora.'],
            ['nombre' => 'Administrar organismos', 'descripcion' => 'Definir los organimos existentes.'],
            ['nombre' => 'Administrar funcionarios', 'descripcion' => 'Asignar los funcionarios a sus respectivos organismos.'],
            ['nombre' => 'Administrar patrullas', 'descripcion' => 'Definir los distintos grupos de moderación.'],
            ['nombre' => 'Administrar moderadores', 'descripcion' => 'Asignar los usuarios que serán moderadores.'],
            ['nombre' => 'Verificar ciudadanos', 'descripcion' => 'Registrar como verificados a usuarios que lo demuestren.'],
        ];
        Poder::insert($poderes);
        $patrulla->poderes()->attach([1,2,3,4,5,6,7]);
        $usuario->patrulla()->associate($patrulla);
        $usuario->save();
    }
} catch (Exception $e) {
    $titulo = '¡Ha ocurrido un error!';
    $descrp = 'No puede establecerse conexión con la base de datos. Revise el archivo de configuracion.';
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
