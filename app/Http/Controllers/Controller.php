<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return error response.
     *
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    protected function errorResponse($message, $status = 404)
    {
        return response([
            'status' => 'error',
            'message' => $message
        ], $status);
    }

    /**
     * Return success response.
     *
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    protected function successResponse($message, $status = 200)
    {
        return response([
            'status' => 'success',
            'message' => $message
        ], $status);
    }

    /**
     * Wrap response.
     *
     * @param array $data
     * @return array
     */
    protected function wrapResponse($data)
    {
        return [
            'status' => 'success',
            'data' => $data
        ];
    }
}
