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
        $dirty = $model->getDirty();
        // ignore trivial timestamp-only updates
        unset($dirty['updated_at']);
        if (empty($dirty)) return;

        $changes = [];
        foreach ($dirty as $field => $newValue) {
            $changes[$field] = [
                'from' => $model->getOriginal($field),
                'to'   => $newValue,
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
        $label  = $this->getLabel($model);
        $name   = class_basename($model);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'description'=> "{$name} \"{$label}\" was {$action}",
            'changes'    => empty($changes) ? null : $changes,
        ]);
    }

    private function getLabel(Model $model): string
    {
        return $model->name
            ?? $model->customer_name
            ?? (isset($model->date) ? (string) $model->date : null)
            ?? "#{$model->getKey()}";
    }
}
