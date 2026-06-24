<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ModelActivityObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        // In the `updated` event, changed values are available via getChanges().
        $changed = $model->getChanges();
        unset($changed['updated_at']);

        if (empty($changed)) {
            return;
        }

        $original = $model->getOriginal();
        $changes = [];

        foreach ($changed as $field => $newValue) {
            $changes[$field] = [
                'from' => $original[$field] ?? null,
                'to' => $newValue,
            ];
        }

        $this->log('updated', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    private function log(string $action, Model $model, array $changes = []): void
    {
        try {
            $label = $this->getLabel($model);
            $name = str(class_basename($model))->snake(' ')->title();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'description' => "{$name} \"{$label}\" was {$action}",
                'changes' => empty($changes) ? null : $changes,
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    private function getLabel(Model $model): string
    {
        return $model->name
            ?? $model->customer_name
            ?? $model->email
            ?? $model->phone
            ?? (isset($model->date) ? (string) $model->date : null)
            ?? "#{$model->getKey()}";
    }
}
