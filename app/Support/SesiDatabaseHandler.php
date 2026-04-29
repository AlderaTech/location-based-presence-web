<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\DatabaseSessionHandler;

class SesiDatabaseHandler extends DatabaseSessionHandler
{
    protected function addUserInformation(&$payload)
    {
        if ($this->container?->bound(Guard::class)) {
            $payload['id_pengguna'] = $this->userId();
        }

        return $this;
    }
}
