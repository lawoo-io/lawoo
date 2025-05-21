<?php

namespace Modules\Core\Abstracts;

use Illuminate\Routing\Controller as LaravelController;

abstract class BaseController extends LaravelController
{
    protected function jsonSuccess($data = [], string $message = 'OK')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function jsonError(string $message = 'Error', int $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
