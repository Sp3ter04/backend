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

loadEnvFile(path.resolve(process.cwd(), '.env'));

const SUPABASE_URL = process.env.SUPABASE_URL;
const SUPABASE_SERVICE_KEY =
  process.env.SUPABASE_SECRET_KEY || process.env.SUPABASE_SERVICE_ROLE || process.env.SUPABASE_SERVICE_KEY;

const ADMIN_EMAIL = process.env.SUPABASE_ADMIN_EMAIL || 'admin@gmail.com';
const ADMIN_PASSWORD = process.env.SUPABASE_ADMIN_PASSWORD || 'Descoberta2026!';
const ADMIN_NAME = process.env.SUPABASE_ADMIN_NAME || 'Administrador';
const ADMIN_ROLE = process.env.SUPABASE_ADMIN_ROLE || 'admin';

if (!SUPABASE_URL || !SUPABASE_SERVICE_KEY) {
  console.error('Faltam SUPABASE_URL e SUPABASE_SECRET_KEY/SUPABASE_SERVICE_ROLE/SUPABASE_SERVICE_KEY.');
  process.exit(1);
}

const supabase = createClient(SUPABASE_URL, SUPABASE_SERVICE_KEY, {
  auth: { autoRefreshToken: false, persistSession: false },
});

async function findAuthUserByEmail(email) {
  let page = 1;

  while (true) {
    const { data, error } = await supabase.auth.admin.listUsers({
      page,
      perPage: 200,
    });

    if (error) {
      throw error;
    }

    const users = data?.users ?? [];
    const match = users.find((user) => user.email?.toLowerCase() === email.toLowerCase());

    if (match) {
      return match;
    }

    if (users.length < 200) {
      return null;
    }

    page += 1;
  }
}

async function findPublicUserByEmail(email) {
  const { data, error } = await supabase
    .from('users')
    .select('id, email, role')
    .ilike('email', email)
    .limit(1)
    .maybeSingle();

  if (error) {
    throw error;
  }

  return data ?? null;
}

async function ensureAuthUser(existingPublicUserId = null) {
  const existingAuthUser = await findAuthUserByEmail(ADMIN_EMAIL);

  if (existingAuthUser) {
    const { data, error } = await supabase.auth.admin.updateUserById(existingAuthUser.id, {
      email: ADMIN_EMAIL,
      password: ADMIN_PASSWORD,
      email_confirm: true,
      user_metadata: {
        name: ADMIN_NAME,
        role: ADMIN_ROLE,
      },
    });

    if (error) {
      throw error;
    }

    return data.user;
  }

  const payload = {
    email: ADMIN_EMAIL,
    password: ADMIN_PASSWORD,
    email_confirm: true,
    user_metadata: {
      name: ADMIN_NAME,
      role: ADMIN_ROLE,
    },
  };

  if (existingPublicUserId) {
    payload.id = existingPublicUserId;
    payload.user_id = existingPublicUserId;
  }

  const { data, error } = await supabase.auth.admin.createUser(payload);

  if (error) {
    throw error;
  }

  return data.user;
}

async function upsertPublicUser(authUserId) {
  const basePayload = {
    id: authUserId,
    email: ADMIN_EMAIL,
    name: ADMIN_NAME,
    role: ADMIN_ROLE,
  };

  const { error } = await supabase
    .from('users')
    .upsert([{ ...basePayload, auth_id: authUserId }], {
      onConflict: 'id',
    });

  if (!error) {
    return;
  }

  if (!String(error.message || '').toLowerCase().includes('auth_id')) {
    throw error;
  }

  const retry = await supabase
    .from('users')
    .upsert([basePayload], {
      onConflict: 'id',
    });

  if (retry.error) {
    throw retry.error;
  }
}

async function main() {
  const existingPublicUser = await findPublicUserByEmail(ADMIN_EMAIL);
  const authUser = await ensureAuthUser(existingPublicUser?.id ?? null);

  await upsertPublicUser(authUser.id);

  console.log(`Admin pronto: ${ADMIN_EMAIL}`);
  console.log(`UUID: ${authUser.id}`);
  console.log(`Role: ${ADMIN_ROLE}`);
  console.log(`Password: ${ADMIN_PASSWORD}`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
