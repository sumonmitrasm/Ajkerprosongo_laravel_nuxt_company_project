<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);
        return in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 10;
    }
}
