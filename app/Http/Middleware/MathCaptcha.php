<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MathCaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post')) {
            $captchaResult = session('math_captcha_result');
            $userAnswer = $request->input('captcha_answer');

            if (!$captchaResult || !$userAnswer || (int)$userAnswer !== (int)$captchaResult) {
                return back()->withErrors(['captcha' => 'Invalid captcha answer.'])->withInput();
            }
        }

        // Generate new captcha for the next request
        $num1 = rand(1, 20);
        $num2 = rand(1, 20);
        $result = $num1 + $num2;

        session(['math_captcha_result' => $result]);
        session(['math_captcha_equation' => "$num1 + $num2 = ?"]);

        return $next($request);
    }
} 