<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\PurchaseImportStaging; // Importa o novo modelo
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // A sessão ainda será usada para o batch_identifier
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseImportController extends Controller
{
  /**
   * Exibe o formulário de upload da planilha e lista importações pendentes.
   */
  public function showImportForm(Request $request)
  {
    $batchIdentifier = $request->query('batch_identifier'); // Pega o ID do batch da URL

    $stagingData = collect();
    $importErrors = collect();
    $showApprovalSection = false;

    // Se houver um batch_identifier na URL, tenta carregar os dados de stage
    $stagingRecords = new PurchaseImportStaging();

    if ($batchIdentifier) {
      $stagingRecords = $stagingRecords->where('batch_identifier', $batchIdentifier);
    }

    $stagingRecords = $stagingRecords->where('user_id', Auth::id()) // Apenas as importações do usuário logado
      ->paginate(perPage: 5);

    if ($stagingRecords->isNotEmpty()) {
      // Separa os dados válidos dos erros
      $stagingData = $stagingRecords->where('is_valid', true);
      $importErrors = $stagingRecords->where('is_valid', false);

      // Mostra a seção de aprovação se houver dados válidos e nenhum erro (todas as linhas erradas já foram registradas)
      // ou se as linhas válidas existem e não há erros de validação para aquele batch.
      if ($stagingData->isNotEmpty()) {
        $showApprovalSection = true;
      }
    } else {
      Session::flash('error', 'Lote de importação não encontrado ou você não tem permissão para acessá-lo.');
    }

    // Recupera mensagens da sessão (upload success/error)
    $sessionSuccess = Session::get('success');
    $sessionError = Session::get('error');

    return view('content.purchase.import.form', [
      'imported_data_preview' => $stagingData,
      'import_errors' => $importErrors,
      'staging_records' => $stagingRecords,
      'show_approval_section' => $showApprovalSection,
      'session_success' => $sessionSuccess,
      'session_error' => $sessionError,
      'current_batch_identifier' => $batchIdentifier // Passa para a view manter o contexto
    ]);
  }

  /**
   * Processa o upload do arquivo CSV, lê, valida os dados e salva no stage.
   */
  public function upload(Request $request)
  {
    $request->validate([
      'excel_file' => 'required|mimes:csv,txt|max:10240',
    ], [
      'excel_file.required' => 'Por favor, selecione um arquivo CSV para upload.',
      'excel_file.mimes' => 'O arquivo deve ser do tipo CSV.',
      'excel_file.max' => 'O arquivo CSV não pode exceder 10MB.',
    ]);

    $file = $request->file('excel_file');
    $filePath = $file->getRealPath();

    $batchIdentifier = uniqid('import_'); // Identificador único para este lote de importação
    $totalValidRecords = 0;
    $totalInvalidRecords = 0;
    $globalErrors = []; // Erros que afetam a planilha inteira (ex: cabeçalhos)

    // Limpa qualquer dado de stage antigo do usuário logado antes de processar um novo batch
    // Ou você pode ter uma política de expiração para batches antigos
    // PurchaseImportStaging::where('user_id', Auth::id())->delete(); // Descomente se quiser limpar a cada novo upload

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
      $headers = fgetcsv($handle, 1000, ',');
      if ($headers === FALSE) {
        return redirect()->back()->with('error', 'Não foi possível ler o cabeçalho do arquivo CSV. Verifique se o arquivo está vazio ou corrompido.');
      }

      $headers = array_map(function ($header) {
        return mb_strtolower(trim(str_replace(' ', '_', $header)));
      }, $headers);

      $expectedHeaders = [
        'gambler_name',
        'numbers',
        'game_id',
        'seller_identifier',
        'gambler_phone'
      ];

      foreach ($expectedHeaders as $expectedHeader) {
        if ($expectedHeader === 'gambler_phone') continue;
        if (!in_array($expectedHeader, $headers)) {
          return redirect()->back()->with('error', "O arquivo CSV está faltando a coluna obrigatória: '{$expectedHeader}'. Por favor, verifique o cabeçalho da planilha.");
        }
      }

      $headerMap = array_flip($headers);
      $rowNumber = 1; // Contador de linha do CSV

      while (($rowData = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $rowNumber++; // Próxima linha de dados
        $currentOriginalData = [];
        foreach ($headerMap as $headerName => $index) {
          $currentOriginalData[$headerName] = $rowData[$index] ?? null;
        }

        $rowHasErrors = false;
        $rowValidationMessages = [];

        // ------------------ Validação Laravel ------------------
        $validator = Validator::make($currentOriginalData, [
          'gambler_name' => 'required|string|max:255',
          'numbers' => 'required|string',
          'game_id' => 'required|integer',
          'seller_identifier' => 'required|string',
          'gambler_phone' => 'nullable|string|max:20',
        ], [
          'gambler_name.required' => 'O nome do apostador é obrigatório.',
          'numbers.required' => 'Os números da aposta são obrigatórios.',
          'game_id.required' => 'O Game ID é obrigatório.',
          'game_id.integer' => 'O Game ID deve ser um número inteiro.',
          'seller_identifier.required' => 'O identificador do vendedor é obrigatório.',
        ]);

        if ($validator->fails()) {
          $rowValidationMessages = array_merge($rowValidationMessages, $validator->errors()->all());
          $rowHasErrors = true;
        }

        // ------------------ Validações de Lógica de Negócio ------------------
        $game = null;
        $seller = null;

        // Validação de numbers
        if (!empty($currentOriginalData['numbers'])) {
          $numbersArray = explode(' ', $currentOriginalData['numbers']);
          if (count($numbersArray) > 11) {
            $rowValidationMessages[] = "A aposta só pode ter no máximo 11 números. Encontrados: " . count($numbersArray) . ".";
            $rowHasErrors = true;
          }
          foreach ($numbersArray as $num) {
            if (!is_numeric($num) || $num < 0 || $num > 99) {
              $rowValidationMessages[] = "O número '{$num}' é inválido. Apenas números de 0 a 99 são permitidos.";
              $rowHasErrors = true;
              break;
            }
          }
        } else {
          $rowValidationMessages[] = "Os números da aposta não foram fornecidos ou estão vazios.";
          $rowHasErrors = true;
        }


        // Validação de game_id
        if (isset($currentOriginalData['game_id']) && is_numeric($currentOriginalData['game_id'])) {
          $game = Game::find($currentOriginalData['game_id']);
          if (!$game) {
            $rowValidationMessages[] = "O Game ID '{$currentOriginalData['game_id']}' não existe.";
            $rowHasErrors = true;
          } elseif ($game->status != 'OPENED') {
            $rowValidationMessages[] = "O jogo '{$currentOriginalData['game_id']}' (ID) não está aberto para novas apostas. Status atual: {$game->status}.";
            $rowHasErrors = true;
          }
        } elseif (!isset($currentOriginalData['game_id'])) {
          $rowValidationMessages[] = "O Game ID é obrigatório.";
          $rowHasErrors = true;
        }


        $seller = NULL;
        // Validação e busca do seller_id
        if (!empty($currentOriginalData['seller_identifier'])) {
          $seller = User::where('email', $currentOriginalData['seller_identifier'])
            ->orWhere('document', $currentOriginalData['seller_identifier'])
            ->first();
          if (!$seller) {
            $rowValidationMessages[] = "Vendedor com identificador '{$currentOriginalData['seller_identifier']}' não encontrado no sistema.";
            $rowHasErrors = true;
          } elseif (!in_array($seller->role->level_id, ['seller', 'admin'])) {
            $rowValidationMessages[] = "O usuário '{$currentOriginalData['seller_identifier']}' não tem permissão para ser um vendedor (role atual: {$seller->role->name}).";
            $rowHasErrors = true;
          }
        } else {
          $rowValidationMessages[] = "O identificador do vendedor (seller_identifier) é obrigatório.";
          $rowHasErrors = true;
        }

        // Salva a linha no stage
        PurchaseImportStaging::create([
          'user_id' => Auth::id(),
          'batch_identifier' => $batchIdentifier,
          'gambler_name' => $currentOriginalData['gambler_name'] ?? null,
          'gambler_phone' => $currentOriginalData['gambler_phone'] ?? null,
          'numbers' => $currentOriginalData['numbers'] ?? null,
          'game_id' => $game->id ?? null,
          'seller_id' => $seller->id ?? null,
          'original_data' => $currentOriginalData,
          'validation_errors' => $rowHasErrors ? $rowValidationMessages : null,
          'is_valid' => !$rowHasErrors,
          'imported_at' => now(),
        ]);

        if ($rowHasErrors) {
          $totalInvalidRecords++;
        } else {
          $totalValidRecords++;
        }
      }
      fclose($handle);
    } else {
      return redirect()->back()->with('error', 'Não foi possível abrir o arquivo CSV. Verifique as permissões ou se o arquivo está bloqueado.');
    }

    if ($totalValidRecords == 0 && $totalInvalidRecords > 0) {
      Session::flash('error', 'Nenhum dado válido encontrado para importação. Por favor, corrija os erros na planilha e tente novamente.');
    } else if ($totalValidRecords > 0 && $totalInvalidRecords > 0) {
      Session::flash('success', "Planilha processada com sucesso. Foram encontradas {$totalValidRecords} apostas válidas e {$totalInvalidRecords} com erros. Revise os dados e aprove a importação.");
    } else {
      Session::flash('success', "Planilha processada com sucesso. Foram encontradas {$totalValidRecords} apostas válidas. Revise os dados e aprove a importação.");
    }


    // Redireciona para a mesma página com o batch_identifier
    return redirect()->route('purchases.import.form', ['batch_identifier' => $batchIdentifier]);
  }

  /**
   * Aprova e persiste as apostas no banco de dados a partir dos dados em stage.
   */
  public function approve(Request $request)
  {
    $batchIdentifier = $request->input('batch_identifier');

    if (!$batchIdentifier) {
      return redirect()->route('purchases.import.form')->with('error', 'Lote de importação não especificado.');
    }

    // Pega todos os registros válidos do batch específico
    $stagingRecords = PurchaseImportStaging::where('batch_identifier', $batchIdentifier)
      ->where('user_id', Auth::id())
      ->where('is_valid', true)
      ->get();

    // Verifica se há registros inválidos no mesmo batch
    $invalidRecordsInBatch = PurchaseImportStaging::where('batch_identifier', $batchIdentifier)
      ->where('user_id', Auth::id())
      ->where('is_valid', false)
      ->exists();

    if ($invalidRecordsInBatch) {
      return redirect()->route('purchases.import.form', ['batch_identifier' => $batchIdentifier])->with('error', 'Existem erros na planilha que precisam ser corrigidos antes da importação. Não é possível aprovar.');
    }

    if ($stagingRecords->isEmpty()) {
      return redirect()->route('purchases.import.form')->with('error', 'Nenhum dado válido para aprovar neste lote. Por favor, carregue uma planilha com dados válidos primeiro.');
    }

    DB::beginTransaction();
    try {
      $totalPurchases = 0;
      $totalAmount = 0;

      foreach ($stagingRecords as $data) {
        // Recupera o game e o seller novamente para garantir que ainda existem e estão consistentes
        $game = Game::find($data->game_id);
        $seller = User::find($data->seller_id);
        $importerUser = User::find($data->user_id); // O usuário que importou

        if (!$game || !$seller || !$importerUser) {
          // Isso pode indicar que algum dado foi deletado entre o upload e a aprovação
          throw new \Exception("Dados inconsistentes para a linha do lote '{$data->id}'. Jogo, Vendedor ou Usuário importador não encontrado.");
        }

        // Cálculo do preço da aposta
        $purchasePrice = $game->price * $data->quantity;

        // Cria a Purchase final
        $purchase = Purchase::create([
          "gambler_name" => $data->gambler_name,
          "gambler_phone" => $data->gambler_phone,
          "numbers" => $data->numbers,
          "quantity" => $data->quantity,
          "price" => $purchasePrice,
          "status" => "PAID", // Inicialmente PAID, pode ser ajustado
          "game_id" => $data->game_id,
          "identifier" => generate_identifier(),
          "round" => $game->round, // Pega o round do jogo atual
          "paid_by_user_id" => $importerUser->id, // Quem importou, paga ou é responsável
          "user_id" => $importerUser->id, // O usuário que importou é o "dono" da compra no sistema
          "seller_id" => $data->seller_id,
        ]);

        // Lógica de débito do crédito do usuário que importou
        if ($importerUser->game_credit < $purchasePrice) {
          $purchase->status = "PENDING"; // Marca como pendente se não tiver crédito
          $purchase->save();
          Transactions::create(
            [
              "type" => 'PURCHASE_PENDING_NO_CREDIT',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $purchasePrice,
              "user_id" => $importerUser->id,
              "description" => "Aposta importada via lote com crédito insuficiente para débito automático.",
            ]
          );
        } else {
          $importerUser->game_credit -= $purchasePrice;
          $importerUser->save();
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $purchasePrice,
              "user_id" => $importerUser->id,
            ]
          );
        }

        // Lógica de comissionamento para o vendedor
        $comission = $purchasePrice * $seller->comission_percent;
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

        // Marca o registro de stage como aprovado
        $data->is_approved = true;
        $data->save();

        $totalPurchases++;
        $totalAmount += $purchasePrice;
      }

      DB::commit();

      // Opcional: deletar registros aprovados do stage (ou mantê-los para auditoria)
      // PurchaseImportStaging::where('batch_identifier', $batchIdentifier)->delete();


      return redirect()->route('purchases.import.form')->with('success', "{$totalPurchases} apostas do lote '{$batchIdentifier}' importadas com sucesso, totalizando R$ " . number_format($totalAmount, 2, ',', '.') . ".");
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->route('purchases.import.form', ['batch_identifier' => $batchIdentifier])->with('error', 'Erro ao importar as apostas: ' . $e->getMessage());
    }
  }

  public function deleteBatch(Request $request)
  {
    $batchIdentifier = $request->input('batch_identifier');

    if (!$batchIdentifier) {
      return redirect()->back()->with('error', 'Lote de importação não especificado para deleção.');
    }

    PurchaseImportStaging::where('batch_identifier', $batchIdentifier)
      ->where('user_id', Auth::id())
      ->delete();

    return redirect()->route('purchases.import.form')->with('success', "Lote '{$batchIdentifier}' deletado com sucesso do stage.");
  }

  /**
   * Helper function: certifique-se de que essa função esteja disponível globalmente.
   */
  // Se não existir, pode adicionar aqui ou em app/Helpers/functions.php
  // if (!function_exists('generate_identifier')) {
  //     function generate_identifier()
  //     {
  //         return uniqid('PUR_');
  //     }
  // }
}
