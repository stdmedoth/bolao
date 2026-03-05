<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Exibe a lista de banners
     */
    public function index()
    {
        $banners = Banner::orderByDesc('id')->get();
        // ✅ Corrigido: agora aponta para 'admin.banners', não 'admin.banners.index'
        return view('admin.banners', compact('banners'));
    }

    /**
     * Armazena um novo banner
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:150',
            'imagem' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'imagem.required' => 'Selecione uma imagem para o banner.',
            'imagem.image' => 'O arquivo enviado deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve estar em formato JPG ou PNG.',
        ]);

        // Caminho onde as imagens são salvas
        $destination = public_path('assets/img/banners');

        // Garante que o diretório exista
        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        // Gera um nome único para a imagem
        $imageName = time() . '_' . $request->file('imagem')->getClientOriginalName();
        $request->file('imagem')->move($destination, $imageName);

        // Cria o banner no banco
        Banner::create([
            'titulo' => $request->titulo,
            'imagem' => 'assets/img/banners/' . $imageName,
            'ativo'  => true,
        ]);

        return redirect()->route('banners.index')->with('sucesso', 'Banner criado com sucesso!');
    }

    /**
     * Alterna o status ativo/inativo de um banner
     */
    public function toggle($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->ativo = !$banner->ativo;
        $banner->save();

        return redirect()->route('banners.index')->with('sucesso', 'Status do banner atualizado!');
    }

    /**
     * Atualiza título e imagem (se enviada)
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'titulo' => 'nullable|string|max:150',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Atualiza o título
        $banner->titulo = $request->titulo;

        // Se foi enviada nova imagem, substitui a existente
        if ($request->hasFile('imagem')) {
            $destination = public_path('assets/img/banners');
            $imageName = time() . '_' . $request->file('imagem')->getClientOriginalName();
            $request->file('imagem')->move($destination, $imageName);

            // Remove imagem antiga, se existir
            if ($banner->imagem && file_exists(public_path($banner->imagem))) {
                unlink(public_path($banner->imagem));
            }

            $banner->imagem = 'assets/img/banners/' . $imageName;
        }

        $banner->save();

        return redirect()->route('banners.index')->with('sucesso', 'Banner atualizado com sucesso!');
    }

    /**
     * Remove um banner e apaga o arquivo físico
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->imagem && file_exists(public_path($banner->imagem))) {
            unlink(public_path($banner->imagem));
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('sucesso', 'Banner removido com sucesso!');
    }
}
