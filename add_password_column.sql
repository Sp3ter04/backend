-- Adicionar coluna password à tabela users no Supabase
-- Execute este SQL diretamente no Supabase SQL Editor

ALTER TABLE users ADD COLUMN IF NOT EXISTS password VARCHAR(255);

-- Adicionar valor padrão temporário para usuários existentes
UPDATE users 
SET password = '$2y$12$default.password.hash.temporary.value'
WHERE password IS NULL;
