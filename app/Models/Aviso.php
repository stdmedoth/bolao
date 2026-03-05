<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Classe responsável por representar um aviso no sistema.
 * Cada instância guarda um título e uma mensagem a ser exibida aos usuários.
 */
class Aviso extends Model
{
    use HasFactory;

    /**
     * Lista de atributos que podem ser preenchidos em massa.
     * Mantemos apenas título e mensagem para evitar alterações indesejadas.
     */
    protected $fillable = [
    'titulo',
    'mensagem',
    'tipo',
];
}
