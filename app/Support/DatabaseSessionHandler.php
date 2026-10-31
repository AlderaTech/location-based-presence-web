<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;

class DatabaseSessionHandler extends BaseDatabaseSessionHandler
{
    protected function addUserInformation(&$payload)
    {
        if ($this->container?->bound(Guard::class)) {
            $payload['id_pengguna'] = $this->userId();
        }

        return $this;
    }
}
