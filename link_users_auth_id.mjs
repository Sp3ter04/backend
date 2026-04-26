import fs from 'node:fs';
import { createClient } from '@supabase/supabase-js';

for (const line of fs.readFileSync('.env', 'utf8').split('\n')) {
  const m = line.match(/^([A-Z_][A-Z0-9_]*)=(.*)$/);
  if (!m) continue;
  let v = m[2].trim();
  if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) v = v.slice(1, -1);
  if (!(m[1] in process.env)) process.env[m[1]] = v;
}

const DRY = process.argv.includes('--dry-run');
const sb = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_ROLE, {
  auth: { autoRefreshToken: false, persistSession: false },
});

// 1) Indexar auth.users por email
const authByEmail = new Map();
let page = 1;
while (true) {
  const { data, error } = await sb.auth.admin.listUsers({ page, perPage: 1000 });
  if (error) { console.error(error.message); process.exit(1); }
  for (const u of data.users) if (u.email) authByEmail.set(u.email.toLowerCase(), u.id);
  if (data.users.length < 1000) break;
  page++;
}
console.log(`auth.users indexados: ${authByEmail.size}`);

// 2) Buscar users public.users sem auth_id
const { data: pending, error: pe } = await sb.from('users').select('id,email,name').is('auth_id', null);
if (pe) { console.error(pe.message); process.exit(1); }
console.log(`users sem auth_id: ${pending.length}  (DRY=${DRY})`);

let linked = 0, noMatch = 0, noEmail = 0, errs = 0;
for (const u of pending) {
  if (!u.email) { console.log(`  - ${u.id} (${u.name||'?'}) sem email`); noEmail++; continue; }
  const authId = authByEmail.get(u.email.toLowerCase());
  if (!authId) { console.log(`  - ${u.email} sem match em auth.users`); noMatch++; continue; }
  if (DRY) { console.log(`  ~ ${u.email} -> ${authId}`); linked++; continue; }
  const { error } = await sb.from('users').update({ auth_id: authId }).eq('id', u.id);
  if (error) { console.log(`  ! ${u.email} ERRO: ${error.message}`); errs++; }
  else { console.log(`  + ${u.email} -> ${authId}`); linked++; }
}

console.log(`\nlinked=${linked} noMatch=${noMatch} noEmail=${noEmail} errs=${errs}`);
