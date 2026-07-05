<?php

namespace App\Support;

use App\Enums\AppKey;

class AppContext
{
    private ?AppKey $current = null;

    public function current(): ?AppKey
    {
        return $this->current;
    }

    public function setCurrent(AppKey $app): void
    {
        $this->current = $app;
    }

    public function clear(): void
    {
        $this->current = null;
    }
}
