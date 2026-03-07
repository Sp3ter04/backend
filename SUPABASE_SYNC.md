# Sincronização Automática com Supabase

Este sistema mantém o SQLite local sincronizado com o Supabase automaticamente.

## 📋 Como Funciona

O sistema sincroniza automaticamente as seguintes tabelas:
- ✅ **exercises** (Exercícios)
- ✅ **words** (Palavras)
- ✅ **syllables** (Sílabas)
- ✅ **users** (Utilizadores)
- ✅ **schools** (Escolas)
- ✅ **exercise_words** (Relação exercícios-palavras)
- ✅ **word_syllables** (Relação palavras-sílabas)
- ✅ **profissional_student** (Relação profissional-aluno)
- ✅ **dictation_metrics** (Métricas de ditado)

## 🔄 Sincronização Automática

A sincronização acontece automaticamente:
- **A cada 5 minutos** quando a aplicação Laravel está em execução (ambiente local)
- Apenas sincroniza se houver alterações (baseado em cache)

## 🛠️ Comandos Manuais

### Sincronizar todas as tabelas
```bash
php artisan supabase:sync-all
```

### Sincronizar apenas uma tabela específica
```bash
php artisan supabase:sync-all --table=exercises
php artisan supabase:sync-all --table=words
php artisan supabase:sync-all --table=syllables
```

## ⚙️ Configuração

### Variáveis de Ambiente (.env)
```env
NEXT_PUBLIC_SUPABASE_URL=https://emwgjilzdxlvpkrvkhmc.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_anon_key_aqui
SUPABASE_SERVICE_ROLE_KEY=sua_service_role_key_aqui
```

### Desabilitar Sincronização Automática

Para desabilitar a sincronização automática, comenta ou remove esta linha em `bootstrap/providers.php`:

```php
// App\Providers\SupabaseSyncServiceProvider::class,
```

## 🔍 Monitorização

### Ver logs de sincronização
Os logs são salvos em `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log | grep Supabase
```

### Verificar última sincronização
```bash
php artisan tinker
>>> cache('supabase_last_sync')
```

## 📊 Estatísticas

Após executar a sincronização, verás:
- 📋 Número de registros encontrados no Supabase
- 🗑️ Confirmação de limpeza da tabela local
- ✅ Número de registros sincronizados com sucesso
- ⚠️ Avisos sobre problemas (se houver)

## 🚀 Exemplo de Uso

```bash
$ php artisan supabase:sync-all

🔄 Iniciando sincronização do Supabase...

📋 Sincronizando tabela: exercises
  📥 Encontrados 13 registros no Supabase
  🗑️  Tabela local limpa
  ✅ 13 registros sincronizados

📋 Sincronizando tabela: words
  📥 Encontrados 0 registros no Supabase
  ⚠️  Nenhum registro encontrado. Pulando...

🎉 Sincronização concluída!
📊 Total de registros sincronizados: 13
```

## 🔐 Segurança

- ❗ **NUNCA** comite as chaves do Supabase no repositório
- ✅ Use variáveis de ambiente (.env)
- ✅ A `SERVICE_ROLE_KEY` tem acesso total - use com cuidado
- ✅ Em produção, considere usar políticas RLS do Supabase

## 🐛 Troubleshooting

### Erro: "Tabela não existe no SQLite"
Execute as migrações:
```bash
php artisan migrate
```

### Erro: "NOT NULL constraint failed"
Verifica se a estrutura da tabela no Supabase corresponde à migração local.

### Sincronização não está a acontecer
Limpa o cache:
```bash
php artisan cache:clear
php artisan config:clear
```
