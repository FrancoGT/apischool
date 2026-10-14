<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Crea el tipo de usuario "Superadministrador" y su usuario asociado.
 * Es idempotente: si el tipo o el correo ya existen, no los duplica.
 *
 * Credenciales configurables en .env:
 *   superadmin.email    = superadmin@apischool.com
 *   superadmin.password = SuperAdmin123*
 *
 * Ejecutar: php spark db:seed SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $ahora    = date('Y-m-d H:i:s');
        $correo   = env('superadmin.email', 'superadmin@apischool.com');
        $password = env('superadmin.password', 'SuperAdmin123*');

        // tipo_usuario: "Superadministrador"
        $tipo = $this->db->table('tipo_usuario')
            ->where('descripcionTipoUsuario', 'Superadministrador')
            ->get()
            ->getRowArray();

        if ($tipo) {
            $idTipoUsuario = (int) $tipo['idTipoUsuario'];
        } else {
            $this->db->table('tipo_usuario')->insert([
                'descripcionTipoUsuario' => 'Superadministrador',
                'fechaCreacion'          => $ahora,
                'fechaActualizacion'     => $ahora,
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ]);
            $idTipoUsuario = (int) $this->db->insertID();
        }

        // usuario superadmin
        $existe = $this->db->table('usuario')
            ->where('correoUsuario', $correo)
            ->countAllResults();

        if ($existe > 0) {
            echo "El usuario superadmin '{$correo}' ya existe.\n";

            return;
        }

        $this->db->table('usuario')->insert([
            'idTipoUsuario'        => $idTipoUsuario,
            'correoUsuario'        => $correo,
            'password'             => password_hash($password, PASSWORD_ARGON2ID),
            'nombreUsuario'        => 'SUPER',
            'apellidoUsuario'      => 'ADMINISTRADOR',
            'dniUsuario'           => '00000000',
            'estadoUsuario'        => 1,
            'ultimaConexion'       => $ahora,
            'fechaCreacion'        => $ahora,
            'fechaActualizacion'   => $ahora,
            'usuarioCreacion'      => 1,
            'usuarioActualizacion' => 1,
        ]);

        echo "Usuario superadmin '{$correo}' creado.\n";
    }
}
