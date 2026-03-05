<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use Illuminate\Http\Request;

/**
 * Controlador responsável por gerenciar os avisos.
 * Inclui listagem, criação, edição e exclusão com validações em português.
 */
class AvisoController extends Controller
{
    /**
     * Exibe todos os avisos ordenados do mais recente para o mais antigo.
     */
    public function index()
    {
        $avisos = Aviso::orderByDesc('created_at')->get();
        return view('admin.avisos', compact('avisos'));
    }

    /**
     * Armazena um novo aviso após validar os dados informados.
     * Permite HTML seguro e filtra tags não permitidas.
     */
    public function store(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'titulo'   => ['required', 'max:150'],
                'mensagem' => ['required', 'string'],
                'tipo'     => ['nullable', 'in:info,alerta,erro'],
            ],
            [
                'titulo.required' => 'O campo título é obrigatório.',
                'titulo.max'      => 'O campo título deve ter no máximo 150 caracteres.',
                'mensagem.required' => 'O campo mensagem é obrigatório.',
            ]
        );

        // Permitir apenas tags HTML básicas seguras
        $dadosValidados['mensagem'] = strip_tags(
            $dadosValidados['mensagem'],
            '<a><b><strong><i><em><u><br><p><ul><ol><li>'
        );

        // Adicionar segurança aos links externos
        $dadosValidados['mensagem'] = preg_replace(
            '/<a\s+([^>]*target="_blank"[^>]*)>/i',
            '<a $1 rel="noopener noreferrer">',
            $dadosValidados['mensagem']
        );

        Aviso::create($dadosValidados);

        return redirect()
            ->route('avisos.index')
            ->with('sucesso', 'Aviso criado com sucesso.');
    }

    /**
     * Atualiza um aviso existente com base no identificador informado.
     * Mantém a validação e higienização do conteúdo HTML.
     */
    public function update(Request $request, int $id)
    {
        $dadosValidados = $request->validate(
            [
                'titulo'   => ['required', 'max:150'],
                'mensagem' => ['required', 'string'],
                'tipo'     => ['nullable', 'in:info,alerta,erro'],
            ],
            [
                'titulo.required' => 'O campo título é obrigatório.',
                'titulo.max'      => 'O campo título deve ter no máximo 150 caracteres.',
                'mensagem.required' => 'O campo mensagem é obrigatório.',
            ]
        );

        // Permitir apenas tags HTML básicas seguras
        $dadosValidados['mensagem'] = strip_tags(
            $dadosValidados['mensagem'],
            '<a><b><strong><i><em><u><br><p><ul><ol><li>'
        );

        // Adicionar segurança aos links externos
        $dadosValidados['mensagem'] = preg_replace(
            '/<a\s+([^>]*target="_blank"[^>]*)>/i',
            '<a $1 rel="noopener noreferrer">',
            $dadosValidados['mensagem']
        );

        $aviso = Aviso::findOrFail($id);
        $aviso->update($dadosValidados);

        return redirect()
            ->route('avisos.index')
            ->with('sucesso', 'Aviso atualizado com sucesso.');
    }

    /**
     * Remove definitivamente o aviso informado.
     */
    public function destroy(int $id)
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->delete();

        return redirect()
            ->route('avisos.index')
            ->with('sucesso', 'Aviso excluído com sucesso.');
    }
}
