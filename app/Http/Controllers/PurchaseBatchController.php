<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\PurchaseBatch;
use App\Models\PurchaseBatchItems;
use App\Models\PurchaseBatchItemMessage; // Importe a nova Model
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PurchaseBatchController extends Controller
{
  /**
   * Exibe o formulário para importação de compras em lote.
   *
   * @return \Illuminate\View\View
   */
  public function importForm(Request $request, $game_id)
  {
    // Busca todos os jogos disponíveis para o select box
    $game = Game::find($game_id);

    $purchaseBatches = PurchaseBatch::where('user_id', Auth::user()->id)->paginate(10);
    // Define a aba ativa para 'tab-import-batch'
    session()->flash('tab', 'tab-import-batch');
    $tab = 'tab-import-batch';

    // Retorna a view com os dados necessários
    return view('content.purchase.import.view_batch', compact('game', 'tab', 'purchaseBatches'));
  }

  /**
   * Armazena as compras importadas de um arquivo CSV em lote.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function storeBatch(Request $request)
  {
    // 1. Validação inicial dos dados do formulário
    $validator = Validator::make($request->all(), [
      'description' => 'nullable|string|max:255', // Descrição opcional
      'batch_file' => 'required|file|mimes:csv,txt|max:5120', // Arquivo CSV/TXT, máximo 5MB
      'game_id' => 'required|exists:games,id', // Jogo relacionado
    ], [
      'batch_file.required' => 'O campo Arquivo CSV é obrigatório.',
      'batch_file.file' => 'O campo Arquivo CSV deve ser um arquivo.',
      'batch_file.mimes' => 'O arquivo deve ser do tipo CSV ou TXT.',
      'batch_file.max' => 'O arquivo CSV não pode ter mais de 5MB.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput()->with('tab', 'tab-import-batch');
    }

    $file = $request->file('batch_file');
    $filePath = $file->getRealPath();

    // 2. Leitura e parse do arquivo CSV
    //$data = array_map('str_getcsv', file($filePath));
    $data = array_map(fn($line) => str_getcsv($line, ';'), file($filePath));
    $data[0][0] = str_replace("\u{FEFF}", '', $data[0][0]);

    // Pega a primeira linha como cabeçalho e remove-a dos dados
    $header = array_shift($data);

    $translation_cols = [
      'Nome do Apostador' => 'gambler_name',
      'Telefone do Apostador' => 'gambler_phone',
      'Números Apostados' => 'numbers',
      'Preço' => 'price',
      'Vendedor' => 'seller_id'
    ];

    // Define as colunas esperadas no CSV e suas regras de validação
    $expectedHeaders = [
      'gambler_name',
      'gambler_phone',
      'numbers',
      'price',
      'seller_id'
      //'identifier',
      //'round',
      //'status'
    ];

    // Mapeia os cabeçalhos com o as colunas esperadas
    $header = array_map(function ($h) use ($translation_cols) {
      return $translation_cols[$h] ?? $h;
    }, $header);
    $header = array_values($header); // Remove chaves numéricas para evitar problemas de comparação

    // Verifica se todos os cabeçalhos esperados estão presentes no arquivo
    if (count(array_intersect($expectedHeaders, $header)) !== count($expectedHeaders)) {
      $missingHeaders = array_diff($expectedHeaders, $header);
      $missingHeaders = array_map(function ($h) use ($translation_cols) {
        return array_flip($translation_cols)[$h] ?? $h; // Inverte o array para obter o nome original
      }, $missingHeaders);

      return redirect()->back()->withInput()->with('error', 'O arquivo CSV não possui todas as colunas necessárias: ' . implode(', ', $missingHeaders) . '. Verifique o cabeçalho do arquivo.')->with('tab', 'tab-import-batch');
    }

    // Inicia uma transação de banco de dados para garantir atomicidade
    DB::beginTransaction();
    try {
      // 3. Criação do registro PurchaseBatch (o lote principal)
      $game = Game::find($request->input('game_id'));
      $purchaseBatch = PurchaseBatch::create([
        'description' => $request->input('description'),
        'status' => 'pending', // Status inicial do lote
        'round' => $game->round,
        'user_id' => auth()->id(), // Usuário que está importando (admin)
        'game_id' => $request->input('game_id'),
      ]);

      $failedItemsCount = 0; // Contador de itens que falharam na validação
      $validItems = [];
      $invalidItemsData = []; // Para itens que falharam na validação, mas queremos registrar

      $line = 1; // Contador de linha para mensagens de erro

      // 4. Itera sobre os dados do CSV para separar itens válidos e inválidos
      foreach ($data as $row) {
        $line++; // Incrementa o contador de linha para cada linha de dados
        // Pula linhas vazias ou com número de colunas inconsistente
        if (empty(array_filter($row)) || count($row) != count($header)) {
          continue; // Pula para a próxima linha
        }

        // Combina o cabeçalho com a linha atual para criar um array associativo
        $itemData = array_combine($header, $row);

        // Validação individual para cada item do CSV
        $itemValidator = Validator::make($itemData, [
          'gambler_name' => 'required|string|max:255',
          'gambler_phone' => 'nullable|string|max:20',
          'numbers' => 'required|string|max:255', // Ex: "11 22 33"
          'price' => 'required|numeric|min:0',
          'seller_id' => 'required|exists:users,id', // Verifica se o vendedor existe
          //'status' => 'nullable|string|max:50', // Ex: 'pending', 'paid'
          //'identifier' => 'nullable|string|max:255|unique:purchase_batch_items,identifier', // Identificador único por item, se fornecido
          //'round' => 'nullable|integer',
        ], [
          'gambler_name.required' => "Nome do apostador é obrigatório.",
          'numbers.required' => "Números são obrigatórios.",
          'price.required' => "Preço é obrigatório.",
          'price.numeric' => "Preço deve ser um valor numérico.",
          'price.min' => "Preço deve ser maior ou igual a 0.",
          'seller_id.required' => "Vendedor é obrigatório.",
          'seller_id.exists' => "O vendedor selecionado não existe.",
          //'identifier.unique' => "O identificador '{$itemData['identifier']}' já existe.",
        ]);

        // verifica se não há uma aposta com os mesmos numeros e mesmo apostador para esse mesmo jogo
        $itemValidator->after(function ($validator) use ($itemData, $game) {
          // Verifica se já existe uma aposta com os mesmos números e apostador para o mesmo jogo
          $existingPurchase = Purchase::where('game_id', $game->id)
            ->where('gambler_name', $itemData['gambler_name'])
            ->where('numbers', $itemData['numbers'])
            ->where('game_id', $game->id)
            ->where('round', $game->round)
            ->where('status', 'PAID')
            ->first();

          if ($existingPurchase) {
            $validator->errors()->add('numbers', "Já existe uma aposta com os mesmos números '{$itemData['numbers']}' para o apostador '{$itemData['gambler_name']}' neste jogo.");
          }
        });

        if ($itemValidator->fails()) {
          $failedItemsCount++;
          $errorMessage = "Erro de validação na linha {$line}: " . $itemValidator->errors()->first();
          $invalidItemsData[] = [
            'data' => $itemData,
            'message' => $errorMessage,
          ];
        } else {
          $validItems[] = [
            'purchase_batch_id' => $purchaseBatch->id,
            'gambler_name' => $itemData['gambler_name'],
            'gambler_phone' => $itemData['gambler_phone'] ?? null,
            'numbers' => $itemData['numbers'],
            'quantity' => 1,
            'price' => $itemData['price'],
            'status' => 'pending',
            'game_id' => $game->id,
            'identifier' => "BATCH_{$purchaseBatch->id}_" . generate_identifier(),
            'round' => $game->round,
            'paid_by_user_id' => $itemData['seller_id'],
            'user_id' => $purchaseBatch->user_id,
            'seller_id' => $itemData['seller_id'],
            'created_at' => now(),
            'updated_at' => now(),
          ];
        }
      }

      // 5. Inserir itens válidos em massa
      if (!empty($validItems)) {
        PurchaseBatchItems::insert($validItems);
      }

      // 6. Processar e registrar itens inválidos individualmente
      foreach ($invalidItemsData as $invalidItem) {
        $originalData = $invalidItem['data'];
        $errorMessage = $invalidItem['message'];

        // Cria um item com status 'failed' para registrar o erro
        $failedItem = PurchaseBatchItems::create([
          'purchase_batch_id' => $purchaseBatch->id,
          'gambler_name' => $originalData['gambler_name'] ?? 'N/A', // Usar N/A para campos obrigatórios que falharam
          'gambler_phone' => $originalData['gambler_phone'] ?? null,
          'numbers' => $originalData['numbers'] ?? 'N/A',
          'quantity' => 1,
          'price' => $originalData['price'] ?? 0.00,
          'status' => 'error', // Marcar como falho
          'game_id' => $game->id,
          'identifier' => "BATCH_{$purchaseBatch->id}_ERROR_" . generate_identifier(),
          'round' => $originalData['round'] ?? ($purchaseBatch->round ?? 1),
          'paid_by_user_id' => $itemData['seller_id'],
          'user_id' => $purchaseBatch->user_id,
          'seller_id' => $itemData['seller_id'],
        ]);

        // Registra a mensagem de erro associada a este item
        PurchaseBatchItemMessage::create([
          'purchase_batch_item_id' => $failedItem->id,
          'message' => $errorMessage,
          'type' => 'error',
        ]);
      }

      $totalItemsProcessed = count($validItems) + count($invalidItemsData);
      if ($totalItemsProcessed == 0) {
        throw new \Exception("Nenhum item válido ou inválido encontrado no arquivo CSV para importação.");
      }

      // Confirma a transação se tudo ocorreu bem
      DB::commit();

      $message = "Lote de compras importado com sucesso! ";
      if ($failedItemsCount > 0) {
        $message .= "No entanto, {$failedItemsCount} item(s) falharam na validação e foram registrados com status 'failed'.";
        return redirect()->route('purchases.import.show', $purchaseBatch->id)->with('warning', $message)->with('tab', 'tab-batch-list');
      } else {
        return redirect()->route('purchases.import.show', $purchaseBatch->id)->with('success', $message)->with('tab', 'tab-batch-list');
      }
    } catch (\Exception $e) {
      // Reverte a transação em caso de qualquer outro erro (não de validação de item individual)
      DB::rollBack();
      return redirect()->back()->withInput()->with('error', 'Erro ao importar lote: ' . $e->getMessage())->with('tab', 'tab-import-batch');
    }
  }

  public function approve(Request $request, $purchaseBatchId)
  {
    // Busca o lote de compras pelo ID
    $purchaseBatch = PurchaseBatch::findOrFail($purchaseBatchId);

    // Verifica se o usuário autenticado é o dono do lote ou um administrador
    if (Auth::user()->id != $purchaseBatch->user_id) {
      return redirect()->back()->with('error', 'Você não tem permissão para aprovar este lote.');
    }

    // Verifica se o lote já está aprovado
    if ($purchaseBatch->status === 'approved') {
      return redirect()->back()->with('info', 'Este lote já está aprovado.');
    }

    $items = $purchaseBatch->items;
    foreach ($items as $item) {

      if ($item->status == 'imported' || $item->status == 'error') {
        continue;
      }

      $purchase = Purchase::create([
        'gambler_name' => $item->gambler_name,
        'gambler_phone' => $item->gambler_phone,
        'numbers' => $item->numbers,
        'quantity' => $item->quantity,
        'price' => $item->price,
        'status' => 'PAID',
        'game_id' => $purchaseBatch->game_id,
        'identifier' => $item->identifier,
        'round' => $purchaseBatch->round,
        'paid_by_user_id' => $item->paid_by_user_id,
        'user_id' => $purchaseBatch->user_id,
        'seller_id' => $item->seller_id,
      ]);

      $seller = User::find($item->seller_id);
      if ($seller->role->level_id == 'seller') {
        $comission = $purchase->price * $seller->comission_percent;
        Transactions::create(
          [
            "type" => 'PAY_PURCHASE_COMISSION',
            "game_id" => $purchase->game_id,
            "purchase_id" => $purchase->id,
            "amount" => $comission,
            "user_id" => $seller->id,
          ]
        );
        $seller->game_credit = $seller->game_credit + $comission;
        $seller->save();
      }

      Transactions::create(
        [
          "type" => 'PAY_PURCHASE',
          "game_id" => $purchase->game_id,
          "purchase_id" => $purchase->id,
          "amount" => $purchase->price,
          "user_id" => $item->paid_by_user_id,
        ]
      );


      $item->status = 'imported'; // Marca o item como pago
      $item->save();

      PurchaseBatchItemMessage::create([
        'purchase_batch_item_id' => $item->id,
        'message' => "Aposta aprovada e importada com sucesso.",
        'type' => 'success',
      ]);
    }


    // Atualiza o status do lote para 'approved'
    $purchaseBatch->status = 'approved';
    $purchaseBatch->save();

    return redirect()->route('purchases.import.show', $purchaseBatch->id)->with('success', 'Lote aprovado com sucesso!')->with('tab', 'tab-batch-list');
  }
  /**
   * Exibe a lista de lotes de compras importados.
   *
   * @param  \App\Models\Game  $game
   * @return \Illuminate\View\View
   */
  public function index(Game $game)
  {
    // Busca todos os lotes de compra para o jogo específico, com seus relacionamentos
    $purchaseBatches = PurchaseBatch::where('game_id', $game->id)
      ->with(['game', 'user', 'seller', 'items'])
      ->latest() // Ordena pelos mais recentes
      ->get();

    // Define a aba ativa para 'tab-batch-list'
    session()->flash('tab', 'tab-batch-list');

    // Retorna a view principal do jogo com os dados necessários para todas as abas
    return view('purchases.import.show', compact('game', 'purchaseBatches'));
  }

  /**
   * Exibe os detalhes de um lote de compra específico.
   * Você pode expandir isso para mostrar os itens dentro do lote.
   *
   * @param  \App\Models\PurchaseBatch  $purchaseBatch
   * @return \Illuminate\View\View
   */
  public function show(Request $request, $purchaseBatchId)
  {

    $status_translate = [
      'pending' => 'Pendente',
      'approved' => 'Aprovado',
      'imported' => 'Importado',
      'error' => 'Erro',
    ];
    // Carrega os itens associados ao lote, e os relacionamentos do lote
    // E agora, carrega também as mensagens de cada item
    $purchaseBatch = PurchaseBatch::with(['game', 'user', 'seller', 'paid_by_user'])->find($purchaseBatchId);
    $items = $purchaseBatch->items()->with(['seller', 'messages'])->paginate(10);

    return view('content.purchase.import.details', compact('purchaseBatch', 'status_translate', 'items'));
  }
}
