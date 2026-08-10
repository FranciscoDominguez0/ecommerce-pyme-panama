<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->comment('Todos los usuarios del sistema: administradores y clientes');

            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 255);
            $table->string('password_hash', 255);
            $table->string('telefono', 30)->nullable();
            $table->string('foto_perfil_ruta', 500)->nullable()->comment('Ruta relativa: storage/app/public/perfiles/foto.jpg');
            $table->date('fecha_nacimiento')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('bloqueado')->default(false);
            $table->text('motivo_bloqueo')->nullable();
            $table->timestamp('bloqueado_en')->nullable();
            $table->boolean('two_fa_habilitado')->default(false);
            $table->string('two_fa_secreto', 255)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verificado_en')->nullable();
            $table->timestamp('ultimo_login_en')->nullable();
            $table->string('ultimo_login_ip', 45)->nullable();
            $table->timestamp('eliminado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->unique(['email'], 'usuarios_email_key');
            $table->index(['email'], 'idx_usuarios_email');
        });

        DB::statement('CREATE INDEX idx_usuarios_activo ON public.usuarios USING btree (activo) WHERE (eliminado_en IS NULL)');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.actualizar_timestamp()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                NEW.actualizado_en := NOW();
                RETURN NEW;
            END;
            $$;
            SQL);

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_usuarios ON public.usuarios');
        DB::statement('CREATE TRIGGER trg_upd_usuarios BEFORE UPDATE ON public.usuarios FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_usuarios ON public.usuarios');
        Schema::dropIfExists('usuarios');
    }
};
