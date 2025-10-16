<?php

namespace Jiny\Auth\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthMailTemplate;

/**
 * 메일 템플릿 상세 보기 컨트롤러
 */
class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $template = AuthMailTemplate::findOrFail($id);

        return view('jiny-auth::admin.mail.template.show', [
            'template' => $template,
        ]);
    }
}