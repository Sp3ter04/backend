import fs from 'node:fs';
import path from 'node:path';
import { createClient } from '@supabase/supabase-js';

function loadEnvFile(filePath) {
  if (!fs.existsSync(filePath)) {
    return;
  }

  for (const rawLine of fs.readFileSync(filePath, 'utf8').split('\n')) {
    const line = rawLine.trim();

    if (!line || line.startsWith('#')) {
      continue;
    }

    const separatorIndex = line.indexOf('=');

    if (separatorIndex === -1) {
      continue;
    }

    const key = line.slice(0, separatorIndex).trim();
    let value = line.slice(separatorIndex + 1).trim();

    if (
      (value.startsWith('"') && value.endsWith('"')) ||
      (value.startsWith("'") && value.endsWith("'"))
    ) {
      value = value.slice(1, -1);
    }

    if (!(key in process.env)) {
      process.env[key] = value;
    }
  }
}

function envBool(value, defaultValue = false) {
  if (value === undefined || value === null || value === '') {
    return defaultValue;
  }

  return ['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase());
}

loadEnvFile(path.resolve(process.cwd(), '.env'));

const SUPABASE_URL = process.env.SUPABASE_URL;
const SUPABASE_SERVICE_KEY =
  process.env.SUPABASE_SECRET_KEY || process.env.SUPABASE_SERVICE_ROLE;
const BATCH_SIZE = Number(process.env.SUPABASE_MIGRATION_BATCH_SIZE || 200);
const DRY_RUN = envBool(process.env.DRY_RUN, false);
const PASSWORD_COLUMN = process.env.SUPABASE_USERS_PASSWORD_COLUMN || 'password';
const TEMP_PASSWORD = process.env.SUPABASE_IMPORT_TEMP_PASSWORD || '';

if (!SUPABASE_URL || !SUPABASE_SERVICE_KEY) {
  console.error('Faltam SUPABASE_URL e SUPABASE_SECRET_KEY/SUPABASE_SERVICE_ROLE no ambiente.');
  process.exit(1);
}

if (!Number.isFinite(BATCH_SIZE) || BATCH_SIZE <= 0) {
  console.error('SUPABASE_MIGRATION_BATCH_SIZE precisa de ser um numero positivo.');
  process.exit(1);
}

const supabase = createClient(SUPABASE_URL, SUPABASE_SERVICE_KEY, {
  auth: { autoRefreshToken: false, persistSession: false },
});

function shouldSkipPassword(password) {
  if (!password || typeof password !== 'string') {
    return true;
  }

  return password.length < 6;
}

function isAlreadyExistsError(error) {
  const message = error?.message?.toLowerCase() ?? '';

  return (
    error?.status === 422 ||
    message.includes('already been registered') ||
    message.includes('user already registered') ||
    message.includes('already exists') ||
    message.includes('email_exists') ||
    message.includes('user_already_exists')
  );
}

async function findAuthUserIdByEmail(email) {
  const { data, error } = await supabase.auth.admin.listUsers({
    page: 1,
    perPage: 1,
    email,
  });

  if (error) {
    return null;
  }

  const found = data?.users?.find((user) => user.email?.toLowerCase() === email.toLowerCase());
  return found?.id ?? null;
}

async function updateAuthId(userId, authId) {
  const { error } = await supabase
    .from('users')
    .update({ auth_id: authId })
    .eq('id', userId);

  return error;
}

async function fetchUsersBatch(lastId = null) {
  let query = supabase
    .from('users')
    .select('*')
    .not('email', 'is', null)
    .order('id', { ascending: true })
    .limit(BATCH_SIZE);

  if (lastId) {
    query = query.gt('id', lastId);
  }

  return query;
}

function resolvePassword(user) {
  const rawPassword = user?.[PASSWORD_COLUMN];

  if (!shouldSkipPassword(rawPassword)) {
    return rawPassword;
  }

  if (!shouldSkipPassword(TEMP_PASSWORD)) {
    return TEMP_PASSWORD;
  }

  return null;
}

async function migrateUsers() {
  let created = 0;
  let linkedExisting = 0;
  let alreadyLinked = 0;
  let skipped = 0;
  let failed = 0;
  let processed = 0;
  let cursor = null;

  console.log(`Modo: ${DRY_RUN ? 'DRY_RUN' : 'EXECUCAO REAL'} | Lote: ${BATCH_SIZE}`);
  console.log(`Password source: coluna "${PASSWORD_COLUMN}"${TEMP_PASSWORD ? ' com fallback TEMP_PASSWORD' : ''}`);

  while (true) {
    const { data: users, error } = await fetchUsersBatch(cursor);

    if (error) {
      console.error(`Erro ao buscar users: ${error.message}`);
      process.exit(1);
    }

    if (!users || users.length === 0) {
      break;
    }

    for (const user of users) {
      cursor = user.id;
      processed += 1;

      if (!user.email) {
        console.log(`SKIP  id=${user.id} sem email`);
        skipped += 1;
        continue;
      }

      if (user.auth_id) {
        console.log(`SKIP  ${user.email} ja tem auth_id`);
        alreadyLinked += 1;
        continue;
      }

      const password = resolvePassword(user);

      if (!password) {
        console.log(`SKIP  ${user.email} sem password utilizavel; define ${PASSWORD_COLUMN} ou SUPABASE_IMPORT_TEMP_PASSWORD`);
        skipped += 1;
        continue;
      }

      if (DRY_RUN) {
        console.log(`DRY   ${user.email}`);
        continue;
      }

      const { data, error: createError } = await supabase.auth.admin.createUser({
        email: user.email,
        password,
        email_confirm: true,
        user_metadata: {
          legacy_user_id: user.id,
        },
      });

      if (createError && !isAlreadyExistsError(createError)) {
        console.error(`FAIL  ${user.email} - ${createError.message}`);
        failed += 1;
        continue;
      }

      const authId = data?.user?.id || (await findAuthUserIdByEmail(user.email));

      if (!authId) {
        console.error(`FAIL  ${user.email} - nao foi possivel obter auth_id`);
        failed += 1;
        continue;
      }

      const updateError = await updateAuthId(user.id, authId);

      if (updateError) {
        console.error(
          `FAIL  ${user.email} - erro update auth_id: ${updateError.message}. Confirma se a coluna public.users.auth_id existe.`
        );
        failed += 1;
        continue;
      }

      if (createError && isAlreadyExistsError(createError)) {
        console.log(`LINK  ${user.email}`);
        linkedExisting += 1;
      } else {
        console.log(`OK    ${user.email}`);
        created += 1;
      }
    }

    if (users.length < BATCH_SIZE) {
      break;
    }
  }

  console.log('\nResumo:');
  console.log(`Processados: ${processed}`);
  console.log(`Criados: ${created}`);
  console.log(`Ligados a auth existente: ${linkedExisting}`);
  console.log(`Ja ligados: ${alreadyLinked}`);
  console.log(`Ignorados: ${skipped}`);
  console.log(`Falhas: ${failed}`);
}

migrateUsers().catch((error) => {
  console.error(error);
  process.exit(1);
});
