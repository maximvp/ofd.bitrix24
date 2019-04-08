<?
use \Bitrix\Main\Application,
    \Bitrix\Main\Web\HttpClient;

class QueryBpm
{
    public static $autherize = array(
        "UserName" => BPM_USER_NAME,
        "UserPassword" => BPM_USER_PASSWORD
    );
    public static $options = array(
        "redirect" => true,
        "redirectMax" => 5,
        "waitResponse" => true,
        "socketTimeout" => 60,
        "streamTimeout" => 0,
        "version" => HttpClient::HTTP_1_1,
        "compress" => false,
        "charset" => "UTF-8",
        "disableSslVerification" => false,
    );

    public static function readArrayInFile($name)
    {
        $path = \Bitrix\Main\Application::getDocumentRoot() . OFD_BITRIX_TMP;
        $file = fopen($path . $name, 'r');
        $str = "";
        while (($buffer = fgets($file, 128)) !== false) {
            $str .= $buffer;
        }
        $array = unserialize($str);
        return $array;
    }
    public static function login()
    {
        session_name('BitrixBpmConnect');
        session_set_cookie_params(1800);
        session_start();
        $body = json_encode(self::$autherize);
        $httpClient = new HttpClient(self::$options);
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $httpClient->query(HttpClient::HTTP_POST, BPM_URL_AUTORISE, $body);
        $cookies = $httpClient->getCookies()->toArray();
        foreach ($cookies as $socks => $val) {
            if ($socks == ".ASPXAUTH") {
                $arrCookies[".ASPXAUTH"] = $val;
            }
            if ($socks == "BPMCSRF") {
                $arrCookies["BPMCSRF"] = $val;
            }

            $_SESSION["BPM_COOKIES"] = $arrCookies;
        }
        return array($_SESSION["BPM_COOKIES"]);
    }

    public static function jsonDataBpm($jsonData, $url)
    {

        //self::login();
        $Data = json_encode($jsonData, true);
        if (!isset($_SESSION["BPM_COOKIES"])) {
            self::login();
        }
        if(isset($_SESSION["BPM_COOKIES"])){
            $addHttpClient = new HttpClient(self::$options);
            foreach ($_SESSION["BPM_COOKIES"] as $socks => $val) {
                if ($socks == ".ASPXAUTH") {
                    $arrCookies[".ASPXAUTH"] = $val;
                    $addHttpClient->setHeader($socks, $val, false);
                }
                if ($socks == "BPMCSRF") {
                    $arrCookies["BPMCSRF"] = $val;
                    $addHttpClient->setHeader($socks, $val, false);
                }
            }

            $addHttpClient->setCookies($arrCookies);
            $addHttpClient->setHeader('Content-Type', 'application/json', false);
            $addHttpClient->query(HttpClient::HTTP_POST, $url, $Data);
            $insert_result = $addHttpClient->getResult();
            $result["success"] = json_decode($insert_result, true);
            $result["status"] = $addHttpClient->getStatus(); // код статуса ответа
            $result["cookies"] = $_SESSION["BPM_COOKIES"];
            /*if($result["status"] == "403"){
                unset($_SESSION["BPM_COOKIES"]);
                session_name('BitrixBpmConnect_ERROR');
                session_set_cookie_params(4);
                session_start();
                if (isset($_SESSION["BitrixBpmConnect_ERROR"])) {
                    self::jsonDataBpm($jsonData, $url);
                }

            }*/
            $logger = Logger::getLogger('Query','ofd.bitrix24/Query.log');
            $logger->log(array(dataTimeformat(),$result, $_SESSION["BPM_COOKIES"]));
            return ($result);
        }

    }
    public static function jsonDataBpmContr($jsonData, $url)
    {
        //self::login();
        $Data = json_encode($jsonData, true);
        if (!isset($_SESSION["BPM_COOKIES"]) or empty($_SESSION["BPM_COOKIES"])) {
            self::login();
        }

        if (isset($_SESSION["BPM_COOKIES"])) {
            $addHttpClient = new HttpClient(self::$options);
            foreach ($_SESSION["BPM_COOKIES"] as $socks => $val) {
                if ($socks == ".ASPXAUTH") {
                    $arrCookies[".ASPXAUTH"] = $val;
                    $addHttpClient->setHeader($socks, $val, false);
                }
                if ($socks == "BPMCSRF") {
                    $arrCookies["BPMCSRF"] = $val;
                    $addHttpClient->setHeader($socks, $val, false);
                }
            }

            $addHttpClient->setCookies($arrCookies);
            $addHttpClient->setHeader('Content-Type', 'application/json', false);
            $addHttpClient->query(HttpClient::HTTP_POST, $url, $Data);
            $insert_result = $addHttpClient->getResult();
            $result["success"] = json_decode($insert_result, true);
            $result["status"] = $addHttpClient->getStatus(); // код статуса ответа
            $result["cookies"] = $_SESSION["BPM_COOKIES"];
            if ($result["status"] == 403 or $result["status"] == 302) {
                if (empty($_SESSION["BitrixBpmConnect_ERROR"])) {
                    $i = 1;
                }
                unset($_SESSION["BPM_COOKIES"]);
                session_name('BitrixBpmConnect_ERROR');
                //session_set_cookie_params(5);
                session_start();
                $_SESSION["BitrixBpmConnect_ERROR"] = $i;
                if ($_SESSION["BitrixBpmConnect_ERROR"] < 5) {
                    $_SESSION["BitrixBpmConnect_ERROR"] = $i + 1;
                    $result["BitrixBpmConnect_ERROR"] = $_SESSION["BitrixBpmConnect_ERROR"];
                    $logger = Logger::getLogger('connectErrors', 'ofd.bitrix24/connectErrors.log');
                    $logger->log($result);

                    self::jsonDataBpm($jsonData, $url);
                } else {
                    $_SESSION["BitrixBpmConnect_ERROR"] = 0;
                }
            }/**/

            return ($result);
        }
    }

        public static function writeArrayInFile($array, $name)
    {
        $path = \Bitrix\Main\Application::getDocumentRoot() . OFD_BITRIX_TMP;
        $serArray = serialize($array);
        $file = fopen($path . $name, "w+");
        fputs($file, $serArray);
        fclose($file);
    }
}