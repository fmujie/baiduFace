包含百度AI平台的人脸UID搜索功能、人脸注册、人脸删除（基于已创建人脸库）

## 安装

 1. 安装包文件

	``` bash
	$ composer require fmujie/baiduface
	```

## 配置

1. 注册 `ServiceProvider`:
	
	```php
	Fmujie\BaiduFace\BaiduFaceServiceProvider::class,
	```

2. 创建配置文件：

	```shell
	php artisan vendor:publish
	```
	
	通常得需要选择`publish`哪一个服务，因为没带参数，选择编号 **[n ]**
	
	~~~bash
	[n ] Provider: Fmujie\BaiduFace\BaiduFaceServiceProvider
	~~~
	
	执行命令后会在 `config` 目录下生成本扩展配置文件：`laravel-baidu-face.php`。
	
3. 在 `.env` 文件中增加如下配置：

	- `BAIDU_FACE_APP_ID`：百度人脸识别`AppId`。

	- `BAIDU_FACE_API_KEY`：百度人脸识别`ApiKey`。

	- `BAIDU_FACE_SECRET_KEY`：百度人脸识别`SecretKey`。

## 使用

1. 人脸UID搜索（仅列出必须）
  
    ```php
    Fmujie\BaiduFace\BaiduFaceApi::searchFaceUid($image, $groupIdList = null, $user_id = null);
    ```
    
    默认参数
    
    ~~~php
    searchFaceUid($image, $groupIdList = null, $user_id = null, $imageType = 'BASE64', $imgIsBase64 = false, $max_face_num = '1', $match_threshold = '80', $quality_control = 'NONE', $liveness_control = 'NONE')
    ~~~
    
    接口字段：
    
    | 参数  | 类型  | 说明  | 可为空  |
    | ------------ | ------------ | ------------ | ------------ |
    | image | Mixed | 取决于image_type参数，传入BASE64字符串或URL字符串或FACE_TOKEN字符串 | N |
    | groupIdList | String | 从指定的group中进行查找 用逗号分隔，上限20个 | N |
    | user_id | String | 要进行对比人脸的唯一标识Uid | N |
    | imageType | String | 决定传入的$image以什么种类 | Y |
    | imgIsBase64 | Bool | 根据需求指定传入的是图像二进制流OR"BASE64"编码后的字符串 | Y |
    | max_face_num | String | 最多处理人脸的数目,默认值为1(仅检测图片中面积最大的那个人脸) 最大值10 | Y |
    | match_threshold | String | 匹配阈值（设置阈值后，score低于此阈值的用户信息将不会返回） 最大100 最小0 默认80 注：此阈值设置得越高，检索速度将会越快，推荐使用默认阈值80 | Y |
    | quality_control | String | 图片质量控制 NONE: 不进行控制 LOW:较低的质量要求 NORMAL: 一般的质量要求 HIGH: 较高的质量要求 默认 NONE | Y |
    | liveness_control | String | 活体检测控制 NONE: 不进行控制 LOW:较低的活体要求(高通过率 低攻击拒绝率) NORMAL: 一般的活体要求(平衡的攻击拒绝率, 通过率) HIGH: 较高的活体要求(高攻击拒绝率 低通过率) 默认NONE | Y |
    
    接口参数字段与原返回字段详细见 [百度人脸识别-人脸搜索官方文档]([https://cloud.baidu.com/doc/FACE/s/zk37c1qrv#%E4%BA%BA%E8%84%B8%E6%90%9C%E7%B4%A2](https://cloud.baidu.com/doc/FACE/s/zk37c1qrv#人脸搜索)).
    
    #### 调用示例
    
    ~~~php
    <?php
    
    namespace App\Http\Controllers\Api;
    
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Fmujie\BaiduFace\BaiduFaceApi;
    
    class TestController extends Controller
    {
        public function imgTest()
        {
            $image = Storage::get('public/example.jpg');
            $res = BaiduFaceApi::searchFaceUid($image, 'xxx', 'xxx');
            return $res;
        }
        
        public function UrlTest()
        {
            $image = 'https://dss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=2534506313,1688529724&fm=26&gp=0.jpg';
            $res = BaiduFaceApi::searchFaceUid($image, 'xxx', 'xxx', 'URL');
            return $res;
        }
        
        public function base64()
        {
            $image = Storage::get('public/example.jpg');
            $image = base64_encode($image);
            $res = BaiduFaceApi::searchFaceUid($image, 'xxx', 'xxx');
            return $res;
        }
        
        public function faceToken()
        {
            $image = 'a3e58f1dfe51db52faba030318786c9d';
            $res = BaiduFaceApi::searchFaceUid($image, 'xxx', 'xxx', 'FACE_TOKEN');
            return $res;
    	}
    }
    ~~~
    
    #### 调用成功返回示例

	~~~json
	{
    	"code": 1,
    	"status": "success",
    	"msg": "人脸Uid搜索成功",
    	"score": 96.488388061523,
    	"face_token": "xxx"
	}
	{
    	"code": 0,
    	"status": "error",
    	"msg": "人脸Uid搜索失败误,错误码:222202,错误详情信息:pic not has face",
    	"score": null,
    	"face_token": null
	}
	{
    	"code": 1,
    	"status": "success",
    	"msg": "人脸Uid搜索成功",
    	"score": 96.488388061523,
    	"face_token": "XXX"
	}
	{
    	"code": 1,
    	"status": "success",
    	"msg": "人脸Uid搜索成功",
    	"score": 96.488388061523,
    	"face_token": "xxx"
	}
	~~~
	
2. 人脸注册（仅列出必须）

     ~~~php
     Fmujie\BaiduFace\BaiduFaceApi::BaiduFaceApi::faceRegistration($image, 'test', '181116', 'BASE64', false, 'APPEND');
     ~~~
     默认参数

     ~~~php
     faceRegistration($image, $groupId = null, $userId = null, $imageType = 'BASE64', $imgIsBase64 = false, $action_type = 'APPEND', $user_info = '', $quality_control = 'NONE', $liveness_control = 'NONE')
     ~~~

     接口字段：

     | 参数  | 类型  | 说明  | 可为空  |
     | ------------ | ------------ | ------------ | ------------ |
     | image | Mixed | 取决于image_type参数，传入BASE64字符串或URL字符串或FACE_TOKEN字符串 | N |
     | groupId | String | 用户组id（由数字、字母、下划线组成），长度限制128B | N |
     | userId | String | 用户id（由数字、字母、下划线组成），长度限制128B | N |
     | imageType | String | 决定传入的$image以什么种类 | Y |
     | imgIsBase64 | Bool | 根据需求指定传入的是图像二进制流OR"BASE64"编码后的字符串 | Y |
     | action_type | String | *操作方式 APPEND: 当user_id在库中已经存在时，对此user_id重复注册时，新注册的图片默认会追加到该user_id下,REPLACE : 当对此user_id重复注册时,则会用新图替换库中该user_id下所有图片,默认使用APPEND* | Y |
     | user_info | String | 用户资料，长度限制256B | Y |
     | quality_control | String | 图片质量控制 NONE: 不进行控制 LOW:较低的质量要求 NORMAL: 一般的质量要求 HIGH: 较高的质量要求 默认 NONE | Y |
     | liveness_control | String | 活体检测控制 NONE: 不进行控制 LOW:较低的活体要求(高通过率 低攻击拒绝率) NORMAL: 一般的活体要求(平衡的攻击拒绝率, 通过率) HIGH: 较高的活体要求(高攻击拒绝率 低通过率) 默认NONE | Y |

3. 人脸删除

     ~~~php
     Fmujie\BaiduFace\BaiduFaceApi::BaiduFaceApi::faceDelete($userId, $groupId, $faceToken);
     ~~~
	接口字段：

     | 参数  | 类型  | 说明  | 可为空  |
     | ------------ | ------------ | ------------ | ------------ |
     | userId | String | *用户id（由数字、字母、下划线组成），长度限制128B* | N |
     | groupId | String | 用户组id（由数字、字母、下划线组成），长度限制128B | N |
     | face_token | String | 需要删除的人脸图片token，（由数字、字母、下划线组成）长度限制64B | N |
     

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
