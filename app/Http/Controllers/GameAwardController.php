<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameAward;
use Illuminate\Http\Request;

class GameAwardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GameAward $gameAward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GameAward $gameAward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAwards(Request $request, Game $game)
    {
        $awardsData = $request->input('awards', []);
    
        foreach ($awardsData as $awardData) {
            if (isset($awardData['id'])) {
                // Atualizar prêmio existente
                $award = GameAward::findOrFail($awardData['id']);
                $award->update([
                    'condition_type' => $awardData['condition_type'],
                    'minimum_point_value' => $awardData['minimum_point_value'] ?? null,
                    'amount' => $awardData['amount'],
                ]);
            } else {
                // Criar novo prêmio
                $game->awards()->create([
                    'condition_type' => $awardData['condition_type'],
                    'minimum_point_value' => $awardData['minimum_point_value'] ?? null,
                    'amount' => $awardData['amount'],
                ]);
            }
        }
    
        return response()->json(['message' => 'Prêmios atualizados com sucesso']);
    }
    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GameAward $gameAward)
    {
        //
    }
}
