<?php

namespace App\Exceptions;

use App\Models\Node;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // 修改错误页面（如 404 页）获取方式，优先读取对应的 html 文件
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
            $langcode = langcode('current_page');

            if ($node = Node::findByUrl('/'.$code.'.html', $langcode)) {
                if ($html = $node->getHtml($langcode)) {
                    return Router::toResponse($request, $html);
                }
            }
        }

        return parent::render($request, $exception);
    }
}
