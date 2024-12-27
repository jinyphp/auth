<?php
namespace Jiny\Auth\Http\Controllers\Account;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 프로파일 이미지 출력
 */
class AccountAvataFile extends Controller
{

    public function avata(Request $request)
    {
        $filename = $request->filename;
        $content_Type = $this->getExt($filename);

        $path = $this->getPath();
        if(file_exists($path.$filename)) {
            $body = file_get_contents($path.$filename);

            return response($body)
                ->header('Content-type',$content_Type);
        }

        return $this->blankImage();
    }

    private function blankImage()
    {
        $packageReourcePath =__DIR__."/../../../../resources/images";
        $packageReourcePath .= "/blank-profile.png";

        if(file_exists($packageReourcePath)) {
            $body = file_get_contents($packageReourcePath);

            $content_Type = $this->getExt($packageReourcePath);
            return response($body)
                ->header('Content-type',$content_Type);
        }

        return "프로파일 정보가 없습니다.";
    }

    private function getPath()
    {
        return storage_path('app/account/avatas').DIRECTORY_SEPARATOR;
    }



    private function getExt($filename)
    {
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

        return $content_Type;
    }

}
