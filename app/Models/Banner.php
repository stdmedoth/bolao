<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo responsável pela tabela 'banners'
 * 
 * Campos esperados:
 * - id (BIGINT, auto increment)
 * - titulo (VARCHAR, opcional)
 * - imagem (VARCHAR, obrigatório)
 * - ativo (BOOLEAN)
 * - created_at / updated_at (TIMESTAMP)
 */
class Banner extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'banners';

    // Campos que podem ser preenchidos via mass assignment
    protected $fillable = [
        'titulo',
        'imagem',
        'ativo',
    ];

    // Define os tipos de cada campo
    protected $casts = [
        'ativo' => 'boolean',
    ];
}
