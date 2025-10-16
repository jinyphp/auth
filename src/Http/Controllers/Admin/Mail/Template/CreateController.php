<?php

namespace Jiny\Auth\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthMailTemplate;

/**
 * 메일 템플릿 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-auth::admin.mail.template.create', [
            'typeOptions' => AuthMailTemplate::getTypeOptions(),
        ]);
    }
}