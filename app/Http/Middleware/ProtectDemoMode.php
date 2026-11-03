<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDemoMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('kwdc.demo')) {
            $message = null;
            if ($request->is('payment/khalti/*', 'payment/esewa/*', 'payment/stripe/*', 'payment/bank/*', 'payment/banking-qr/*')) {
                $message = 'Payments are disabled in this demo. You can review sample invoices and payment history.';
            } elseif ($request->is('register', 'activate', 'activate/*', 'forgot-password', 'reset-password', 'reset-password/*')) {
                $message = 'Use the supplied demo accounts to sign in. Registration and password recovery are unavailable in this demo.';
            } elseif (count($request->allFiles()) > 0) {
                $message = 'File uploads are unavailable in this demo. Use sample records and do not upload personal documents.';
            }

            if ($message) {
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $message], 409)
                    : response()->view('errors.demo', ['message' => $message], 409);
            }
        }

        return $next($request);
    }
}
