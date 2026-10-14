<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'idUsuario';
    protected $allowedFields = [
        'idTipoUsuario',
        'correoUsuario',
        'password',
        'nombreUsuario',
        'apellidoUsuario',
        'dniUsuario',
        'estadoUsuario',
        'ultimaConexion',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];
    protected $useTimestamps = false; // Si no usas timestamps automáticos

    // Usuario con la descripción de su tipo (descripcionTipoUsuario)
    public function conTipo(int $idUsuario): ?array
    {
        return $this->select('usuario.*, tipo_usuario.descripcionTipoUsuario')
            ->join('tipo_usuario', 'tipo_usuario.idTipoUsuario = usuario.idTipoUsuario', 'left')
            ->where('usuario.idUsuario', $idUsuario)
            ->first();
    }
}