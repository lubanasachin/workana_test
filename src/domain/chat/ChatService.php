<?php

namespace app\domain\chat;

/**
 * Chat app service 
 * @author: Sachin Lubana
*/

class ChatService {

    private $redisHost;
    private $redisPort;
    private $allowBlankReferrer;
    private $allowedDomains;
    private $sessionHash;    

    /**
     * default Constructor
     * @params: string $envfile path
    */
    public function __construct($envfile) {
        $env = new \Dotenv\Dotenv($envfile);
        $env->load();
    }

    /**
     * init execution
     * @params
    */
    public function init() {
        try {
            $this->checkEnvConfig();
            $this->checkOrigin();
            $this->checkSession();
            $this->run();
        } catch (Exception $e) {
            Response::sendResponse(true, 500, 'Unknown exception. Cause: ' . $e->getMessage());
        }
    }

    /**
     * check environment configuration
     * @params
    */
    private function checkEnvConfig() {
        $this->redisHost = getenv('REDIS_HOST');
        $this->redisPort = getenv('REDIS_PORT');
        $this->allowedDomains = explode(',', getenv('ALLOWED_DOMAINS'));
        $this->allowBlankReferrer = getenv('ALLOW_BLANK_REFERRER');

        if(empty($this->redisHost) || empty($this->redisPort) || empty($this->allowedDomains) || !is_array($this->allowedDomains)) {
            Response::sendResponse(true, 500, 'Server error, invalid configuration.');
        }
    }

    /**
     * check origin domain allowed
     * @params
    */    
    private function checkOrigin() {
        $httpOrigin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
        if($this->allowBlankReferrer == 'true' || in_array($httpOrigin, $this->allowedDomains)) {
            header('Access-Control-Allow-Credentials: true');
            if($httpOrigin) header("Access-Control-Allow-Origin: $httpOrigin");
        } else Response::sendResponse(true, 403, 'Not a valid origin.');              
    }

    /**
     * check session
     * @params
    */
    private function checkSession() {
        if(empty($_COOKIE['app'])) Response::sendResponse(true, 403, 'Not a valid session.');
        $this->sessionHash = $_COOKIE['app'];
    }

    /**
     * start execution
     * @params
    */
    private function run() {
        $redis = new RedisStore($this->redisHost,$this->redisPort);
        $friendList = $redis->getOnlineFriendList($this->sessionHash);
        if(!$friendsList) Response::sendResponse(false, 200, []);
        else Response::sendResponse(true, 404, 'Friends list not available.');        
    }

}