# SGOC ERP Architecture v1.0

## 1. Classificação obrigatória dos Models

### Cadastro mestre — `BaseModel`
Entidades parametrizáveis que podem ser desativadas e restauradas. Utilizam `SoftDeletes`.

Exemplos: empresas, filiais, obras, produtos, fornecedores, ativos, almoxarifados e cadastros auxiliares.

### Documento ou fato operacional — `TransactionModel`
Registros que representam fatos do negócio. Não utilizam `SoftDeletes`.

Exemplos: RQ, OC, cotação, solicitação de manutenção, OS, movimentação, reserva, requisição de estoque, workflow e anexos operacionais.

Regra: documento emitido não é apagado; deve ser cancelado, rejeitado ou estornado.

### Saldo ou consolidação — `SnapshotModel`
Representa o estado corrente derivado. Não utiliza `SoftDeletes`.

Exemplos: saldo de estoque, consolidação financeira e indicadores materializados.

## 2. Regras de domínio

- Resources do Filament cuidam de interface, não de regra de negócio.
- Escritas transacionais devem ocorrer em Services ou Actions.
- Atualizações de saldo usam transação e bloqueio pessimista.
- Correções de lançamentos usam estorno, nunca exclusão.
- Integrações entre módulos devem preservar IDs de origem e rastreabilidade.
- Valores monetários usam decimal no banco e máscara pt-BR somente na interface.
- UUID é o identificador padrão.

## 3. Organização

```text
app/Modules/<Domain>/
├── Application/
│   ├── Actions/
│   ├── DTOs/
│   └── Services/
├── Domain/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   └── Models/
└── Infrastructure/
```

## 4. Auditoria

Todo documento operacional deve registrar usuário, data/hora, ação, origem, estado anterior e estado posterior. A auditoria não substitui status de cancelamento ou movimentação de estorno.

## 5. Verificação

Execute:

```bash
php artisan sgoc:architecture-status
php artisan test --filter=ModelLifecycleArchitectureTest
```
