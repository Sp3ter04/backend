alter table public.users
add column if not exists auth_id uuid;

create unique index if not exists users_auth_id_unique
on public.users (auth_id)
where auth_id is not null;
