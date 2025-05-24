<div class="tab-pane fade {{ $tab == 'tab-bet-form' ? 'show active' : '' }}" id="bet-form" role="tabpanel"
    aria-labelledby="bet-form-tab">
    @if ($game->status == 'OPENED')
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="bet_form" action="{{ route('purchase-store', $game->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="gambler_name">Nome do Apostador</label>
                        <input type="text" class="form-control" id="gambler_name" name="gambler_name"
                            placeholder="Digite seu nome" required>
                    </div>

                    <div class="form-group">
                        <label for="gambler_phone">Telefone do Apostador</label>
                        <input type="text" class="form-control" id="gambler_phone" name="gambler_phone"
                            placeholder="Digite seu telefone">
                    </div>

                    @if (auth()->user()->role->level_id == 'admin')
                        <div class="form-group">
                            <label for="seller_id">Local do jogo</label>
                            <select class="form-control" name="seller_id">
                                <option value="{{ auth()->user()->id }}"
                                    {{ old('seller_id') == auth()->user()->id ? 'selected' : '' }}>Banca Central
                                </option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}"
                                        {{ old('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if (auth()->user()->role->level_id == 'gambler')
                        @if (auth()->user()->invited_by_id)
                            <input type="hidden" name="seller_id"
                                value="{{ \App\Models\User::find(auth()->user()->invited_by_id)->id }}">
                        @else
                            <input type="hidden" name="seller_id"
                                value="{{ \App\Models\User::where('role_user_id', 1)->first()->id }}">
                        @endif
                    @endif

                    @if (auth()->user()->role->level_id == 'seller')
                        <input type="hidden" name="seller_id" value="{{ auth()->user()->id }}">
                    @endif

                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                    <div class="form-group">
                        <label>Método de Seleção</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selection_method" id="use_text"
                                value="text" checked>
                            <label class="form-check-label" for="use_text">Inserir dezenas manualmente</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selection_method" id="use_grid"
                                value="grid">
                            <label class="form-check-label" for="use_grid">Usar grade interativa</label>
                        </div>
                    </div>

                    <div class="form-group" id="text_input_container">
                        <label for="manual_numbers">Digite suas dezenas (máximo de 11 números, ex: 11 22 33 44):</label>
                        <input type="text" inputmode="numeric" class="form-control" id="manual_numbers"
                            placeholder="Ex: 11 22 33 10 99" maxlength="32">
                        <small id="error-message1" class="text-danger" style="display: none;"></small>
                        <small class="form-text text-muted">Insira até 11 dezenas separadas por espaço.</small>
                    </div>

                    <div class="card" id="grid_input_container" style="display: none;">
                        <div class="card-body">
                            <label>Escolha suas dezenas (máximo de 11)</label>
                            <div
                                class="number-grid mb-3 row row-cols-5 row-cols-sm-6 row-cols-md-7 row-cols-lg-10 gx-1 gy-1">
                                @for ($i = 0; $i <= 99; $i++)
                                    <div class="col">
                                        <button type="button"
                                            class="btn btn-outline-primary w-100 number-button btn-sm"
                                            data-number="{{ $i }}">
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                        </button>
                                    </div>
                                @endfor
                            </div>
                            <small class="form-text text-muted">Selecione até 11 números. Clique novamente em um número
                                para desmarcá-lo.</small>
                            <div id="error-message2" class="text-danger mt-2" style="display: none;"></div>
                        </div>
                    </div>

                    <input type="hidden" name="game_id" value="{{ $game->id }}">
                    <input type="hidden" id="numbers" name="numbers" value="">

                    <button type="submit" class="btn btn-primary">Comprar Dezenas</button>
                </form>
            </div>
        </div>
    @else
        <p class="text-muted">O jogo já está encerrado.</p>
    @endif
</div>
