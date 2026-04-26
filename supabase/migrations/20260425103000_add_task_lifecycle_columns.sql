-- Adiciona colunas necessárias para o ciclo de tarefas e métricas associadas a tasks.
-- Idempotente: usa IF NOT EXISTS para todas as alterações.

ALTER TABLE public.dictation_metrics
  ADD COLUMN IF NOT EXISTS task_id uuid;

CREATE INDEX IF NOT EXISTS dictation_metrics_task_id_index
  ON public.dictation_metrics (task_id);

ALTER TABLE public.tasks
  ADD COLUMN IF NOT EXISTS realizado boolean NOT NULL DEFAULT false;

ALTER TABLE public.tasks
  ADD COLUMN IF NOT EXISTS realizado_em timestamptz NULL;

ALTER TABLE public.tasks
  ADD COLUMN IF NOT EXISTS visto_profissional boolean NOT NULL DEFAULT false;
