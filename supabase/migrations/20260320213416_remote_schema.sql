drop extension if exists "pg_net";

create sequence "public"."failed_jobs_id_seq";

create sequence "public"."jobs_id_seq";

create sequence "public"."migrations_id_seq";


  create table "public"."cache" (
    "key" character varying(255) not null,
    "value" text not null,
    "expiration" integer not null
      );


alter table "public"."cache" enable row level security;


  create table "public"."cache_locks" (
    "key" character varying(255) not null,
    "owner" character varying(255) not null,
    "expiration" integer not null
      );


alter table "public"."cache_locks" enable row level security;


  create table "public"."dictation_metrics" (
    "id" uuid not null default gen_random_uuid(),
    "student_id" uuid not null,
    "exercise_id" uuid not null,
    "difficulty" character varying(255) not null,
    "correct_count" integer not null default 0,
    "error_count" integer not null default 0,
    "missing_count" integer not null default 0,
    "extra_count" integer not null default 0,
    "accuracy_percent" numeric(5,2) not null default '0'::numeric,
    "letter_omission_count" integer not null default 0,
    "letter_insertion_count" integer not null default 0,
    "letter_substitution_count" integer not null default 0,
    "transposition_count" numeric(8,2) not null default '0'::numeric,
    "split_join_count" integer not null default 0,
    "punctuation_error_count" integer not null default 0,
    "capitalization_error_count" integer not null default 0,
    "error_words" json,
    "resolution" text,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );



  create table "public"."exercise_words" (
    "id" uuid not null,
    "exercise_id" uuid not null,
    "word_id" uuid not null,
    "word_order" integer not null,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."exercise_words" enable row level security;


  create table "public"."exercises" (
    "id" uuid not null,
    "sentence" text not null,
    "words_json" text,
    "difficulty" character varying(255) not null default 1,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "content" text,
    "number" integer,
    "audio_url_1" text,
    "audio_url_2" text,
    "created_by" character varying
      );



  create table "public"."failed_jobs" (
    "id" bigint not null default nextval('public.failed_jobs_id_seq'::regclass),
    "uuid" character varying(255) not null,
    "connection" text not null,
    "queue" text not null,
    "payload" text not null,
    "exception" text not null,
    "failed_at" timestamp(0) without time zone not null default CURRENT_TIMESTAMP
      );


alter table "public"."failed_jobs" enable row level security;


  create table "public"."job_batches" (
    "id" character varying(255) not null,
    "name" character varying(255) not null,
    "total_jobs" integer not null,
    "pending_jobs" integer not null,
    "failed_jobs" integer not null,
    "failed_job_ids" text not null,
    "options" text,
    "cancelled_at" integer,
    "created_at" integer not null,
    "finished_at" integer
      );


alter table "public"."job_batches" enable row level security;


  create table "public"."jobs" (
    "id" bigint not null default nextval('public.jobs_id_seq'::regclass),
    "queue" character varying(255) not null,
    "payload" text not null,
    "attempts" smallint not null,
    "reserved_at" integer,
    "available_at" integer not null,
    "created_at" integer not null
      );


alter table "public"."jobs" enable row level security;


  create table "public"."migrations" (
    "id" integer not null default nextval('public.migrations_id_seq'::regclass),
    "migration" character varying(255) not null,
    "batch" integer not null
      );


alter table "public"."migrations" enable row level security;


  create table "public"."password_reset_tokens" (
    "email" character varying(255) not null,
    "token" character varying(255) not null,
    "created_at" timestamp(0) without time zone
      );


alter table "public"."password_reset_tokens" enable row level security;


  create table "public"."pedidos" (
    "id" uuid not null default gen_random_uuid(),
    "solicitante_id" uuid not null,
    "solicitante_tipo" character varying(255) not null,
    "destinatario_id" uuid not null,
    "destinatario_tipo" character varying(255) not null,
    "status" character varying(255) not null default 'pendente'::character varying,
    "criado_em" timestamp(0) with time zone not null default now(),
    "respondido_em" timestamp(0) with time zone
      );


alter table "public"."pedidos" enable row level security;


  create table "public"."profissional_student" (
    "id" uuid not null,
    "profissional_id" uuid not null,
    "student_id" uuid not null,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."profissional_student" enable row level security;


  create table "public"."schools" (
    "id" uuid not null,
    "name" character varying(255) not null,
    "address" text,
    "phone" character varying(255),
    "email" character varying(255),
    "director_name" character varying(255),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."schools" enable row level security;


  create table "public"."sessions" (
    "id" character varying(255) not null,
    "user_id" uuid,
    "ip_address" character varying(45),
    "user_agent" text,
    "payload" text not null,
    "last_activity" integer not null
      );


alter table "public"."sessions" enable row level security;


  create table "public"."syllables" (
    "id" uuid not null,
    "syllable" character varying(255) not null,
    "audio_url" character varying(255),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."syllables" enable row level security;


  create table "public"."user_progress" (
    "user_id" uuid not null,
    "stars_total" integer not null default 0,
    "level" character varying(255) not null default 'explorador'::character varying,
    "active_days" json not null default '[]'::json,
    "evolution_count" integer not null default 0,
    "last_daily_bonus_date" date,
    "accuracy_history" json not null default '[]'::json,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."user_progress" enable row level security;


  create table "public"."users" (
    "id" uuid not null,
    "name" character varying(255) not null,
    "email" character varying(255) not null,
    "email_verified_at" timestamp(0) without time zone,
    "remember_token" character varying(100),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "role" character varying(255) not null default 'aluno'::character varying,
    "school_id" uuid,
    "school_year" character varying(255)
      );


alter table "public"."users" enable row level security;


  create table "public"."word_syllables" (
    "id" uuid not null,
    "word_id" uuid not null,
    "position" integer not null,
    "audio_url" character varying(255),
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone,
    "syllable_id" uuid not null
      );


alter table "public"."word_syllables" enable row level security;


  create table "public"."words" (
    "id" uuid not null,
    "word" character varying(255) not null,
    "syllables" text,
    "audio_url" character varying(255),
    "difficulty" integer not null default 1,
    "created_at" timestamp(0) without time zone,
    "updated_at" timestamp(0) without time zone
      );


alter table "public"."words" enable row level security;

alter sequence "public"."failed_jobs_id_seq" owned by "public"."failed_jobs"."id";

alter sequence "public"."jobs_id_seq" owned by "public"."jobs"."id";

alter sequence "public"."migrations_id_seq" owned by "public"."migrations"."id";

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);

CREATE UNIQUE INDEX cache_locks_pkey ON public.cache_locks USING btree (key);

CREATE UNIQUE INDEX cache_pkey ON public.cache USING btree (key);

CREATE INDEX dictation_metrics_accuracy_percent_index ON public.dictation_metrics USING btree (accuracy_percent);

CREATE INDEX dictation_metrics_exercise_id_difficulty_index ON public.dictation_metrics USING btree (exercise_id, difficulty);

CREATE UNIQUE INDEX dictation_metrics_pkey ON public.dictation_metrics USING btree (id);

CREATE INDEX dictation_metrics_student_id_created_at_index ON public.dictation_metrics USING btree (student_id, created_at);

CREATE UNIQUE INDEX exercise_words_pkey ON public.exercise_words USING btree (id);

CREATE UNIQUE INDEX exercises_pkey ON public.exercises USING btree (id);

CREATE UNIQUE INDEX failed_jobs_pkey ON public.failed_jobs USING btree (id);

CREATE UNIQUE INDEX failed_jobs_uuid_unique ON public.failed_jobs USING btree (uuid);

CREATE UNIQUE INDEX job_batches_pkey ON public.job_batches USING btree (id);

CREATE UNIQUE INDEX jobs_pkey ON public.jobs USING btree (id);

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);

CREATE UNIQUE INDEX migrations_pkey ON public.migrations USING btree (id);

CREATE UNIQUE INDEX password_reset_tokens_pkey ON public.password_reset_tokens USING btree (email);

CREATE UNIQUE INDEX pedidos_pkey ON public.pedidos USING btree (id);

CREATE UNIQUE INDEX profissional_student_pkey ON public.profissional_student USING btree (id);

CREATE INDEX profissional_student_profissional_id_index ON public.profissional_student USING btree (profissional_id);

CREATE UNIQUE INDEX profissional_student_profissional_id_student_id_unique ON public.profissional_student USING btree (profissional_id, student_id);

CREATE INDEX profissional_student_student_id_index ON public.profissional_student USING btree (student_id);

CREATE UNIQUE INDEX schools_pkey ON public.schools USING btree (id);

CREATE UNIQUE INDEX sessions_pkey ON public.sessions USING btree (id);

CREATE INDEX sessions_user_id_last_activity_index ON public.sessions USING btree (user_id, last_activity);

CREATE UNIQUE INDEX syllables_pkey ON public.syllables USING btree (id);

CREATE UNIQUE INDEX syllables_syllable_unique ON public.syllables USING btree (syllable);

CREATE UNIQUE INDEX unique_pending_request ON public.pedidos USING btree (solicitante_id, destinatario_id, status);

CREATE UNIQUE INDEX user_progress_pkey ON public.user_progress USING btree (user_id);

CREATE UNIQUE INDEX users_email_unique ON public.users USING btree (email);

CREATE UNIQUE INDEX users_pkey ON public.users USING btree (id);

CREATE UNIQUE INDEX word_syllables_pkey ON public.word_syllables USING btree (id);

CREATE UNIQUE INDEX word_syllables_word_id_position_unique ON public.word_syllables USING btree (word_id, "position");

CREATE UNIQUE INDEX words_pkey ON public.words USING btree (id);

CREATE UNIQUE INDEX words_word_unique ON public.words USING btree (word);

alter table "public"."cache" add constraint "cache_pkey" PRIMARY KEY using index "cache_pkey";

alter table "public"."cache_locks" add constraint "cache_locks_pkey" PRIMARY KEY using index "cache_locks_pkey";

alter table "public"."dictation_metrics" add constraint "dictation_metrics_pkey" PRIMARY KEY using index "dictation_metrics_pkey";

alter table "public"."exercise_words" add constraint "exercise_words_pkey" PRIMARY KEY using index "exercise_words_pkey";

alter table "public"."exercises" add constraint "exercises_pkey" PRIMARY KEY using index "exercises_pkey";

alter table "public"."failed_jobs" add constraint "failed_jobs_pkey" PRIMARY KEY using index "failed_jobs_pkey";

alter table "public"."job_batches" add constraint "job_batches_pkey" PRIMARY KEY using index "job_batches_pkey";

alter table "public"."jobs" add constraint "jobs_pkey" PRIMARY KEY using index "jobs_pkey";

alter table "public"."migrations" add constraint "migrations_pkey" PRIMARY KEY using index "migrations_pkey";

alter table "public"."password_reset_tokens" add constraint "password_reset_tokens_pkey" PRIMARY KEY using index "password_reset_tokens_pkey";

alter table "public"."pedidos" add constraint "pedidos_pkey" PRIMARY KEY using index "pedidos_pkey";

alter table "public"."profissional_student" add constraint "profissional_student_pkey" PRIMARY KEY using index "profissional_student_pkey";

alter table "public"."schools" add constraint "schools_pkey" PRIMARY KEY using index "schools_pkey";

alter table "public"."sessions" add constraint "sessions_pkey" PRIMARY KEY using index "sessions_pkey";

alter table "public"."syllables" add constraint "syllables_pkey" PRIMARY KEY using index "syllables_pkey";

alter table "public"."user_progress" add constraint "user_progress_pkey" PRIMARY KEY using index "user_progress_pkey";

alter table "public"."users" add constraint "users_pkey" PRIMARY KEY using index "users_pkey";

alter table "public"."word_syllables" add constraint "word_syllables_pkey" PRIMARY KEY using index "word_syllables_pkey";

alter table "public"."words" add constraint "words_pkey" PRIMARY KEY using index "words_pkey";

alter table "public"."dictation_metrics" add constraint "dictation_metrics_student_id_foreign" FOREIGN KEY (student_id) REFERENCES public.users(id) ON DELETE CASCADE not valid;

alter table "public"."dictation_metrics" validate constraint "dictation_metrics_student_id_foreign";

alter table "public"."exercise_words" add constraint "exercise_words_exercise_id_foreign" FOREIGN KEY (exercise_id) REFERENCES public.exercises(id) ON DELETE CASCADE not valid;

alter table "public"."exercise_words" validate constraint "exercise_words_exercise_id_foreign";

alter table "public"."exercise_words" add constraint "exercise_words_word_id_foreign" FOREIGN KEY (word_id) REFERENCES public.words(id) ON DELETE CASCADE not valid;

alter table "public"."exercise_words" validate constraint "exercise_words_word_id_foreign";

alter table "public"."failed_jobs" add constraint "failed_jobs_uuid_unique" UNIQUE using index "failed_jobs_uuid_unique";

alter table "public"."pedidos" add constraint "pedidos_destinatario_tipo_check" CHECK (((destinatario_tipo)::text = ANY ((ARRAY['aluno'::character varying, 'profissional'::character varying])::text[]))) not valid;

alter table "public"."pedidos" validate constraint "pedidos_destinatario_tipo_check";

alter table "public"."pedidos" add constraint "pedidos_solicitante_tipo_check" CHECK (((solicitante_tipo)::text = ANY ((ARRAY['aluno'::character varying, 'profissional'::character varying])::text[]))) not valid;

alter table "public"."pedidos" validate constraint "pedidos_solicitante_tipo_check";

alter table "public"."pedidos" add constraint "pedidos_status_check" CHECK (((status)::text = ANY ((ARRAY['pendente'::character varying, 'aceite'::character varying, 'recusado'::character varying])::text[]))) not valid;

alter table "public"."pedidos" validate constraint "pedidos_status_check";

alter table "public"."pedidos" add constraint "unique_pending_request" UNIQUE using index "unique_pending_request";

alter table "public"."profissional_student" add constraint "profissional_student_profissional_id_foreign" FOREIGN KEY (profissional_id) REFERENCES public.users(id) ON DELETE CASCADE not valid;

alter table "public"."profissional_student" validate constraint "profissional_student_profissional_id_foreign";

alter table "public"."profissional_student" add constraint "profissional_student_profissional_id_student_id_unique" UNIQUE using index "profissional_student_profissional_id_student_id_unique";

alter table "public"."profissional_student" add constraint "profissional_student_student_id_foreign" FOREIGN KEY (student_id) REFERENCES public.users(id) ON DELETE CASCADE not valid;

alter table "public"."profissional_student" validate constraint "profissional_student_student_id_foreign";

alter table "public"."syllables" add constraint "syllables_syllable_unique" UNIQUE using index "syllables_syllable_unique";

alter table "public"."user_progress" add constraint "user_progress_level_check" CHECK (((level)::text = ANY ((ARRAY['explorador'::character varying, 'leitor'::character varying, 'escritor'::character varying, 'mestre'::character varying])::text[]))) not valid;

alter table "public"."user_progress" validate constraint "user_progress_level_check";

alter table "public"."user_progress" add constraint "user_progress_user_id_foreign" FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE not valid;

alter table "public"."user_progress" validate constraint "user_progress_user_id_foreign";

alter table "public"."users" add constraint "users_email_unique" UNIQUE using index "users_email_unique";

alter table "public"."users" add constraint "users_school_id_foreign" FOREIGN KEY (school_id) REFERENCES public.schools(id) ON DELETE SET NULL not valid;

alter table "public"."users" validate constraint "users_school_id_foreign";

alter table "public"."word_syllables" add constraint "word_syllables_syllable_id_foreign" FOREIGN KEY (syllable_id) REFERENCES public.syllables(id) ON DELETE CASCADE not valid;

alter table "public"."word_syllables" validate constraint "word_syllables_syllable_id_foreign";

alter table "public"."word_syllables" add constraint "word_syllables_word_id_foreign" FOREIGN KEY (word_id) REFERENCES public.words(id) ON DELETE CASCADE not valid;

alter table "public"."word_syllables" validate constraint "word_syllables_word_id_foreign";

alter table "public"."word_syllables" add constraint "word_syllables_word_id_position_unique" UNIQUE using index "word_syllables_word_id_position_unique";

alter table "public"."words" add constraint "words_word_unique" UNIQUE using index "words_word_unique";

set check_function_bodies = off;

CREATE OR REPLACE FUNCTION public.rls_auto_enable()
 RETURNS event_trigger
 LANGUAGE plpgsql
 SECURITY DEFINER
 SET search_path TO 'pg_catalog'
AS $function$
DECLARE
  cmd record;
BEGIN
  FOR cmd IN
    SELECT *
    FROM pg_event_trigger_ddl_commands()
    WHERE command_tag IN ('CREATE TABLE', 'CREATE TABLE AS', 'SELECT INTO')
      AND object_type IN ('table','partitioned table')
  LOOP
     IF cmd.schema_name IS NOT NULL AND cmd.schema_name IN ('public') AND cmd.schema_name NOT IN ('pg_catalog','information_schema') AND cmd.schema_name NOT LIKE 'pg_toast%' AND cmd.schema_name NOT LIKE 'pg_temp%' THEN
      BEGIN
        EXECUTE format('alter table if exists %s enable row level security', cmd.object_identity);
        RAISE LOG 'rls_auto_enable: enabled RLS on %', cmd.object_identity;
      EXCEPTION
        WHEN OTHERS THEN
          RAISE LOG 'rls_auto_enable: failed to enable RLS on %', cmd.object_identity;
      END;
     ELSE
        RAISE LOG 'rls_auto_enable: skip % (either system schema or not in enforced list: %.)', cmd.object_identity, cmd.schema_name;
     END IF;
  END LOOP;
END;
$function$
;

grant delete on table "public"."cache" to "anon";

grant insert on table "public"."cache" to "anon";

grant references on table "public"."cache" to "anon";

grant select on table "public"."cache" to "anon";

grant trigger on table "public"."cache" to "anon";

grant truncate on table "public"."cache" to "anon";

grant update on table "public"."cache" to "anon";

grant delete on table "public"."cache" to "authenticated";

grant insert on table "public"."cache" to "authenticated";

grant references on table "public"."cache" to "authenticated";

grant select on table "public"."cache" to "authenticated";

grant trigger on table "public"."cache" to "authenticated";

grant truncate on table "public"."cache" to "authenticated";

grant update on table "public"."cache" to "authenticated";

grant delete on table "public"."cache" to "service_role";

grant insert on table "public"."cache" to "service_role";

grant references on table "public"."cache" to "service_role";

grant select on table "public"."cache" to "service_role";

grant trigger on table "public"."cache" to "service_role";

grant truncate on table "public"."cache" to "service_role";

grant update on table "public"."cache" to "service_role";

grant delete on table "public"."cache_locks" to "anon";

grant insert on table "public"."cache_locks" to "anon";

grant references on table "public"."cache_locks" to "anon";

grant select on table "public"."cache_locks" to "anon";

grant trigger on table "public"."cache_locks" to "anon";

grant truncate on table "public"."cache_locks" to "anon";

grant update on table "public"."cache_locks" to "anon";

grant delete on table "public"."cache_locks" to "authenticated";

grant insert on table "public"."cache_locks" to "authenticated";

grant references on table "public"."cache_locks" to "authenticated";

grant select on table "public"."cache_locks" to "authenticated";

grant trigger on table "public"."cache_locks" to "authenticated";

grant truncate on table "public"."cache_locks" to "authenticated";

grant update on table "public"."cache_locks" to "authenticated";

grant delete on table "public"."cache_locks" to "service_role";

grant insert on table "public"."cache_locks" to "service_role";

grant references on table "public"."cache_locks" to "service_role";

grant select on table "public"."cache_locks" to "service_role";

grant trigger on table "public"."cache_locks" to "service_role";

grant truncate on table "public"."cache_locks" to "service_role";

grant update on table "public"."cache_locks" to "service_role";

grant delete on table "public"."dictation_metrics" to "anon";

grant insert on table "public"."dictation_metrics" to "anon";

grant references on table "public"."dictation_metrics" to "anon";

grant select on table "public"."dictation_metrics" to "anon";

grant trigger on table "public"."dictation_metrics" to "anon";

grant truncate on table "public"."dictation_metrics" to "anon";

grant update on table "public"."dictation_metrics" to "anon";

grant delete on table "public"."dictation_metrics" to "authenticated";

grant insert on table "public"."dictation_metrics" to "authenticated";

grant references on table "public"."dictation_metrics" to "authenticated";

grant select on table "public"."dictation_metrics" to "authenticated";

grant trigger on table "public"."dictation_metrics" to "authenticated";

grant truncate on table "public"."dictation_metrics" to "authenticated";

grant update on table "public"."dictation_metrics" to "authenticated";

grant delete on table "public"."dictation_metrics" to "service_role";

grant insert on table "public"."dictation_metrics" to "service_role";

grant references on table "public"."dictation_metrics" to "service_role";

grant select on table "public"."dictation_metrics" to "service_role";

grant trigger on table "public"."dictation_metrics" to "service_role";

grant truncate on table "public"."dictation_metrics" to "service_role";

grant update on table "public"."dictation_metrics" to "service_role";

grant delete on table "public"."exercise_words" to "anon";

grant insert on table "public"."exercise_words" to "anon";

grant references on table "public"."exercise_words" to "anon";

grant select on table "public"."exercise_words" to "anon";

grant trigger on table "public"."exercise_words" to "anon";

grant truncate on table "public"."exercise_words" to "anon";

grant update on table "public"."exercise_words" to "anon";

grant delete on table "public"."exercise_words" to "authenticated";

grant insert on table "public"."exercise_words" to "authenticated";

grant references on table "public"."exercise_words" to "authenticated";

grant select on table "public"."exercise_words" to "authenticated";

grant trigger on table "public"."exercise_words" to "authenticated";

grant truncate on table "public"."exercise_words" to "authenticated";

grant update on table "public"."exercise_words" to "authenticated";

grant delete on table "public"."exercise_words" to "service_role";

grant insert on table "public"."exercise_words" to "service_role";

grant references on table "public"."exercise_words" to "service_role";

grant select on table "public"."exercise_words" to "service_role";

grant trigger on table "public"."exercise_words" to "service_role";

grant truncate on table "public"."exercise_words" to "service_role";

grant update on table "public"."exercise_words" to "service_role";

grant delete on table "public"."exercises" to "anon";

grant insert on table "public"."exercises" to "anon";

grant references on table "public"."exercises" to "anon";

grant select on table "public"."exercises" to "anon";

grant trigger on table "public"."exercises" to "anon";

grant truncate on table "public"."exercises" to "anon";

grant update on table "public"."exercises" to "anon";

grant delete on table "public"."exercises" to "authenticated";

grant insert on table "public"."exercises" to "authenticated";

grant references on table "public"."exercises" to "authenticated";

grant select on table "public"."exercises" to "authenticated";

grant trigger on table "public"."exercises" to "authenticated";

grant truncate on table "public"."exercises" to "authenticated";

grant update on table "public"."exercises" to "authenticated";

grant delete on table "public"."exercises" to "service_role";

grant insert on table "public"."exercises" to "service_role";

grant references on table "public"."exercises" to "service_role";

grant select on table "public"."exercises" to "service_role";

grant trigger on table "public"."exercises" to "service_role";

grant truncate on table "public"."exercises" to "service_role";

grant update on table "public"."exercises" to "service_role";

grant delete on table "public"."failed_jobs" to "anon";

grant insert on table "public"."failed_jobs" to "anon";

grant references on table "public"."failed_jobs" to "anon";

grant select on table "public"."failed_jobs" to "anon";

grant trigger on table "public"."failed_jobs" to "anon";

grant truncate on table "public"."failed_jobs" to "anon";

grant update on table "public"."failed_jobs" to "anon";

grant delete on table "public"."failed_jobs" to "authenticated";

grant insert on table "public"."failed_jobs" to "authenticated";

grant references on table "public"."failed_jobs" to "authenticated";

grant select on table "public"."failed_jobs" to "authenticated";

grant trigger on table "public"."failed_jobs" to "authenticated";

grant truncate on table "public"."failed_jobs" to "authenticated";

grant update on table "public"."failed_jobs" to "authenticated";

grant delete on table "public"."failed_jobs" to "service_role";

grant insert on table "public"."failed_jobs" to "service_role";

grant references on table "public"."failed_jobs" to "service_role";

grant select on table "public"."failed_jobs" to "service_role";

grant trigger on table "public"."failed_jobs" to "service_role";

grant truncate on table "public"."failed_jobs" to "service_role";

grant update on table "public"."failed_jobs" to "service_role";

grant delete on table "public"."job_batches" to "anon";

grant insert on table "public"."job_batches" to "anon";

grant references on table "public"."job_batches" to "anon";

grant select on table "public"."job_batches" to "anon";

grant trigger on table "public"."job_batches" to "anon";

grant truncate on table "public"."job_batches" to "anon";

grant update on table "public"."job_batches" to "anon";

grant delete on table "public"."job_batches" to "authenticated";

grant insert on table "public"."job_batches" to "authenticated";

grant references on table "public"."job_batches" to "authenticated";

grant select on table "public"."job_batches" to "authenticated";

grant trigger on table "public"."job_batches" to "authenticated";

grant truncate on table "public"."job_batches" to "authenticated";

grant update on table "public"."job_batches" to "authenticated";

grant delete on table "public"."job_batches" to "service_role";

grant insert on table "public"."job_batches" to "service_role";

grant references on table "public"."job_batches" to "service_role";

grant select on table "public"."job_batches" to "service_role";

grant trigger on table "public"."job_batches" to "service_role";

grant truncate on table "public"."job_batches" to "service_role";

grant update on table "public"."job_batches" to "service_role";

grant delete on table "public"."jobs" to "anon";

grant insert on table "public"."jobs" to "anon";

grant references on table "public"."jobs" to "anon";

grant select on table "public"."jobs" to "anon";

grant trigger on table "public"."jobs" to "anon";

grant truncate on table "public"."jobs" to "anon";

grant update on table "public"."jobs" to "anon";

grant delete on table "public"."jobs" to "authenticated";

grant insert on table "public"."jobs" to "authenticated";

grant references on table "public"."jobs" to "authenticated";

grant select on table "public"."jobs" to "authenticated";

grant trigger on table "public"."jobs" to "authenticated";

grant truncate on table "public"."jobs" to "authenticated";

grant update on table "public"."jobs" to "authenticated";

grant delete on table "public"."jobs" to "service_role";

grant insert on table "public"."jobs" to "service_role";

grant references on table "public"."jobs" to "service_role";

grant select on table "public"."jobs" to "service_role";

grant trigger on table "public"."jobs" to "service_role";

grant truncate on table "public"."jobs" to "service_role";

grant update on table "public"."jobs" to "service_role";

grant delete on table "public"."migrations" to "anon";

grant insert on table "public"."migrations" to "anon";

grant references on table "public"."migrations" to "anon";

grant select on table "public"."migrations" to "anon";

grant trigger on table "public"."migrations" to "anon";

grant truncate on table "public"."migrations" to "anon";

grant update on table "public"."migrations" to "anon";

grant delete on table "public"."migrations" to "authenticated";

grant insert on table "public"."migrations" to "authenticated";

grant references on table "public"."migrations" to "authenticated";

grant select on table "public"."migrations" to "authenticated";

grant trigger on table "public"."migrations" to "authenticated";

grant truncate on table "public"."migrations" to "authenticated";

grant update on table "public"."migrations" to "authenticated";

grant delete on table "public"."migrations" to "service_role";

grant insert on table "public"."migrations" to "service_role";

grant references on table "public"."migrations" to "service_role";

grant select on table "public"."migrations" to "service_role";

grant trigger on table "public"."migrations" to "service_role";

grant truncate on table "public"."migrations" to "service_role";

grant update on table "public"."migrations" to "service_role";

grant delete on table "public"."password_reset_tokens" to "anon";

grant insert on table "public"."password_reset_tokens" to "anon";

grant references on table "public"."password_reset_tokens" to "anon";

grant select on table "public"."password_reset_tokens" to "anon";

grant trigger on table "public"."password_reset_tokens" to "anon";

grant truncate on table "public"."password_reset_tokens" to "anon";

grant update on table "public"."password_reset_tokens" to "anon";

grant delete on table "public"."password_reset_tokens" to "authenticated";

grant insert on table "public"."password_reset_tokens" to "authenticated";

grant references on table "public"."password_reset_tokens" to "authenticated";

grant select on table "public"."password_reset_tokens" to "authenticated";

grant trigger on table "public"."password_reset_tokens" to "authenticated";

grant truncate on table "public"."password_reset_tokens" to "authenticated";

grant update on table "public"."password_reset_tokens" to "authenticated";

grant delete on table "public"."password_reset_tokens" to "service_role";

grant insert on table "public"."password_reset_tokens" to "service_role";

grant references on table "public"."password_reset_tokens" to "service_role";

grant select on table "public"."password_reset_tokens" to "service_role";

grant trigger on table "public"."password_reset_tokens" to "service_role";

grant truncate on table "public"."password_reset_tokens" to "service_role";

grant update on table "public"."password_reset_tokens" to "service_role";

grant delete on table "public"."pedidos" to "anon";

grant insert on table "public"."pedidos" to "anon";

grant references on table "public"."pedidos" to "anon";

grant select on table "public"."pedidos" to "anon";

grant trigger on table "public"."pedidos" to "anon";

grant truncate on table "public"."pedidos" to "anon";

grant update on table "public"."pedidos" to "anon";

grant delete on table "public"."pedidos" to "authenticated";

grant insert on table "public"."pedidos" to "authenticated";

grant references on table "public"."pedidos" to "authenticated";

grant select on table "public"."pedidos" to "authenticated";

grant trigger on table "public"."pedidos" to "authenticated";

grant truncate on table "public"."pedidos" to "authenticated";

grant update on table "public"."pedidos" to "authenticated";

grant delete on table "public"."pedidos" to "service_role";

grant insert on table "public"."pedidos" to "service_role";

grant references on table "public"."pedidos" to "service_role";

grant select on table "public"."pedidos" to "service_role";

grant trigger on table "public"."pedidos" to "service_role";

grant truncate on table "public"."pedidos" to "service_role";

grant update on table "public"."pedidos" to "service_role";

grant delete on table "public"."profissional_student" to "anon";

grant insert on table "public"."profissional_student" to "anon";

grant references on table "public"."profissional_student" to "anon";

grant select on table "public"."profissional_student" to "anon";

grant trigger on table "public"."profissional_student" to "anon";

grant truncate on table "public"."profissional_student" to "anon";

grant update on table "public"."profissional_student" to "anon";

grant delete on table "public"."profissional_student" to "authenticated";

grant insert on table "public"."profissional_student" to "authenticated";

grant references on table "public"."profissional_student" to "authenticated";

grant select on table "public"."profissional_student" to "authenticated";

grant trigger on table "public"."profissional_student" to "authenticated";

grant truncate on table "public"."profissional_student" to "authenticated";

grant update on table "public"."profissional_student" to "authenticated";

grant delete on table "public"."profissional_student" to "service_role";

grant insert on table "public"."profissional_student" to "service_role";

grant references on table "public"."profissional_student" to "service_role";

grant select on table "public"."profissional_student" to "service_role";

grant trigger on table "public"."profissional_student" to "service_role";

grant truncate on table "public"."profissional_student" to "service_role";

grant update on table "public"."profissional_student" to "service_role";

grant delete on table "public"."schools" to "anon";

grant insert on table "public"."schools" to "anon";

grant references on table "public"."schools" to "anon";

grant select on table "public"."schools" to "anon";

grant trigger on table "public"."schools" to "anon";

grant truncate on table "public"."schools" to "anon";

grant update on table "public"."schools" to "anon";

grant delete on table "public"."schools" to "authenticated";

grant insert on table "public"."schools" to "authenticated";

grant references on table "public"."schools" to "authenticated";

grant select on table "public"."schools" to "authenticated";

grant trigger on table "public"."schools" to "authenticated";

grant truncate on table "public"."schools" to "authenticated";

grant update on table "public"."schools" to "authenticated";

grant delete on table "public"."schools" to "service_role";

grant insert on table "public"."schools" to "service_role";

grant references on table "public"."schools" to "service_role";

grant select on table "public"."schools" to "service_role";

grant trigger on table "public"."schools" to "service_role";

grant truncate on table "public"."schools" to "service_role";

grant update on table "public"."schools" to "service_role";

grant delete on table "public"."sessions" to "anon";

grant insert on table "public"."sessions" to "anon";

grant references on table "public"."sessions" to "anon";

grant select on table "public"."sessions" to "anon";

grant trigger on table "public"."sessions" to "anon";

grant truncate on table "public"."sessions" to "anon";

grant update on table "public"."sessions" to "anon";

grant delete on table "public"."sessions" to "authenticated";

grant insert on table "public"."sessions" to "authenticated";

grant references on table "public"."sessions" to "authenticated";

grant select on table "public"."sessions" to "authenticated";

grant trigger on table "public"."sessions" to "authenticated";

grant truncate on table "public"."sessions" to "authenticated";

grant update on table "public"."sessions" to "authenticated";

grant delete on table "public"."sessions" to "service_role";

grant insert on table "public"."sessions" to "service_role";

grant references on table "public"."sessions" to "service_role";

grant select on table "public"."sessions" to "service_role";

grant trigger on table "public"."sessions" to "service_role";

grant truncate on table "public"."sessions" to "service_role";

grant update on table "public"."sessions" to "service_role";

grant delete on table "public"."syllables" to "anon";

grant insert on table "public"."syllables" to "anon";

grant references on table "public"."syllables" to "anon";

grant select on table "public"."syllables" to "anon";

grant trigger on table "public"."syllables" to "anon";

grant truncate on table "public"."syllables" to "anon";

grant update on table "public"."syllables" to "anon";

grant delete on table "public"."syllables" to "authenticated";

grant insert on table "public"."syllables" to "authenticated";

grant references on table "public"."syllables" to "authenticated";

grant select on table "public"."syllables" to "authenticated";

grant trigger on table "public"."syllables" to "authenticated";

grant truncate on table "public"."syllables" to "authenticated";

grant update on table "public"."syllables" to "authenticated";

grant delete on table "public"."syllables" to "service_role";

grant insert on table "public"."syllables" to "service_role";

grant references on table "public"."syllables" to "service_role";

grant select on table "public"."syllables" to "service_role";

grant trigger on table "public"."syllables" to "service_role";

grant truncate on table "public"."syllables" to "service_role";

grant update on table "public"."syllables" to "service_role";

grant delete on table "public"."user_progress" to "anon";

grant insert on table "public"."user_progress" to "anon";

grant references on table "public"."user_progress" to "anon";

grant select on table "public"."user_progress" to "anon";

grant trigger on table "public"."user_progress" to "anon";

grant truncate on table "public"."user_progress" to "anon";

grant update on table "public"."user_progress" to "anon";

grant delete on table "public"."user_progress" to "authenticated";

grant insert on table "public"."user_progress" to "authenticated";

grant references on table "public"."user_progress" to "authenticated";

grant select on table "public"."user_progress" to "authenticated";

grant trigger on table "public"."user_progress" to "authenticated";

grant truncate on table "public"."user_progress" to "authenticated";

grant update on table "public"."user_progress" to "authenticated";

grant delete on table "public"."user_progress" to "service_role";

grant insert on table "public"."user_progress" to "service_role";

grant references on table "public"."user_progress" to "service_role";

grant select on table "public"."user_progress" to "service_role";

grant trigger on table "public"."user_progress" to "service_role";

grant truncate on table "public"."user_progress" to "service_role";

grant update on table "public"."user_progress" to "service_role";

grant delete on table "public"."users" to "anon";

grant insert on table "public"."users" to "anon";

grant references on table "public"."users" to "anon";

grant select on table "public"."users" to "anon";

grant trigger on table "public"."users" to "anon";

grant truncate on table "public"."users" to "anon";

grant update on table "public"."users" to "anon";

grant delete on table "public"."users" to "authenticated";

grant insert on table "public"."users" to "authenticated";

grant references on table "public"."users" to "authenticated";

grant select on table "public"."users" to "authenticated";

grant trigger on table "public"."users" to "authenticated";

grant truncate on table "public"."users" to "authenticated";

grant update on table "public"."users" to "authenticated";

grant delete on table "public"."users" to "service_role";

grant insert on table "public"."users" to "service_role";

grant references on table "public"."users" to "service_role";

grant select on table "public"."users" to "service_role";

grant trigger on table "public"."users" to "service_role";

grant truncate on table "public"."users" to "service_role";

grant update on table "public"."users" to "service_role";

grant delete on table "public"."word_syllables" to "anon";

grant insert on table "public"."word_syllables" to "anon";

grant references on table "public"."word_syllables" to "anon";

grant select on table "public"."word_syllables" to "anon";

grant trigger on table "public"."word_syllables" to "anon";

grant truncate on table "public"."word_syllables" to "anon";

grant update on table "public"."word_syllables" to "anon";

grant delete on table "public"."word_syllables" to "authenticated";

grant insert on table "public"."word_syllables" to "authenticated";

grant references on table "public"."word_syllables" to "authenticated";

grant select on table "public"."word_syllables" to "authenticated";

grant trigger on table "public"."word_syllables" to "authenticated";

grant truncate on table "public"."word_syllables" to "authenticated";

grant update on table "public"."word_syllables" to "authenticated";

grant delete on table "public"."word_syllables" to "service_role";

grant insert on table "public"."word_syllables" to "service_role";

grant references on table "public"."word_syllables" to "service_role";

grant select on table "public"."word_syllables" to "service_role";

grant trigger on table "public"."word_syllables" to "service_role";

grant truncate on table "public"."word_syllables" to "service_role";

grant update on table "public"."word_syllables" to "service_role";

grant delete on table "public"."words" to "anon";

grant insert on table "public"."words" to "anon";

grant references on table "public"."words" to "anon";

grant select on table "public"."words" to "anon";

grant trigger on table "public"."words" to "anon";

grant truncate on table "public"."words" to "anon";

grant update on table "public"."words" to "anon";

grant delete on table "public"."words" to "authenticated";

grant insert on table "public"."words" to "authenticated";

grant references on table "public"."words" to "authenticated";

grant select on table "public"."words" to "authenticated";

grant trigger on table "public"."words" to "authenticated";

grant truncate on table "public"."words" to "authenticated";

grant update on table "public"."words" to "authenticated";

grant delete on table "public"."words" to "service_role";

grant insert on table "public"."words" to "service_role";

grant references on table "public"."words" to "service_role";

grant select on table "public"."words" to "service_role";

grant trigger on table "public"."words" to "service_role";

grant truncate on table "public"."words" to "service_role";

grant update on table "public"."words" to "service_role";


  create policy "Users can insert their own progress"
  on "public"."user_progress"
  as permissive
  for insert
  to public
with check ((auth.uid() = user_id));



  create policy "Users can update their own progress"
  on "public"."user_progress"
  as permissive
  for update
  to public
using ((auth.uid() = user_id));



  create policy "Users can view their own progress"
  on "public"."user_progress"
  as permissive
  for select
  to public
using ((auth.uid() = user_id));



