<?php

return [
    'driver' => $this->env('SESSION_DRIVER', 'redis'),
    'timeout' => $this->env('SESSION_TIMEOUT', 300),
    'encrypt' => $this->env('SESSION_ENCRYPT'),
];
