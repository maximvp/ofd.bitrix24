<?
use Bitrix\Main\CUserTypeEntity,
    Bitrix\Main\Loader;

Loader::includeModule("iblock");
Loader::includeModule("crm");

class RestBpmBitrix
{

    public static $statusQuery;

    public static function OnRestServiceBuildDescription()
    {
        return array(
            'bpmbitrix' => array(
                'bpm.connect' => array(
                    'callback' => array(__CLASS__, 'connect'),
                    'params' => array("inn" => "")
                ),
                'bpm.contragent.add' => array(
                    'callback' => array(__CLASS__, 'add'),
                    'params' => array("inn" => "")
                ),
                'bpm.contragent.update' => array(
                    'callback' => array(__CLASS__, 'update'),
                    'params' => array("inn" => "", "AccountId" => "", "ActDate" => "")
                ),
                'bpm.tiket.add' => array(
                    'callback' => array(__CLASS__, 'tiketAdd'),
                    'params' => array("id" => "", "number" => "")
                ),
            )
        );
    }

    public static function connect($query, $n, \CRestServer $server)
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
            return array('error' => $query);
        }
        $verify = self::verify($query["params"]["inn"]);
        
        if (!empty($verify)) {
            $result = array('message' => "OK", "accountId" => $verify["guid"]);
            return $result;
        } else {
            $result = array('message' => "Ошибка - контрагент не найден", "accountId" => "");
            return $result;
        }

    }

    public static function verify($inn)
    {
        $entity_id = "CRM_COMPANY";
        $uf_guid = "UF_CRM_COMPANY_GUID";

        $req = new \Bitrix\Crm\EntityRequisite();
        $rser = $req->getList(array(
            "filter" => array(
                'RQ_INN' => $inn,
                "ENTITY_TYPE_ID" => CCrmOwnerType::Company,
                'PRESET_ID' => array(1, 2)
            )
        ));

        $rows = $rser->fetchAll();
        foreach ($rows as $company) {
            $param = array(
                'filter' => array(
                    'ENTITY_ID' => $company['ID'],
                    'ENTITY_TYPE_ID' => CCrmOwnerType::Requisite)
            );
        }

        if ($company["RQ_INN"]) {

            $guidget = GetUserField($entity_id, $company['ENTITY_ID'], $uf_guid);
            $actulDataTimeAdd = metkaColTime($company['ENTITY_ID'],CONTRAGENT_METKA_ADD);
            $actulDataTime = metkaColTime($company['ENTITY_ID'], CONTRAGENT_METKA_UPDATE, 1);

            if (!empty($guidget)) {

                if ($company["ENTITY_TYPE_ID"] == 4) {
                    $result = array("dataUpdateTime"=> $actulDataTime["t"], "id" => $company["ENTITY_ID"], "guid" => $guidget, "inn" => $company["RQ_INN"], "kpp" => $company["RQ_KPP"], "ogrnip" => $company["RQ_OGRNIP"], "okpo" => $company["RQ_OKPO"], "oktmo" => $company["RQ_OKTMO"]);
                } else {
                    $result = array("dataUpdateTime"=> $actulDataTime["t"],"id" => $company["ENTITY_ID"], "guid" => $guidget, "inn" => $company["RQ_INN"], "kpp" => $company["RQ_KPP"], "fulname" => $company["RQ_COMPANY_FULL_NAME"], "ogrn" => $company["RQ_OGRN"], "okpo" => $company["RQ_OKPO"], "oktmo" => $company["RQ_OKTMO"]);
                }
            } else {
                $guid = self::generate_guid();
                SetUserField($entity_id, $company["ENTITY_ID"], $uf_guid, $guid);
                if ($company["ENTITY_TYPE_ID"] == 4) {
                    $result = array("dataUpdateTime"=> $actulDataTime["t"],"id" => $company["ENTITY_ID"], "guid" => $guid, "inn" => $company["RQ_INN"], "kpp" => $company["RQ_KPP"], "ogrnip" => $company["RQ_OGRNIP"], "okpo" => $company["RQ_OKPO"], "oktmo" => $company["RQ_OKTMO"]);
                } else {
                    $result = array("dataUpdateTime"=> $actulDataTime["t"],"id" => $company["ENTITY_ID"], "guid" => $guid, "inn" => $company["RQ_INN"], "kpp" => $company["RQ_KPP"], "fulname" => $company["RQ_COMPANY_FULL_NAME"], "ogrn" => $company["RQ_OGRN"], "okpo" => $company["RQ_OKPO"], "oktmo" => $company["RQ_OKTMO"]);
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public static function generate_guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }

    public static function add($query, $n, \CRestServer $server)
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
            return array('error' => $query);
        }
        if (!isset($_SESSION["BPM_COOKIES"])) {
            QueryBpm::login();
        }

        $verify = self::verify($query["params"]["inn"]);
        $logger = Logger::getLogger('AddContr','ofd.bitrix24/AddContr.log');
        $logger->log(array($query));
        if (!empty($verify)) {
            //собираю массив
            $que = CrmBitrixBpm::contragenAdd($verify);

            $status = $que["status"];
            if ($status == 200) {
                return array("code" => 100, "result" => array("message" => "Inserted successful", "accountId" => $verify["guid"], "method" => "insert"));
            } else {
                return array("code" => 102, "result" => array("message" => "Error $status", "method" => "insert"));
            }
        } else {
            return array("code" => 101, "result" => array("message" => "Item not found", "accountId" => ""));
        }
    }

    public static function update($query, $n, \CRestServer $server)
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
            return array('error' => $query);
        }
        if (!isset($_SESSION["BPM_COOKIES"])) {
            QueryBpm::login();
        }
        $verify = self::verify($query["params"]["inn"]);

        $guid = $verify["guid"];
            $que = CrmBitrixBpmUpdate::contragenUpdate($verify);
            $status = $que["status"];
            if($status == 200){
                metkaColTime($verify["id"], CONTRAGENT_METKA_ADD, 1);
                metkaColTime($verify["id"], CONTRAGENT_METKA_UPDATE, 1);
                $result =  array("code" => 100, "result" => array("message" => "Updated successful", "accountId" => "$guid", "method" => "update"));
            }else{
                $result =  array("code" => 102, "result" => array("message" => "Error: update failed $status", "accountId" => "$guid", "method" => "update"));
            }
            return $result;
    }

    public static function tiketAdd($query, $n, \CRestServer $server)
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
            return array('error' => $query);
        }
        if (!isset($_SESSION["BPM_COOKIES"])) {
            QueryBpm::login();
        }
        if (!empty($query["params"]["id"])) {
            $logger = Logger::getLogger('TiketConnect','ofd.bitrix24/TiketConnect.log');
            $logger->log($query["params"]);
            $result = TiketBpmBitrix::getTiket($query["params"]["id"]);
            $status = $result["status"];
            if ($result["add"] == "Y") {
                return array("code" => 100, "result" => array("message" => "Adding succesfull", "method" => "insert"));
            }
            if ($status == 200 and $result["add"] = "U") {

                return array("code" => 101, "result" => array("message" => "update", "method" => "insert"));
            }
            if ($status != 200){
                return array("code" => 102, "result" => array("message" => "Error $status", "method" => "insert"));
            }
        }
    }
}
