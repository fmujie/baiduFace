<?php

namespace Fmujie\BaiduFace;

use Fmujie\BaiduFace\Libs\AipFace;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 百度人脸识别SDK库
 * @package Fmujie\BaiduFace
 */
class BaiduFaceApi{

    /**
     * @return array
     */
    public static function loadConfig()
    {
        $config = [
            'face_appID'  => config('laravel-baidu-face.face_app_id'),
            'face_apiKey' => config('laravel-baidu-face.face_api_key'),
            'face_secretKey' => config('laravel-baidu-face.face_secret_key'),
        ];
        return $config;
    }

    /**
     * 图像搜索
     *
     * @param $image BASE64/URL/FACE_TOKEN 要搜索进行对比的人脸图像，取决于image_type参数，传入BASE64字符串或URL字符串或FACE_TOKEN字符串
     * @param $groupIdList string 从指定的group中进行查找 用逗号分隔，上限20个
     * @param $user_id string 要进行对比人脸的唯一标识Uid
     * @param $imageType string 决定传入的$image以什么种类
     * @param $imgIsBase64 bool 根据需求指定传入的是图像二进制流OR"BASE64"编码后的字符串
     * @param $max_face_num string 最多处理人脸的数目,默认值为1(仅检测图片中面积最大的那个人脸) 最大值10
     * @param $match_threshold string 匹配阈值（设置阈值后，score低于此阈值的用户信息将不会返回） 最大100 最小0 默认80 注：此阈值设置得越高，检索速度将会越快，推荐使用默认阈值80
     * @param $quality_control string 图片质量控制 NONE: 不进行控制 LOW:较低的质量要求 NORMAL: 一般的质量要求 HIGH: 较高的质量要求 默认 NONE
     * @param $liveness_control string 活体检测控制 NONE: 不进行控制 LOW:较低的活体要求(高通过率 低攻击拒绝率) NORMAL: 一般的活体要求(平衡的攻击拒绝率, 通过率) HIGH: 较高的活体要求(高攻击拒绝率 低通过率) 默认NONE
     * @return array
     */

    
    public static function searchFaceUid($image, $groupIdList = null, $user_id = null, $imageType = 'BASE64', $imgIsBase64 = false, $max_face_num = '1', $match_threshold = '80', $quality_control = 'NONE', $liveness_control = 'NONE')
    {
        $return = [
            'code' => 0,
            'status' => 'error',
            'msg' => '人脸Uid搜索失败',
            'score' => null,
            'face_token' => null
        ];

        if(($groupIdList == null) || ($user_id == null)) {
            $return['msg'] = '缺少用户组或ID用户ID';
            return $return;
        }

        if(!$imgIsBase64) {
            switch ($imageType) {
                case 'BASE64':
                    $image = base64_encode($image);
                    break;
                case 'URL':
                    $urlJudgeRes = self::judgeUrl($image); 
                    if(!$urlJudgeRes){
                        $return['msg'] = '图片URL解析失败';
                        return $return;
                    }
                    break;
                case 'FACE_TOKEN':
                    break;
                default:
                    $return['msg'] = '参数imageType未填或填写不支持的格式';
                    return $return;
            }
        } else {
            $imageType = 'BASE64';
            $res = self::is_base64($image);
            if(!$res) {
                $return['msg'] = '参数image非BASE64格式';
                return $return;
            }
        }

        $config = self::loadConfig();
        $faceClient = new AipFace($config['face_appID'], $config['face_apiKey'], $config['face_secretKey']);
        $options = [
            'max_face_num' => "$max_face_num",
            'match_threshold' => "$match_threshold",
            'quality_control' => "$quality_control",
            'liveness_control' => "$liveness_control",
            'user_id' => "$user_id",
            'max_user_num' => '1'
        ];
        $response = $faceClient->search($image, $imageType, $groupIdList, $options);
        $res_code = $response['error_code'];
        $res_msg = $response['error_msg'];
        
        if($res_code == 0)
        {
            $recResFaceToken = $response['result']['face_token'];
            $recResScore = $response['result']['user_list'][0]['score'];
            $return = [
                'code' => 1,
                'status' => 'success',
                'msg' => '人脸Uid搜索成功',
                'score' => $recResScore,
                'face_token' => $recResFaceToken
            ];
        } else {
            $return['msg'] = '人脸Uid搜索失败误,错误码:'.$response['error_code'].',错误详情信息:'.$response['error_msg'];
        }

        return $return;
    }

    public static function is_base64($str)
    {  
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }

    public static function is_image($str)
    {
        $res = imagecreatefromstring($str);
        if($res !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function judgeUrl($url)
    {
        $urlTF = false;
        $client = new Client([
            'base_uri' => "$url",
            'timeout'  => 2.0,
        ]);
        try {
            $res = $client->request('GET');
            $urlResCode =  $res->getStatusCode();
            $urlResHeader = $res->getHeader('content-type');
            if(($urlResCode == 200) && ($urlResHeader[0]) == 'image/jpeg') {
                $urlTF = true;
            }
            return $urlTF;
        } catch(RequestException $error) {
            return $urlTF;
        }
    }
    
}