<?php

use WindApi\ApiParser;
use WindApi\session\driver\options\FileOptions;
use WindApi\session\driver\options\MysqlOptions;
use WindApi\session\driver\options\RedisOptions;
use WindApi\Loader;
use WindApi\exceptions\ParserException;
use WindApi\exceptions\SessionRuntimeException;
use WindApi\exceptions\SessionTimeoutException;
use WindApi\request\Tp5ApiRequestProvider;
use WindApi\session\ApiSession;
use WindApi\session\SessionManager;

define("API_INIT",false);


class Index
{

    /**
     * 构造虚拟的请求信息
     */
    public static function virtualRequest(){
        $authorization = [
            'session_id' => '380853FABE3930471FA3',
            'host' => 'http://127.0.0.1:7001',
            'method' => 'get',
            'query' => json_encode([
                urlencode("name") => urlencode("env107"),
                urlencode("title") => urlencode("windapi")
            ]),
            'client_time' => time(),
            'version' => '1.0'
        ];
        $jm = $authorization['host']."#{$authorization['method']}?query=".sha1($authorization['query']);
        $authorization['signature'] = hash_hmac("sha256",$jm,$authorization['client_time']);
        $_SERVER['HTTP_AUTHORIZATION'] = base64_encode(json_encode($authorization));
        $_SERVER['HTTP_ORIGIN'] = "http://127.0.0.1:7001";
        $_SERVER['HTTP_API_INIT'] = API_INIT ? 1 : 0;
        ini_set("display_errors","on");
    }


    public static function main(){
        self::virtualRequest();
        require_once __DIR__ . "/../WindApi/Loader.php";
        Loader::register();
        $is_init = false;
        try {
            @ob_clean();

            //1.验证签名数据
            $requestProvider = new Tp5ApiRequestProvider();
            $package = ApiParser::run(
                $requestProvider
            );

            //2.加载会话驱动
            $options = new FileOptions();
            $options->setSavePath(__DIR__ . "/_sessionData");
            $options->setSessionFileExt(".sf");
            ApiSession::apply("file",$options);


            ApiSession::setExpiresTime(1800);

            $header = $requestProvider->getHeaderInfo();
            $is_init = isset($header['api_init']) && $header['api_init'] == 1 ? true : false;

            if($is_init) {
               //会话初始化模式
                $is_create = false;
                if(isset($package['session_id']) && !empty($package['session_id'])) {
                    $session_id = $package['session_id'];
                    $data = SessionManager::exist($session_id);
                    if(empty($data)){
                        json([
                            'status' => 'fail',
                            'errmsg' => "'{$session_id}'不是一个有效的会话对象"
                        ]);
                    }
                }else{
                    $session_id = ApiSession::create_id();
                    $is_create = true;
                }

                //读取会话，如果会话过期将会抛出SessionTimeoutException异常
                ApiSession::load($session_id);

                //如果当前是创建新的会话则type为update
                if($is_create) {
                    $type = 'update';
                }else{
                    $type = 'ok';
                }

                json([
                    'status' => 'success',
                    'session' => [
                        'type' => $type,
                        'session_id' => $session_id,
                        'server_time' => time()
                    ]
                ]);

                return true;
            } else {
                //正常模式

                //需要检测是否有会话ID
                if(!isset($package['session_id']) || empty($package['session_id'])) {
                    json([
                        'status' => 'fail',
                        'errmsg' => '请初始化会话'
                    ]);
                }

                $session_id = $package['session_id'];
                //再判断会话是否存在
                if(!SessionManager::exist($session_id)) {
                    json([
                        'status' => 'fail',
                        'errmsg' => '不存在该会话，请初始化会话',
                        'unknown_session_id' => $session_id
                    ]);
                }
                //读取会话数据
                $before_session = ApiSession::load($session_id);

                //测试会话
                $_SESSION['name'] = mt_rand(1,1000)."#Session";

                $after_session = $_SESSION;

                json([
                    'status' => 'success',
                    'errmsg' => 'Api测试完成',
                    'data' => [
                        'before' => $before_session,
                        'after' => $after_session
                    ]
                ]);
            }

        } catch (ParserException $parserException) {
            //签名数据验证出错
            json([
                'status' => 'fail',
                'errmsg' => $parserException->getMessage()
            ]);
        } catch (SessionTimeoutException $sessionTimeoutException) {
            //会话超时后，如果模式为会话初始化，则重新创建一个新的会话
            if($is_init) {
                $session_id = ApiSession::create_id();
                ApiSession::load($session_id);
                json([
                    'status' => 'success',
                    'session' => [
                        'session_id' => $session_id,
                        'type' => 'update',
                        'status' => 'over_time'
                    ]
                ]);
            } else {
                //如果为正常模式则报会话过期
                json([
                    'status' => 'fail',
                    'session_id' => $sessionTimeoutException->getTimeoutSessionId(),
                    'err_msg' => '会话已过期'
                ]);
            }
        } catch (SessionRuntimeException $sessionRuntimeException) {
             json([
                'status' => 'fail',
                'err_msg' => $sessionRuntimeException->getMessage()
            ],$sessionRuntimeException);
        } catch (Exception $exception) {
            json([
                'status' => 'fail',
                'err_msg' => "系统错误:".$exception->getMessage()
            ]);
        }

    }
}

function json($data,Exception $exception = null){
    header(
        'content-type:application/json'
    );
    if(!empty($exception)){
        $data['trace'] = $exception->getTrace();
    }
    ApiSession::save();
    echo (json_encode($data));

    exit;
}

//Now you can open 'http://127.0.0.1' in your web explorer
Index::main();