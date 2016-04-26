<?php

	namespace app\domain\chat;

	/**
	 * Redis Store service 
	 * @author: Sachin Lubana
	*/

	class RedisStore {

		/** redis connection*/
		private $redisCon;
		/** redis connection details*/
		private $redisConDet = array("host"=>"","port"=>"");

	    const FRIENDS_CACHE_PREFIX_KEY = 'chat:friends:{:userId}';
	    const ONLINE_CACHE_PREFIX_KEY =  'chat:online:{:userId}';		

		/**
		 * default constructor
		 * @params string $redisHost & $redisPort
		*/		
		public function __construct($redisHost='',$redisPort='') {
			list($this->redisConDet['host'],$this->redisConDet['port']) = array($redisHost,$redisPort);
			$this->getInstance();
		}

		/**
		 * get redis instance, if already created or else create new
		 * @params 
		*/
		public function getInstance() {
			if($this->redisCon && $this->redisCon->isConnected) return $this->redisCon;
			$this->connect();
		}

		/**
		 * create new redis connection
		 * @params 
		*/
		private function connect() {
			$this->redisCon = new \Redis();
			$this->redisCon->connect($this->redisConDet['host'], $this->redisConDet['port']);
			if(!$this->redisCon->isConnected()) Response::sendResponse(true, 500, 'Server error, can\'t connect.');
			$this->redisCon->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
		}
			
		/**
		 * get session ID from session hash extracted from cookie
		 * @params string $sessionHash
   		*/
		private function getSessionId($sessionHash) {
	        $session = $this->redisCon->get(join(':', ['PHPREDIS_SESSION', $sessionHash]));
	        if (!empty($session['default']['id'])) return $session['default']['id'];
	        return -1;
		}

		/**
		 * get online friends list
		 * @params string $sessionHash
		*/
		public function getOnlineFriendList($sessionHash) {
			$sessionId = $this->getSessionId($sessionHash);
			if($sessionId == '-1') Response::sendResponse(true, 404, 'Friends list not available.');

            $friendsList = $this->redisCon->get(str_replace('{:userId}', $sessionId, self::FRIENDS_CACHE_PREFIX_KEY));
            if(!$friendsList) Response::sendResponse(false, 200, []);

			$friendUserIds = $friendsList->getUserIds();
			$onlineUsers = $this->getOnlineUsersByID($friendUserIds);
			if($onlineUsers)  $friendsList->setOnline($onlineUsers);

			Response::sendResponse(false, 200,$friendsList->toArray());
		}

		/**
		* Get online users by their IDs
		* @params array Friends IDs
		*/
		public function getOnlineUsersByID(array $ids) {
			if (!empty($ids)) {
			    $keys = array_map(function ($userId) {
			        return str_replace('{:userId}', $userId, self::ONLINE_CACHE_PREFIX_KEY);
			    }, $ids);
			    $result = $this->redisCon->mget($keys);
			    return  array_filter(array_combine($ids,$result));
			} else return false;                
		}
	}
