# Testes Automatizados - Bolão Laravel

Este diretório contém testes automatizados abrangentes para todos os fluxos importantes da aplicação.

## Estrutura dos Testes

### 1. UserRegistrationTest.php
Testa o cadastro de usuários:
- Cadastro de apostador sem indicação
- Cadastro de apostador com indicação de vendedor
- Cadastro de apostador com indicação de outro apostador
- Criação de vendedor pelo admin
- Criação de apostador vinculado a vendedor pelo admin

### 2. ReferEarnFlowTest.php
Testa o fluxo completo de "Indique e Ganhe":
- Criação de ReferEarn no cadastro
- Marcação de compra realizada na primeira compra
- Pagamento de bônus de indicação
- Criação de transação ao pagar bônus
- Validação de que bônus só é dado na primeira compra

### 3. PurchaseAndCommissionTest.php
Testa compras e comissões:
- Criação de compra pelo apostador
- Comissão do vendedor quando apostador paga
- Vendedor pagando compra do apostador
- Criação de transações de pagamento
- Múltiplas compras acumulando comissões

### 4. AwardsAndResultsTest.php
Testa prêmios e resultados:
- Criação de prêmios quando números coincidem
- Pagamento de prêmio e criação de transação
- Divisão de prêmio entre múltiplos ganhadores
- Cálculo de resultados do jogo

### 5. TransactionsSummaryTest.php
Testa o resumo de transações (fluxo completo):
- Inclusão de todos os tipos de transação no resumo
- Cálculo correto de totais (entradas e saídas)
- Agrupamento de compras por jogo
- Agrupamento de comissões do vendedor
- Validação completa do fluxo end-to-end

## Como Executar os Testes

### Executar todos os testes:
```bash
php artisan test
```

### Executar um arquivo específico:
```bash
php artisan test tests/Feature/UserRegistrationTest.php
```

### Executar um teste específico:
```bash
php artisan test --filter test_can_register_gambler_without_referral
```

### Executar com cobertura:
```bash
php artisan test --coverage
```

## Fluxos Testados

### Fluxo Completo End-to-End

1. **Cadastro de Apostador com Indicação**
   - Vendedor indica apostador
   - Apostador se cadastra
   - ReferEarn é criado
   - Apostador faz primeira compra
   - ReferEarn é marcado como comprado
   - Bônus de indicação é pago
   - Transação REFER_EARN é criada

2. **Compra e Comissão**
   - Apostador faz compra
   - Vendedor recebe comissão
   - Transações PAY_PURCHASE e PAY_PURCHASE_COMISSION são criadas
   - Créditos são atualizados corretamente

3. **Prêmios**
   - Jogo é sorteado
   - Prêmios são calculados
   - Prêmios são pagos
   - Transação PAY_AWARD é criada

4. **Resumo de Transações**
   - Todas as transações aparecem no resumo
   - Totais são calculados corretamente
   - Agrupamentos funcionam corretamente

## Observações Importantes

- Todos os testes usam `RefreshDatabase` para garantir isolamento
- Os testes não modificam o código de produção
- Cada teste é independente e pode ser executado isoladamente
- Os testes cobrem tanto cenários de sucesso quanto validações de regras de negócio

## Estrutura de Dados de Teste

Os testes criam automaticamente:
- Roles (admin, seller, gambler)
- Usuários de teste
- Jogos de teste
- Compras de teste
- Transações de teste

Todos os dados são limpos após cada teste graças ao `RefreshDatabase`.

