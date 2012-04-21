<?php
/**
 * Panada Restful class.
 *
 * @package	Resources
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @since	Version 0.1
 */
namespace Resources;

class Rest {
    
    public
	$requestMethod,
	$responseStatus,
	$requestData	    = array(),
	$setRequestHeaders  = array(),
	$responseOutputHeader= false,
	$timeout	    = 30;
    
    public function __construct(){
	
	/**
	* Makesure Curl extension is enabled
	*/
	if( ! extension_loaded('curl') )
	    throw new RunException('Curl extension that required by Rest Resource is not available.');
    }
    
    /**
     * Get clent request type.
     *
     * @return string
     */
    public function getRequest(){
        
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        
        switch ($this->requestMethod){
            case 'GET':
                $this->requestData = $_GET;
                break;
            case 'POST':
                $this->requestData = $_POST;
                break;
            case 'PUT':
                $this->requestData = $this->getPHPInput();
                break;
	    case 'DELETE':
                $this->requestData = $this->getPHPInput();
                break;
        }
        
        return $this->requestData;
    }
    
    /**
     * Get client request headers
     *
     * @return array
     */
    public function getClientHeaders(){
	
	$headers = array();
	
	foreach ($_SERVER as $key => $val){
	    
	    if (substr($key, 0, 5) == 'HTTP_'){
		
		$key = str_replace('_', ' ', substr($key, 5));
		$key = str_replace(' ', '-', ucwords(strtolower($key)));
		
		$headers[$key] = $val;
	    }
	}
	
	return $headers;
    }  
    
    //EN: See this trick at http://www.php.net/manual/en/function.curl-setopt.php#96056
    private function getPHPInput(){
	
	parse_str(file_get_contents('php://input'), $put_vars);
        return $put_vars;
    }
    
    public function sendRequest($uri, $method = 'GET', $data = array()){
	
	$this->setRequestHeaders[]	= 'User-Agent: Panada PHP Framework REST API/0.2';
	$method				= strtoupper($method);
        $url_separator			= ( parse_url( $uri, PHP_URL_QUERY ) ) ? '&' : '?';
        $uri				= ( $method == 'GET' && ! empty($data) ) ? $uri . $url_separator . http_build_query($data) : $uri;
        $c				= curl_init();
	
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_URL, $uri);
	curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
        
        if($this->responseOutputHeader)
            curl_setopt($c, CURLOPT_HEADER, true);
	
        if( $method != 'GET' ) {
	    
	    $data = http_build_query($data);
	    
	    if( $method == 'POST' )
		curl_setopt($c, CURLOPT_POST, true);
	    
	    if( $method == 'PUT' || $method == 'DELETE' ) {
		$this->setRequestHeaders[] = 'Content-Length: ' . strlen($data);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
	    }
	    
	    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        }
	
	curl_setopt($c, CURLOPT_HTTPHEADER, $this->setRequestHeaders);
	curl_setopt($c, CURLINFO_HEADER_OUT, true);
	
        $contents = curl_exec($c);
	$this->responseStatus = curl_getinfo($c, CURLINFO_HTTP_CODE);
	$this->headerSent = curl_getinfo($c, CURLINFO_HEADER_OUT);
        
        curl_close($c);
	
        if($contents)
	    return $contents;
        
        return false;
    }
    
    public function setResponseHeader($code = 200){
	
	Tools::setStatusHeader($code);
    }
    
    public function wrapResponseOutput($data, $format = 'json', $ContentType = 'application'){
        
        header('Content-type: '.$ContentType.'/' . $format);
	
	if($format == 'xml')
	    return Tools::xmlEncode($data);
	else
	    return json_encode($data);
    }
    
}