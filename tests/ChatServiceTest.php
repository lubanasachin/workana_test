<?php
/**
 * ChatService Unit Tests
*/
class ChatService extends PHPUnit_Framework_TestCase {
     
    protected function setUp() {
        $this->curlService = new curlService();
        require __DIR__ . '/../vendor/autoload.php';
    }
    
    /**
     * Case: Valid user, session & origin
     * @tags session, origin 
    */
    public function testValidUseCase() {
        $response = $this->curlService->curlMe("valid");
    }

    /**
     * Case: Invalid session, cookie does not exists
     * @tags session 
    */
    public function testInvalidCookie() {
        $response = $this->curlService->curlMe("invalidcookie");
    } 

    /**
     * Case: Valid user with invalid origin
     * @tags origin 
    */
    public function testInvalidOrigin() {
        $response = $this->curlService->curlMe("invalidorigin");
    }    

    /**
     * Case: Valid origin but with session cookie empty 
     * @tags session 
    */
    public function testInvalidSession() {
        $response = $this->curlService->curlMe("invalidsession");
    }

    /**
     * Case: Valid user, session & origin but no friends
     * @tags session, origin 
    */
    public function testNoFriends() {
        $response = $this->curlService->curlMe("nofriends");
    }    
}

class curlService {
    private $url = "http://localhost:8080";
    public function curlMe($reqtype) {
        $headerData = array();
        switch($reqtype) {
            case 'valid':
                echo "\n----------Run Valid Use-case----------\n\n";
                $headerData = array("Cookie: app=hash","Origin: http://localhost");
                break;
            case 'invalidcookie':
                echo "\n----------Run Invalid cookie Use-case----------\n\n";
                $headerData = array("Cookie: app1=hash","Origin: http://localhost");
                break;                
            case 'invalidorigin':
                echo "\n----------Run invalid origin Use-case----------\n\n";
                $headerData = array("Cookie: app=hash","Origin: http://wax.com");
                break;
            case 'invalidsession':
                echo "\n----------Run Invalid session Use-case----------\n\n";
                $headerData = array("Cookie: app=","Origin: http://localhost");
                break;
            case 'nofriends':
                echo "\n----------Run for users with no friends Use-case----------\n\n";
                $headerData = array("Cookie: app=hash1","Origin: http://localhost");
                break;                                                
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RANGE,"1-2000000");
        $result = curl_exec($ch); 
        $result = curl_error($ch) ? curl_error($ch) : $result;
        curl_close($ch);
        echo "Response:$result\n\n";
        return $result;
    }
}