<?php

namespace Jiny\Auth\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthMailTemplate;

/**
 * 메일 템플릿 수정 폼 컨트롤러
 */
class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $template = AuthMailTemplate::findOrFail($id);

        return view('jiny-auth::admin.mail.template.edit', [
            'template' => $template,
            'typeOptions' => AuthMailTemplate::getTypeOptions(),
        ]);
    }
}