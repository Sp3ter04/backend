# 📝 Prompt para IA - Alterações no Sistema de Exercícios

**Use este prompt com Cursor, ChatGPT ou Copilot para implementar mudanças similares:**

---

## Contexto

I am working on a Laravel + PostgreSQL (Supabase) backend for an educational platform.

I recently added a new column called `created_by` (type text) to the table `exercises`.

I need the following changes implemented:

---

## 1. Update Existing Records

All existing rows where `created_by` is NULL must be updated so that:

```sql
created_by = 'admin@gmail.com'
```

Provide the SQL query for this migration.

---

## 2. When Creating a New Exercise

Currently the form contains:
- Exercicio (sentence)
- Conteudo (content)
- Numero (number)
- Dificuldade (difficulty)

I want to change the logic so that:
- `conteudo` is not taken from the form anymore
- `conteudo` must automatically be equal to the value of `exercicio`

**Example:**

Input:
```
exercicio = "O gato bebe leite"
```

Database:
```
exercicio = "O gato bebe leite"
conteudo = "O gato bebe leite"
```

---

## 3. Automatically Fill created_by

When a new exercise is created:

```php
created_by = auth()->user()->email
```

So the logged-in user's email is stored.

---

## 4. Controller Update

Modify the `store()` method in the Laravel controller so that it saves:
- exercicio
- conteudo (same as exercicio)
- numero
- dificuldade
- created_by

---

## 5. Form Update

Update the Filament / form page so that:
- the `Conteudo` field is removed from the form
- only the field `Exercicio` remains for the text input

---

## Requirements

- Use **Laravel Model Events** (`creating()`, `updating()`)
- Keep the code clean and professional
- Centralize logic in the Model (not in controllers)
- Provide SQL migration file
- Update controller validation rules
- Update Filament form schema

---

## Expected Output

Provide:
1. SQL query for updating existing records
2. Updated `Exercise.php` Model with `boot()` method and Model Events
3. Updated `ExerciseController.php` with new validation rules
4. Updated `ExerciseForm.php` (Filament) without the `content` field
5. Brief explanation of the changes

---

## Additional Notes

- The Model is: `App\Models\Exercise`
- The Controller is: `App\Http\Controllers\ExerciseController`
- The Filament form is: `App\Filament\Resources\Exercises\Schemas\ExerciseForm`
- Use Laravel 11 syntax
- Use PostgreSQL (Supabase) as database
