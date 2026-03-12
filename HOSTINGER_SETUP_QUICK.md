# 🚀 Setup Rápido no Hostinger

## ⚠️ Situação Detectada

Você tem um symlink apontando para `storage/public/` ao invés do padrão Laravel `storage/app/public/`.

---

## ✅ Solução: Configurar .env

### 1️⃣ Descobrir o Caminho Real

Conecte via SSH e execute:

```bash
cd ~/domains/education.medtrack.click/public_html
readlink -f public/storage
```

**Exemplo de saída:**
```
/home/username/domains/education.medtrack.click/storage/public
```

---

### 2️⃣ Adicionar ao .env

Edite o arquivo `.env` e adicione (substitua pelo caminho real descoberto acima):

```env
PUBLIC_STORAGE_ROOT=/home/username/domains/education.medtrack.click/storage/public
```

**Exemplo completo:**
```env
APP_URL=https://education.medtrack.click
APP_ENV=production
APP_DEBUG=false

# Storage customizado do Hostinger
PUBLIC_STORAGE_ROOT=/home/username/domains/education.medtrack.click/storage/public
```

---

### 3️⃣ Criar Pastas de Áudio

```bash
# Use o mesmo caminho que você colocou no .env
mkdir -p /home/username/domains/education.medtrack.click/storage/public/audio/sentences
chmod -R 775 /home/username/domains/education.medtrack.click/storage/public/audio
```

---

### 4️⃣ Limpar Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

### 5️⃣ Testar

```bash
php artisan tinker
```

No tinker, execute:
```php
Storage::disk('public')->put('audio/test.txt', 'funciona!');
Storage::disk('public')->url('audio/test.txt');
exit
```

Acesse a URL retornada no navegador (algo como `https://education.medtrack.click/storage/audio/test.txt`).

---

## 🎯 Checklist Final

- [ ] Descobri o caminho real do symlink
- [ ] Adicionei `PUBLIC_STORAGE_ROOT` no `.env`
- [ ] Criei a pasta `audio/sentences`
- [ ] Ajustei as permissões (775)
- [ ] Limpei os caches do Laravel
- [ ] Testei criar um arquivo via tinker
- [ ] Consegui acessar o arquivo via URL
- [ ] Cliquei em "Listen" no admin e funcionou!

---

## 🐛 Se Não Funcionar

### Problema: Arquivo não aparece via URL

**Verifique .htaccess:**
```bash
cat public/.htaccess | grep -A 5 "storage"
```

Deve ter estas linhas:
```apache
RewriteCond %{REQUEST_URI} ^/storage/
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f
RewriteRule ^ - [L]
```

### Problema: Erro de permissão

```bash
# Verifique o dono dos arquivos
ls -la storage/public/audio

# Ajuste se necessário
chmod -R 775 storage/public/audio
chown -R $USER:$USER storage/public/audio
```

### Problema: Caminho errado

Execute o script de diagnóstico:
```bash
chmod +x hostinger-storage-diagnostic.sh
./hostinger-storage-diagnostic.sh
```

---

## 📞 Comandos Úteis

```bash
# Ver estrutura de pastas
tree -L 3 storage

# Ver permissões
ls -la storage/public/audio

# Ver symlink
ls -la public/storage

# Ver .env (sem mostrar senhas)
cat .env | grep -v PASSWORD | grep -v SECRET

# Ver logs de erro
tail -f storage/logs/laravel.log
```

---

## ✨ Configuração Final

Após seguir todos os passos, sua configuração deve estar assim:

```
📁 Raiz do Projeto
├── public/
│   └── storage -> ../../storage/public (symlink existente)
├── storage/
│   └── public/
│       └── audio/
│           └── sentences/
│               └── (arquivos .mp3 aqui)
└── .env
    └── PUBLIC_STORAGE_ROOT=/caminho/completo/storage/public
```

🎉 **Pronto! Agora os áudios devem funcionar perfeitamente!**
