<?php

namespace App\Exceptions\Api;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

trait RestExceptionHandlerTrait
{
    /**
     * Determines if request is an api call.
     *
     * If the request URI contains '/api' or expectsJson.
     *
     * @param Request $request
     * @return bool
     */
    protected function isApiCall(Request $request)
    {
        return $request->expectsJson();
    }
    private function handleApiException($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof HttpResponseException) {
            $exception = $exception->getResponse();
        } elseif ($exception instanceof AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        } elseif ($exception instanceof ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }
        if ($exception instanceof \Illuminate\Database\QueryException) {
            $exception = response()->json([
                'message' => $exception->getMessage(),
            ], 419);
        }
        return $this->customApiResponse($exception);
    }
    private function customApiResponse($exception)
    {
        $isDebug = config('app.debug');
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } elseif (method_exists($exception, 'getHttpStatusCode')) {
            $statusCode = $exception->getHttpStatusCode();
        } else {
            $statusCode = 500;
        }
        $response = [];
        switch ($statusCode) {
            case 400:
                $response['message'] = $exception->getMessage();
                break;
            case 401:
                $response['message'] = trans('response.401');
                break;
            case 403:
                $response['message'] = trans('response.403');
                break;
            case 404:
                $response['message'] = trans('response.404');
                break;
            case 405:
                $response['message'] = trans('response.405');
                break;
            case 429:
                $response['message'] = trans('response.429');
                break;
            case 422:
                $response['message'] = $exception->original['message'];
                $response['errors'] = $exception->original['errors'];
                break;
            case 419:

                if ($isDebug) {
                    if (method_exists($exception, 'getMessage')) {
                        $response['message'] = (!$isDebug) ? trans('response.419') : $exception->getMessage();
                    } else {
                        $response['message'] = isset($exception->original) ? $exception->original['message'] : '';
                    }

                    if (empty($response['message'])) {
                        $response['message'] = isset($exception['message']) ? $exception['message'] : '';
                    }
                } else {
                    $response['message'] = trans('response.419');
                }
                break;
            default:
                if (method_exists($exception, 'getMessage')) {
                    $response['message'] = (!$isDebug) ? trans('response.500') : $exception->getMessage();
                } else {
                    $response['message'] = trans('response.500');
                }
                break;
        }

        if (method_exists($exception, 'getHint')) {
            $response['hint'] = $exception->getHint();
        }
        $response['status'] = $statusCode;
        if ($isDebug) {
            $response['exception'] = get_class($exception);
            if (method_exists($exception, 'getTrace')) {
                $response['trace'] = $exception->getTrace();
            }
        }
        return response()->json($response, $statusCode);
    }
}
