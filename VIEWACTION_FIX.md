# ✅ Correção: Erro "ViewAction not found"

## 🐛 Erro Original

```
Class "Filament\Tables\Actions\ViewAction" not found
```

---

## 🔧 Causa do Erro

O namespace das **Actions de tabela** no Filament está **incorreto**. 

### Diferença de Namespaces

**Para Table Actions (dentro de tabelas):**
```php
// ❌ ERRADO - Namespace de actions de formulário/página
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

// ✅ CORRETO - Actions gerais que funcionam em tabelas
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
```

**Para Bulk Actions (ações em massa):**
```php
// ✅ CORRETO - Permanecem em Tables\Actions
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
```

---

## 🛠️ Solução Aplicada

### Arquivo Corrigido
`app/Filament/Resources/ProfissionalStudents/Tables/ProfissionalStudentsTable.php`

### Imports Antes (❌ Incorreto)

```php
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;       // ❌ Errado
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;         // ❌ Errado
use Filament\Tables\Actions\ViewAction;         // ❌ Errado
```

### Imports Depois (✅ Correto)

```php
use Filament\Actions\DeleteAction;              // ✅ Correto
use Filament\Actions\EditAction;                // ✅ Correto
use Filament\Actions\ViewAction;                // ✅ Correto
use Filament\Tables\Actions\BulkActionGroup;    // ✅ Mantém
use Filament\Tables\Actions\DeleteBulkAction;   // ✅ Mantém
```

---

## 📚 Referência: Namespaces do Filament

### 1. Actions Gerais (`Filament\Actions\`)

Usadas em:
- ✅ Métodos `actions()` de tabelas
- ✅ Header actions de páginas
- ✅ Infolists

**Classes:**
```php
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;        // Action customizada
```

**Exemplo de uso:**
```php
->actions([
    ViewAction::make(),
    EditAction::make(),
    DeleteAction::make(),
])
```

---

### 2. Table Actions (`Filament\Tables\Actions\`)

Usadas em:
- ✅ Bulk actions (ações em massa)
- ✅ Header actions da tabela

**Classes:**
```php
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\AttachBulkAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
```

**Exemplo de uso:**
```php
->bulkActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
])
```

---

### 3. Form Actions (`Filament\Actions\`)

Usadas em:
- ✅ Formulários (footer actions)

**Classes:**
```php
use Filament\Actions\Action;
```

---

## 📖 Comparação com Outros Resources

### ExercisesTable.php (Correto)

```php
use Filament\Actions\ViewAction;    // ✅ Correto
use Filament\Actions\EditAction;    // ✅ Correto
use Filament\Actions\DeleteAction;  // ✅ Correto

->actions([
    ViewAction::make(),
    EditAction::make(),
    DeleteAction::make(),
])
```

### ProfissionalStudentsTable.php (Corrigido)

```php
use Filament\Actions\ViewAction;    // ✅ Agora correto
use Filament\Actions\EditAction;    // ✅ Agora correto
use Filament\Actions\DeleteAction;  // ✅ Agora correto

->actions([
    ViewAction::make(),
    EditAction::make(),
    DeleteAction::make(),
])
```

---

## ✅ Verificação

### Rotas Funcionando

```bash
php artisan route:list | grep profissional
```

**Resultado:**
```
✅ GET|HEAD  admin/profissional-students              (index)
✅ GET|HEAD  admin/profissional-students/create       (create)
✅ GET|HEAD  admin/profissional-students/{record}     (view)
✅ GET|HEAD  admin/profissional-students/{record}/edit (edit)
```

**Total:** 4 rotas registradas com sucesso!

---

### Servidor Funcionando

O log mostra acesso bem-sucedido:
```
✅ 2026-03-05 18:15:59 /admin/profissional-students ......... ~ 1s
```

---

## 💡 Dica: Como Saber Qual Namespace Usar?

### Regra Geral

| Contexto | Namespace |
|----------|-----------|
| **Row actions** (ações por linha) | `Filament\Actions\` |
| **Header actions** (ações no topo da tabela) | `Filament\Tables\Actions\` |
| **Bulk actions** (ações em massa) | `Filament\Tables\Actions\` |
| **Page header actions** | `Filament\Actions\` |
| **Form actions** | `Filament\Actions\` |

### Exemplo Visual

```php
Table::make()
    ->headerActions([
        // Use Filament\Tables\Actions\Action
        Tables\Actions\Action::make('export')
    ])
    ->actions([
        // Use Filament\Actions\*Action
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ])
    ->bulkActions([
        // Use Filament\Tables\Actions\*BulkAction
        BulkActionGroup::make([
            DeleteBulkAction::make(),
        ]),
    ])
```

---

## 🎉 Status Final

| Item | Status |
|------|--------|
| **Erro corrigido** | ✅ |
| **Imports corrigidos** | ✅ |
| **Rotas funcionando** | ✅ |
| **Servidor rodando** | ✅ |
| **Resource acessível** | ✅ |

---

## 📍 Acesso ao Resource

```
http://localhost:8001/admin/profissional-students
```

---

**Data da correção:** 5 de março de 2026  
**Arquivo corrigido:** `app/Filament/Resources/ProfissionalStudents/Tables/ProfissionalStudentsTable.php`  
**Erro resolvido:** Namespace incorreto das Actions de tabela
