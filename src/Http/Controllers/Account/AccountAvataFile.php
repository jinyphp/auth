<?php
namespace Jiny\Auth\Http\Controllers\Account;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountAvataFile extends Controller
{
    /**
     * 사용자 프로파일 이미지 출력
     */
    public function avata(Request $request)
    {
        $filename = $request->filename;
        $extension = substr($filename, strrpos($filename, '.')+1);
        if($extension == "gif") {
            $content_Type="image/gif";
        } else if($extension == "jpg") {
            $content_Type="image/jpeg";
        } else if($extension == "png") {
            $content_Type="image/png";
        } else if($extension == "svg") {
            $content_Type="image/svg+xml";
        }



        //dd($content_Type);

        $path = storage_path('app/avatas').DIRECTORY_SEPARATOR;
        //dd($path.$filename);
        if(file_exists($path.$filename)) {
            $body = file_get_contents($path.$filename);
            return response($body)
                ->header('Content-type',$content_Type);
        }

        return "no file = ";
    }

}
