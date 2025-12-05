<?php

namespace App\Traits\Wagons;

trait WagonStateTrait
{
    public function getWagonStateLabelAttribute(): string
    {
        if ($this->state === 'Порожний' && $this->containers->isEmpty()) {
            return 'Порожний вагон';
        }

        if ($this->state === 'Груженный' && $this->containers->isEmpty()) {
            return 'Груж. вагон (с грузом)';
        }

        if ($this->state === 'Груженный' && $this->containers->isNotEmpty()) {
            return 'Груж. вагон (с груж. конт.)';
        }

        if ($this->state === 'Порожний' && $this->containers->isNotEmpty()) {
            return 'Груж. вагон (с порож. конт.)';
        }

        return 'Не определено';
    }

    public function getWagonContainersNumberAttribute(): string
    {
        if ($this->relationLoaded('containers')) {
            $numbers = $this->containers->pluck('container_number')->filter()->toArray();
        } else {
            $numbers = $this->containers()->pluck('container_number')->toArray();
        }

        return implode(', ', $numbers);
    }
}
