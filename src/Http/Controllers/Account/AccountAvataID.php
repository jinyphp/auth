<?php
namespace Jiny\Auth\Http\Controllers\Account;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 id에 대한 아바타 이미지를 반환 합니다.
 */
class AccountAvataID extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->id;
        if($user_id) {
            if($res = $this->avata($user_id)) {
                return $res;
            }
        }

        // 정보가 없는 경우 기본값 출력
        return $this->blankImage();
    }

    private function avata($user_id)
    {
        $path = storage_path('app').DIRECTORY_SEPARATOR;
        $profile = DB::table("user_avata")
                ->where('user_id',$user_id)
                ->first();
        if($profile) {

            $extension = $this->getExt($profile->image);
            $content_Type = $this->extType($extension);

            if(file_exists($path.$profile->image)) {
                $body = file_get_contents($path.$profile->image);
                return response($body)
                    ->header('Content-type',$content_Type);
            }
        }

        return false;
    }

    private function getExt($file)
    {
        return substr($file, strrpos($file, '.')+1);
    }

    private function extType($extension)
    {
        if($extension == "gif") {
            return "image/gif";
        } else if($extension == "jpg") {
            return "image/jpeg";
        } else if($extension == "png") {
            return "image/png";
        } else if($extension == "svg") {
            return "image/svg+xml";
        }
    }

    private function blankImage()
    {
        $packageReourcePath =__DIR__."/../../../../resources/images";
        $packageReourcePath .= "/blank-profile.png";

        if(file_exists($packageReourcePath)) {
            $body = file_get_contents($packageReourcePath);

            $extension = $this->getExt($packageReourcePath);
            $content_Type = $this->extType($extension);

            return response($body)
                ->header('Content-type',$content_Type);
        }

        return "프로파일 정보가 없습니다.";
    }


}
