<?php

namespace BalajiDharma\LaravelMenu\Traits;

if (class_exists(\Spatie\Activitylog\ActivitylogServiceProvider::class)) {
    trait HasLogsActivity
    {
        use \Spatie\Activitylog\Traits\LogsActivity;

        public $hasLogsActivity = true;

        public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
        {
            return \Spatie\Activitylog\LogOptions::defaults()
                ->logAll()
                ->logExcept(['created_at', 'updated_at'])
                ->logOnlyDirty()
                ->dontSubmitEmptyLogs()
                ->setDescriptionForEvent(fn (string $eventName) => "{$this->getActivitylogModelName()} has been {$eventName}");
        }

        public function getActivitylogModelName(): string
        {
            return class_basename($this);
        }
    }
} else {
    trait HasLogsActivity
    {
        public $hasLogsActivity = false;
    }
}
