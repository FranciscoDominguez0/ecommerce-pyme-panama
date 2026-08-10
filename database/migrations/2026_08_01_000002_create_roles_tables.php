<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->comment('Roles de acceso. Integrado con Spatie Permission');

            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->string('name', 125);
            $table->string('guard_name', 125)->default('web');

            $table->unique(['nombre'], 'roles_nombre_key');
            $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
        });

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_roles ON public.roles');
        DB::statement('CREATE TRIGGER trg_upd_roles BEFORE UPDATE ON public.roles FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('permisos', function (Blueprint $table) {
            $table->comment('Permisos granulares por módulo');

            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('modulo', 100);
            $table->timestamp('creado_en')->useCurrent();
            $table->string('name', 150);
            $table->string('guard_name', 125)->default('web');

            $table->unique(['nombre'], 'permisos_nombre_key');
            $table->unique(['name', 'guard_name'], 'permisos_name_guard_name_unique');
        });

        Schema::create('usuario_roles', function (Blueprint $table) {
            $table->comment('Relación muchos a muchos: usuario ↔ rol');

            $table->bigInteger('usuario_id');
            $table->bigInteger('rol_id');
            $table->timestamp('asignado_en')->useCurrent();
            $table->string('model_type', 255)->nullable();

            $table->primary(['usuario_id', 'rol_id']);
            $table->foreign('usuario_id', 'usuario_roles_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('rol_id', 'usuario_roles_rol_id_fkey')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->comment('Relación muchos a muchos: rol ↔ permiso');

            $table->bigInteger('rol_id');
            $table->bigInteger('permiso_id');

            $table->primary(['rol_id', 'permiso_id']);
            $table->foreign('rol_id', 'rol_permisos_rol_id_fkey')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permiso_id', 'rol_permisos_permiso_id_fkey')->references('id')->on('permisos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_roles ON public.roles');
        Schema::dropIfExists('rol_permisos');
        Schema::dropIfExists('usuario_roles');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
