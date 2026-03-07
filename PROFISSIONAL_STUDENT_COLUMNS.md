# 📊 Tabela Profissional-Aluno: Estrutura de Colunas

## Colunas Visíveis por Padrão

### 1. Profissional
- **Campo:** `profissional.name`
- **Label:** "Profissional"
- **Descrição:** Email do profissional (abaixo do nome)
- **Pesquisável:** ✅ Sim
- **Ordenável:** ✅ Sim
- **Exemplo:**
  ```
  Maria Silva
  maria@email.com
  ```

---

### 2. Aluno
- **Campo:** `student.name`
- **Label:** "Aluno"
- **Descrição:** Email do aluno (abaixo do nome)
- **Pesquisável:** ✅ Sim
- **Ordenável:** ✅ Sim
- **Exemplo:**
  ```
  João Santos
  joao@email.com
  ```

---

### 3. Escola
- **Campo:** `student.school.name`
- **Label:** "Escola"
- **Pesquisável:** ✅ Sim
- **Ordenável:** ✅ Sim
- **Toggleable:** ✅ Sim (visível por padrão)
- **Exemplo:**
  ```
  Escola ABC
  ```

---

### 4. Criado em
- **Campo:** `created_at`
- **Label:** "Criado em"
- **Formato:** `d/m/Y H:i` (05/03/2026 17:30)
- **Ordenável:** ✅ Sim
- **Exemplo:**
  ```
  05/03/26 17:30
  ```

---

## Colunas Ocultas (Toggleable)

### 5. Email do Profissional
- **Campo:** `profissional.email`
- **Label:** "Email do Profissional"
- **Pesquisável:** ✅ Sim
- **Ordenável:** ✅ Sim
- **Visível:** ❌ Oculta por padrão (pode mostrar clicando no ícone de colunas)

---

### 6. Email do Aluno
- **Campo:** `student.email`
- **Label:** "Email do Aluno"
- **Pesquisável:** ✅ Sim
- **Ordenável:** ✅ Sim
- **Visível:** ❌ Oculta por padrão (pode mostrar clicando no ícone de colunas)

---

## Visualização Completa da Tabela

```
┌────────────────────────┬────────────────────────┬─────────────────┬──────────────────┬────────────┐
│ Profissional           │ Aluno                  │ Escola          │ Criado em        │ Ações      │
├────────────────────────┼────────────────────────┼─────────────────┼──────────────────┼────────────┤
│ Maria Silva            │ João Santos            │ Escola ABC      │ 05/03/26 17:30   │ 👁️ ✏️ 🗑️  │
│ maria@escola.com       │ joao@aluno.com         │                 │                  │            │
├────────────────────────┼────────────────────────┼─────────────────┼──────────────────┼────────────┤
│ Carlos Souza           │ Ana Oliveira           │ Escola XYZ      │ 05/03/26 16:45   │ 👁️ ✏️ 🗑️  │
│ carlos@escola.com      │ ana@aluno.com          │                 │                  │            │
├────────────────────────┼────────────────────────┼─────────────────┼──────────────────┼────────────┤
│ Pedro Alves            │ Lucas Mendes           │ Colégio 123     │ 04/03/26 15:20   │ 👁️ ✏️ 🗑️  │
│ pedro@escola.com       │ lucas@aluno.com        │                 │                  │            │
└────────────────────────┴────────────────────────┴─────────────────┴──────────────────┴────────────┘
```

### Legenda de Ações
- 👁️ = Ver detalhes (View)
- ✏️ = Editar (Edit)
- 🗑️ = Excluir (Delete)

---

## Como Mostrar Colunas Ocultas

1. Clique no ícone **"Colunas"** (geralmente no canto superior direito da tabela)
2. Marque as opções desejadas:
   - ☐ Email do Profissional
   - ☐ Email do Aluno
3. As colunas aparecem imediatamente na tabela

---

## Ordenação Disponível

Clique no **header** de qualquer coluna para ordenar:

| Coluna | Ordenação |
|--------|-----------|
| Profissional | A-Z, Z-A (alfabética por nome) |
| Aluno | A-Z, Z-A (alfabética por nome) |
| Escola | A-Z, Z-A (alfabética por nome) |
| Criado em | Mais recente → Mais antigo (padrão) |
| Email do Profissional | A-Z, Z-A |
| Email do Aluno | A-Z, Z-A |

---

## Pesquisa na Tabela

Digite na barra de pesquisa para buscar em:
- ✅ Nome do profissional
- ✅ Email do profissional
- ✅ Nome do aluno
- ✅ Email do aluno
- ✅ Nome da escola

**Exemplo:**
```
🔍 maria
```
Resultado: Mostra todas as relações onde:
- Profissional se chama "Maria"
- OU email contém "maria"
- OU aluno se chama "Maria"

---

## Resumo Técnico

```php
// Colunas principais
TextColumn::make('profissional.name')      // Nome + Email do profissional
TextColumn::make('student.name')           // Nome + Email do aluno
TextColumn::make('student.school.name')    // Escola do aluno
TextColumn::make('created_at')             // Data de criação

// Colunas secundárias (ocultas)
TextColumn::make('profissional.email')     // Email do profissional
TextColumn::make('student.email')          // Email do aluno
```

---

**Total de Colunas:** 6  
**Visíveis por Padrão:** 4  
**Ocultas (Toggleable):** 2
