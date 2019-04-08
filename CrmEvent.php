<?
use Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Crm;

class CrmEvent {

    public static function deleteContragent(&$arFields){
        unset($_SESSION["DELETE_CONTR_LINE"][$arFields["ID"]]);
        $dbContragentGet = CrmBitrixBpm::GetListEx(
            array(),
            array('ID' => $arFields["ID"]),
            false,
            false,
            array("TITLE","UF_CRM_COMPANY_GUID","UF_CRM_INN", "UF_CRM_1486035239", "UF_CRM_1486033688", "UF_CRM_1486035185", "COMPANY_TYPE", "INDUSTRY", "ASSIGNED_BY_ID", "UF_CRM_COMPANY_GUID")
        );
        while ($dbContragent = $dbContragentGet->GetNext()) {

            $verifity["guidd"] = $dbContragent["UF_CRM_COMPANY_GUID"];
            $verifity["inn"] = $dbContragent["UF_CRM_INN"];
            if (!empty($verifity["dataOfd"])) {
                $dtim = null;
            }else{
                $dt = dataTimeformat("Y");
                $dtim = "\"$dt\"";
            }
        }
        $req = new \Bitrix\Crm\EntityRequisite();
        $rser = $req->getList(array(
            "filter" => array(
                "ENTITY_ID" => $arFields["ID"],
                "ENTITY_TYPE_ID" => CCrmOwnerType::Company,
                "PRESET_ID" => array(1,2)
            )
        ));
        $rows = $rser->fetchAll();

        foreach ($rows as $company) {
            $params = array(
                'filter' => array(
                    'ENTITY_ID' => $company['ID'],
                    'ENTITY_TYPE_ID' => CCrmOwnerType::Requisite)
            );
            $bank = new \Bitrix\Crm\EntityBankDetail();
            $dbRes = $bank->getList(array(
                'filter' => array('ENTITY_ID' => $company['ID'])
            ));
            $rowsd = $dbRes->fetchAll();
            foreach ($rowsd as $k => $vale) {
                switch ($k) {
                    default:
                        $verifity["bank"][$vale["RQ_ACC_NUM"]] = $vale["RQ_ACC_NUM"];
                }

            }
            $adress = Bitrix\Crm\EntityRequisite::getAddresses($company['ID']);
            $dbResMultiFields = CCrmFieldMulti::GetList(array(), array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $arFields["ID"]));
            while ($arMultiFields = $dbResMultiFields->Fetch()) {
                $comunicetions[] = $arMultiFields;

            }
            foreach ($comunicetions as $com => $value) {
                if ($value["TYPE_ID"] == "PHONE") {
                    switch ($value["VALUE_TYPE"]) {
                        case "WORK":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_WORK_PHONE;
                            break;
                        case "MOBILE":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_MOBFHONE;
                            break;
                        case "FAX":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_FAX;
                            break;
                        case "OTHER":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_OTHER_PHONE;
                            break;
                    }
                }
                if ($value["TYPE_ID"] == "EMAIL") {
                    switch ($value["VALUE_TYPE"]) {
                        case "WORK":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_EMAIL;
                            break;
                        case "OTHER":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_EMAIL;
                            break;
                    }
                }
                if ($value["TYPE_ID"] == "IM") {
                    switch ($value["VALUE_TYPE"]) {
                        case "SKYPE":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_SKYPE;
                            break;
                    }
                }
                if ($value["TYPE_ID"] == "WEB") {
                    switch ($value["VALUE_TYPE"]) {
                        case "WORK":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_URL;
                            break;
                        case "TWITTER":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_TWITTER;
                            break;
                        case "FACEBOOK":
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_FACEBOOK;
                            break;
                        default:
                            $value["COMMUN_TYP"] = BPM_COMMUNICATION_URL;
                    }
                }
                $comunic[] = $value;
            }
            foreach ($adress as $key => $val) {
                switch ($key) {
                    case 6:
                        $verifity["adress"][$key]["typAdress"] = BPM_ADRESS_UR;
                        $verifity["adress"][$key]["adressValu"] = $val["ADDRESS_1"] . " ";
                        break;
                    case 1:
                        $verifity["adress"][$key]["typAdress"] = BPM_ADRESS_REAL;
                        $verifity["adress"][$key]["adressValu"] = $val["ADDRESS_1"] . " ";
                        break;
                    default:
                        $verifity["adress"][$key]["typAdress"] = BPM_ADRESS_OTHER;
                        $verifity["adress"][$key]["adressValu"] = $val["ADDRESS_1"] . " ";
                }
            }
        }
        foreach($arFields["FM"]["PHONE"] as $key=>$val){

                $deleteComunic[$key]= $val['VALUE'];

        }
        foreach($arFields["FM"]["EMAIL"] as $key=>$val){

                $deleteComunic[$key]= $val['VALUE'];

        }
        foreach($arFields["FM"]["IM"] as $key=>$val){

                $deleteComunic[$key]= $val['VALUE'];

        }
        foreach($arFields["FM"]["WEB"] as $key=>$val){

                $deleteComunic[$key]= $val['VALUE'];

        }
        $result["ID"] = $arFields["ID"];
        $result["guid"] = $arFields["UF_CRM_COMPANY_GUID"];
        $result["comunicetions"] = array_diff_assoc($comunic, $deleteComunic);
        $result["comunicetions_ar"] = $comunic;
        $result["comunicetions_del"] = $deleteComunic;
        $result["adress"] = $verifity["adress"];
        $result["bank"] = $verifity["bank"];
        $result["inn"] = $arFields;

        session_start();
        $_SESSION["DELETE_CONTR_LINE"][$arFields["ID"]] = $result;
    }
    public static function updateContragent(&$arFields){


        metkaColTime($arFields["ID"], CONTRAGENT_METKA_UPDATE, 1);

    }
}