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
  process.env.SUPABASE_SECRET_KEY || process.env.SUPABASE_SERVICE_ROLE;
const TEMP_PASSWORD = process.env.SUPABASE_IMPORT_TEMP_PASSWORD || 'Descoberta2026!';

if (!SUPABASE_URL || !SUPABASE_SERVICE_KEY) {
  console.error('Faltam SUPABASE_URL e SUPABASE_SECRET_KEY/SUPABASE_SERVICE_ROLE no ambiente.');
  process.exit(1);
}

const supabase = createClient(SUPABASE_URL, SUPABASE_SERVICE_KEY, {
  auth: { autoRefreshToken: false, persistSession: false },
});

const users = [
  { id: '015f630f-2ee9-46be-8a18-2cb2a62b8881', email: 'castiel@gmail.com', name: 'Castiel', role: 'aluno', email_confirmed: true },
  { id: '018c94da-192a-4e34-96af-c1d37b9139f3', email: 'aluno@gmail.com', name: 'Aluno João', role: 'aluno', email_confirmed: false },
  { id: '08637836-054c-415d-93eb-e5f2ddbd61f9', email: 'margaridasequeira@gmail.com', name: 'Margarida Sequeira', role: 'aluno', email_confirmed: true },
  { id: '096dfd82-daf5-48a8-98a0-3eace0655466', email: 'startupdislexia@gmail.com', name: 'Arminda', role: 'aluno', email_confirmed: true },
  { id: '0a1dd7f8-4d4b-4342-8dee-c4c6c76afcd1', email: 'margarida@gmail.com', name: 'Margarida', role: 'aluno', email_confirmed: true },
  { id: '0b7c159b-ed64-47d4-b5e3-8c82558e0d80', email: 'carlota.martinho@gmail.com', name: 'Carlota Martinho', role: 'aluno', email_confirmed: true },
  { id: '12afb8e3-6130-44dd-84e0-ca891ab5053b', email: 'andre10@gmail.com', name: 'Miguel Alegre', role: 'aluno', email_confirmed: true },
  { id: '1a7ab8ce-9c42-4632-b071-d9fba8f0a0b8', email: 'megs@gmail.com', name: 'Margarida', role: 'aluno', email_confirmed: true },
  { id: '1ab6e6e0-f112-4222-a32e-0a4fdaeb863e', email: 'Pedronunes.pmmn@gmail.com', name: 'Pedro Nunes', role: 'aluno', email_confirmed: true },
  { id: '2b68719f-fbb2-4a9a-9abf-a730d52b9b80', email: 'tiago@gmail.com', name: 'Tiago Antunes', role: 'aluno', email_confirmed: true },
  { id: '314c3d91-be93-4454-8786-74dd204cb514', email: 'vitoraluno@gmail.com', name: 'Vitor Clara', role: 'aluno', email_confirmed: true },
  { id: '32ce0180-b888-4e43-b505-a49666ae4989', email: 'guilherme@gmail.com', name: 'Guilherme', role: 'aluno', email_confirmed: true },
  { id: '3c46a8b3-b966-40b5-be52-54d6ac00362a', email: 'eduarda.santos@gmail.com', name: 'Eduarda Santos', role: 'aluno', email_confirmed: true },
  { id: '3db75e40-c6b8-4c0f-b589-d67390541ff7', email: 'andre4@gmail.com', name: 'Francisca Marques', role: 'aluno', email_confirmed: true },
  { id: '3fe78509-aa28-4f80-9d54-32e4d591a18d', email: 'pedronunes@gmail.com', name: 'Pedro nunes', role: 'aluno', email_confirmed: true },
  { id: '496afdc9-08c6-43b3-923f-b520641a4004', email: 'margaridasimoes@gmail.com', name: 'Margarida Simões', role: 'aluno', email_confirmed: true },
  { id: '56aea145-f73d-4742-aca0-6258d9af020c', email: 'bea@gmail.com', name: 'Bea', role: 'aluno', email_confirmed: true },
  { id: '585cba88-9644-493e-86b3-88f1a518a0e8', email: 'joaodedeus2_1@gmail.com', name: 'Joaquim Dias', role: 'aluno', email_confirmed: true },
  { id: '59dd17d8-682d-41b0-9d86-b96baf61a1bd', email: 'diogo.castanheira@gmail.com', name: 'Diogo Castanheira', role: 'aluno', email_confirmed: true },
  { id: '5d742b57-d297-4bb6-b9c5-ae704630fedf', email: 'andreazrods@gmail.com', name: 'André Rodrigues', role: 'aluno', email_confirmed: true },
  { id: '72d6d667-1612-416f-9715-08c4d3e4b1f1', email: 'anacalado1979@gmail.com', name: 'Ana Calado', role: 'aluno', email_confirmed: true },
  { id: '7cadae0c-086f-4f3b-8065-866318bafaf5', email: 'joaodedeus1_1@gmail.com', name: 'Francisco Coelho', role: 'aluno', email_confirmed: true },
  { id: '7e6a6984-4803-4fe0-a00b-357f5880d411', email: 'marianaazevedo2004@gmail.com', name: 'Mariana Azevedo', role: 'aluno', email_confirmed: true },
  { id: '800dddb5-b38a-425b-9512-7b1cff563827', email: 'marianunes@gmail.com', name: 'Maria Nunes', role: 'aluno', email_confirmed: true },
  { id: '82fcff68-7301-4599-b818-2cfa383b603c', email: 'sara@gmail.com', name: 'Sara', role: 'aluno', email_confirmed: true },
  { id: '888d5cf8-563d-48fb-b8e2-5762fa280dfd', email: 'marianacpereiraa@gmail.com', name: 'Mariana Pereira', role: 'aluno', email_confirmed: true },
  { id: '8c23bdc0-377e-4e09-a9a8-cc3b694eef85', email: 'andre7@gmail.com', name: 'Estela Bento', role: 'aluno', email_confirmed: true },
  { id: '91cec526-b000-4893-99a2-f0d21a51b67f', email: 'armando@gmail.com', name: 'armando djigidijonson', role: 'aluno', email_confirmed: true },
  { id: '93e79d89-7ad2-406c-b29d-78a3bcd21d86', email: 'dinisgoncales@gmail.com', name: 'Dinis Gonçalves', role: 'aluno', email_confirmed: true },
  { id: '95588c31-0b37-478d-bc65-c0ba19f4e0b3', email: 'andre6@gmail.com', name: 'Afonso Trindade', role: 'aluno', email_confirmed: true },
  { id: '9aa69c4e-3cb6-4288-a156-3334ccc890bc', email: 'vitor@gmail.com', name: 'Vitor', role: 'aluno', email_confirmed: true },
  { id: '9c2d4ff2-4451-45b4-934a-aa12f9889144', email: 'andre.rodrigues@sapo.pt', name: 'João Santos', role: 'aluno', email_confirmed: true },
  { id: '9ff0ec9f-133b-4944-9e7b-e2a1d8d26aea', email: 'joaodedeus0_1@gmail.com', name: 'Théo Martins', role: 'aluno', email_confirmed: true },
  { id: '9ff86021-9ba9-452d-af3a-0e574e813e8a', email: 'bernardo@gmail.com', name: 'Bernardo Martins', role: 'aluno', email_confirmed: true },
  { id: 'a68107e6-eab7-4d0b-9ae5-3b06163809da', email: 'vascogomes@gmail.com', name: 'Vasco Gomes', role: 'aluno', email_confirmed: true },
  { id: 'ab3dbad0-652e-4a8b-a9d3-67f0f836a092', email: 'beatriz@gmail.com', name: 'Beatriz', role: 'aluno', email_confirmed: true },
  { id: 'ae5b447c-f72b-45cf-9627-1a28812ff86b', email: 'vicente@gmail.com', name: 'Vicente', role: 'aluno', email_confirmed: true },
  { id: 'ae85a1b2-9106-4c87-8778-e9aa88c5d889', email: 'margaridanunes@gmail.com', name: 'Margarida Nunes', role: 'aluno', email_confirmed: true },
  { id: 'b1212737-d9ee-4cbf-93be-ccfb8d425b69', email: 'goncalo@gmail.com', name: 'Gonçalo', role: 'aluno', email_confirmed: true },
  { id: 'b53dc0d0-a977-4696-8b40-61489c1ae123', email: 'dinis@gmail.com', name: 'Dinis', role: 'aluno', email_confirmed: true },
  { id: 'b7c12e5a-9301-43fd-b316-568a11448486', email: 'andre1@gmail.com', name: 'João Figueiras', role: 'aluno', email_confirmed: true },
  { id: 'b885a11a-8c6b-4bb4-bcb1-62c08bbe7275', email: 'daiame@gmail.com', name: 'Daiame', role: 'aluno', email_confirmed: true },
  { id: 'c99cb54e-0cf3-426b-80af-9a2ef12c2357', email: 'tomas@gmail.com', name: 'Tomás Grãos', role: 'aluno', email_confirmed: true },
  { id: 'ca699656-fcc5-4b13-a7dd-65102f62999b', email: 'andre3@gmail.com', name: 'Mariana Gomes', role: 'aluno', email_confirmed: true },
  { id: 'cd5a0788-fb23-462b-be20-9204b336f43d', email: 'andre2@gmail.com', name: 'Miriam Valente', role: 'aluno', email_confirmed: true },
  { id: 'd0236b95-3830-4d8d-bb59-9bb8bc02f69f', email: 'peafcc@gmail.com', name: 'Pedro Carvalho', role: 'aluno', email_confirmed: true },
  { id: 'd1dfcab1-baf0-40a3-b5f2-298e0ec2731b', email: 'andre5@gmail.com', name: 'Kobe Bryant', role: 'aluno', email_confirmed: true },
  { id: 'd37c7d7c-aa9b-45d9-8d25-693333ad937f', email: 'joaodedeus2_2@gmail.com', name: 'Carolina Mateus', role: 'aluno', email_confirmed: true },
  { id: 'd425e723-1663-4302-bfff-75e975b51959', email: 'margaridamn@gmail.com', name: 'Margarida', role: 'aluno', email_confirmed: true },
  { id: 'd95110b6-912c-42dc-bc46-aee75f0bad95', email: 'matilde.madureira@junitec.pt', name: 'Matilde Madureira', role: 'aluno', email_confirmed: true },
  { id: 'dfd769d3-6d2a-4c39-ba22-b75d5b781e57', email: 'andre9@gmail.com', name: 'Paula Fernandes', role: 'aluno', email_confirmed: true },
  { id: 'e2839413-dc47-4a2c-9ac7-ff5448a3c809', email: 'victoria.isabel@gmail.com', name: 'Victoria Isabel', role: 'aluno', email_confirmed: true },
  { id: 'ea6ca1db-4460-45b5-a218-6676490a2d47', email: 'joaodedeus1_2@gmail.com', name: 'Lara Romão', role: 'aluno', email_confirmed: true },
  { id: 'efe7eb37-fdd1-4623-ba73-f9e2e712ac61', email: 'barbara.pinheiro@gmail.com', name: 'Bárbara Pinheiro', role: 'aluno', email_confirmed: true },
  { id: 'f160026d-e1eb-4ae2-a966-9c2fdc33337b', email: 'francisco.redondo@gmail.com', name: 'Francisco Redondo', role: 'aluno', email_confirmed: true },
  { id: 'fc8a2db6-8678-465e-9f8f-aceb87fd138a', email: 'maria@gmail.com', name: 'Maria', role: 'aluno', email_confirmed: true },
  { id: '0c8159c5-7eca-4930-bc2a-bd492335c351', email: 'andre.rodrigues@junitec.pt', name: 'José Pinhal', role: 'profissional', email_confirmed: true },
  { id: '0fc3660d-c0dd-4498-ab31-0b29d204cc41', email: 'vitorclara@gmail.com', name: 'Vitor Clara', role: 'profissional', email_confirmed: true },
  { id: '122eda05-ee92-4983-a59c-0222440e2ce4', email: 'cristinajanuario@crsi.pt', name: 'Cristina Januário', role: 'profissional', email_confirmed: true },
  { id: '217675ab-a65b-401c-8836-88e7fa90d356', email: 'margarida.nunes.mmn@gmail.com', name: 'Margarida', role: 'profissional', email_confirmed: true },
  { id: '295d1ad1-f6b2-4424-aa70-3809f17457ed', email: 'margaridan@gmail.com', name: 'Margarida Nunes', role: 'profissional', email_confirmed: true },
  { id: '5c147895-08dd-4a75-baeb-7984e06684ea', email: 'alzirax@gmail.com', name: 'Alzira Vicente', role: 'profissional', email_confirmed: true },
  { id: '6bd0a7b2-fb52-4f7f-bea5-d8a7e01fcd9e', email: 'profissional@gmail.com', name: 'Professor João', role: 'profissional', email_confirmed: false },
  { id: '7f0945b6-3bb9-49b8-90c0-342f08613a0f', email: 'andre.correia@yahoo.com', name: 'André Correia', role: 'profissional', email_confirmed: true },
  { id: '9a80b9dc-84b5-4cc8-81af-7712944fccfd', email: 'ana@gmail.com', name: 'Ana', role: 'profissional', email_confirmed: true },
  { id: 'bf56f7cf-8ddf-4799-aaa9-19f1ffcb17c0', email: 'anabelagguimaraes@hotmail.com', name: 'Anabela Guimarães', role: 'profissional', email_confirmed: true },
  { id: 'ce3831d3-4c2a-49a0-bb67-2dcd36a3af13', email: 'andreclashroyalerods@gmail.com', name: 'André Rodrigues', role: 'profissional', email_confirmed: true },
  { id: 'e62aecc7-b9e5-4e91-a94c-af0522b9da06', email: 'anaritacalado@aemoinhosarroja.pt', name: 'Ana Calado', role: 'profissional', email_confirmed: true },
  { id: '41c29ba9-55af-48c1-83cc-944fb7f2c5ec', email: 'admin@gmail.com', name: 'administrador', role: 'admin', email_confirmed: false },
];

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

async function createUsers() {
  let ok = 0;
  let skip = 0;
  let fail = 0;

  for (const user of users) {
    const payload = {
      id: user.id,
      user_id: user.id,
      email: user.email,
      password: TEMP_PASSWORD,
      email_confirm: user.email_confirmed,
      user_metadata: {
        name: user.name,
        role: user.role,
      },
      app_metadata: {
        provider: 'email',
        providers: ['email'],
      },
    };

    const { data, error } = await supabase.auth.admin.createUser(payload);

    if (error) {
      if (isAlreadyExistsError(error)) {
        console.log(`SKIP  ${user.email} (ja existe)`);
        skip += 1;
        continue;
      }

      console.error(`FAIL  ${user.email} - ${error.message}`);
      fail += 1;
      continue;
    }

    const createdId = data?.user?.id;

    if (createdId && createdId !== user.id) {
      console.error(`FAIL  ${user.email} - criado com UUID inesperado (${createdId})`);
      fail += 1;
      continue;
    }

    console.log(`OK    ${user.email} (${user.role})`);
    ok += 1;
  }

  console.log(`\nCriados: ${ok} | Ja existiam: ${skip} | Erros: ${fail}`);
  console.log(`Password temporaria: ${TEMP_PASSWORD}`);
}

createUsers().catch((error) => {
  console.error(error);
  process.exit(1);
});
