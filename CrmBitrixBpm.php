<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Crm;


class CrmBitrixBpm extends CCrmCompany
{
    public static function contragenAdd($verifity = array())
    {
        $dbContragentGet = CrmBitrixBpm::GetListEx(
        array(),
        array('ID' => $verifity["id"]),
        false,
        false,
        array("TITLE","UF_CRM_COMPANY_GUID", "UF_CRM_1486035239", "UF_CRM_1486033688", "UF_CRM_1486035185", "COMPANY_TYPE", "INDUSTRY", "ASSIGNED_BY_ID", "UF_CRM_COMPANY_GUID")
    );
        while ($dbContragent = $dbContragentGet->GetNext()) {

            //COMPANY_TYPE тип компании
            switch ($dbContragent["COMPANY_TYPE"]) {
                case "PARTNER":
                    $typCompany = BPM_COMPANY_PARTNER;
                    break;
                case "CUSTOMER":
                    $typCompany = BPM_COMPANY_CLIENT;
                    break;
                case "2":
                    $typCompany = BPM_COMPANY_CLIENTAG;
                    break;
                case "RESELLER":
                    $typCompany = BPM_COMPANY_POSTAVCHIK;
                    break;
                case "OTHER":
                    $typCompany = BPM_COMPANY_PODRYDHIK;
                    break;
                case "1":
                    $typCompany = BPM_COMPANY_GPX;
                    break;
                default:
                    $typCompany = BPM_COMPANY_CLIENT;
            }
            //ответственный
            $manager = ownerIdGuid ($dbContragent["ASSIGNED_BY_ID"]);
            //дата подключения к офд
            if ($dbContragent["UF_CRM_1486033688"]) {
                $dataO = $dbContragent["UF_CRM_1486033688"];
                $dataOfd = dataTimeformat($dataO, null, "Y");
            }
            //организационная форма
            if ($dbContragent["UF_CRM_1486035185"]) {
                $dbContragent["USER"] = GetUserField("CRM_COMPANY", $dbContragent["ID"], "UF_CRM_1486035185");
                //орг форма
                switch ($dbContragent["UF_CRM_1486035185"]) {
                    case "63":
                        $Ownership = BPM_ORG_OOO;
                        break;
                    case "64":
                        $Ownership = BPM_ORG_OAO;
                        break;
                    case "65":
                        $Ownership = BPM_ORG_AO;
                        break;
                    case "66":
                        $Ownership = BPM_ORG_ZAO;
                        break;
                    case "67":
                        $Ownership = BPM_ORG_IP;
                        break;
                    case "92":
                        $Ownership = BPM_ORG_PAO;
                        break;
                    case "68":
                        $Ownership = BPM_ORG_FGUP;
                        break;
                    case "69":
                        $Ownership = BPM_ORG_KP;
                        break;
                    case "70":
                        $Ownership = BPM_ORG_GU;
                        break;
                    case "97":
                        $Ownership = BPM_ORG_HL;
                        break;
                    default:
                        $Ownership = BPM_ORG_OOO;
                }

            }

            //Industry - Вид деятельности компании (UF_CRM_1486035239)
            if ($dbContragent["UF_CRM_1486035239"]) {
                $dbContragent["USER"] = GetUserField("CRM_COMPANY", $dbContragent["ID"], "UF_CRM_1486035239");
                switch ($dbContragent["UF_CRM_1486035239"]) {
                    case "71":
                        $activity = BPM_ACTIVITY_PRODUCTS;
                        break;
                    case "72":
                        $activity = BPM_ACTIVITY_SERVICES;
                        break;
                    case "73":
                        $activity = BPM_ACTIVITY_TRADE;
                        break;
                    case "74":
                        $activity = BPM_ACTIVITY_CTO;
                        break;
                    case "75":
                        $activity = BPM_ACTIVITY_INTEGRATOR;
                        break;
                    case "76":
                        $activity = BPM_ACTIVITY_EDO;
                        break;
                    case "77":
                        $activity = BPM_ACTIVITY_MARKETPLACE;
                        break;
                    case "95":
                        $activity = BPM_ACTIVITY_DEVELOPMENT;
                        break;
                    case "96":
                        $activity = BPM_ACTIVITY_MORE;
                        break;
                    default:
                        $activity = BPM_ACTIVITY_MORE;
                }
            }

            $verifity["activity"] = $activity;
            $verifity["Ownership"] = $Ownership;
            $verifity["dataOfd"] = $dataOfd;
            $verifity["manager"] = $manager;
            $verifity["typCompany"] = $typCompany;
            $verifity["id"] = $dbContragent["ID"];
            $verifity["guidd"] = $dbContragent["UF_CRM_COMPANY_GUID"];
            if(empty($verifity["ogrn"])){
                $verifity["ogrn"] = $verifity["ogrnip"];
            }
            if (!empty($verifity["dataOfd"])) {
                $dtim = null;
            }else{
                $dt = dataTimeformat("Y");
                $dtim = "\"$dt\"";
            }
            if(empty($verifity["fulname"])){
                $verifity["fulname"] = trim($dbContragent["TITLE"]);
            }
        }
        $req = new \Bitrix\Crm\EntityRequisite();
        $rser = $req->getList(array(
            "filter" => array(
                "ENTITY_ID" => $verifity["id"],
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
                        $verifity["bank"][$k] = $vale;
                }

            }
            $adress = Bitrix\Crm\EntityRequisite::getAddresses($company['ID']);
            $dbResMultiFields = CCrmFieldMulti::GetList(array(), array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $verifity["id"]));
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

        $datatime = dataTimeformat();
        //$verifity "id","guid","inn","kpp","fulname","ogrn","okpo","oktmo"

        //Основная информация о контрагенте
        $Account = array(
            "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
            "RootSchemaName" => "Account",
            "OperationType" => 1,
            "ColumnValues" => array(
                "Items" => array(
                    "Id" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 0,
                            "Value" => $verifity["guid"]
                        )
                    ),
                    "Name" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => htmlspecialcharsBack($verifity["fulname"]),
                        )
                    ),
                    "Usraidiagent" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => $verifity["inn"],
                        )
                    ),
                    "UsrKPP" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => $verifity["kpp"],
                        )
                    ),
                    "Ownership" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 0,
                            "Value" => $verifity["Ownership"],
                        ),
                    ),
                    "Type" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 0,
                            "Value" => $verifity["typCompany"],
                        ),
                    ),
                    "Industry" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 0,
                            "Value" => $verifity["activity"],
                        ),
                    ),
                    "Owner" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 0,
                            "Value" => $verifity["manager"],
                        ),
                    ),
                    "UsrOGRN" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => $verifity["ogrn"],
                        ),
                    ),
                    "UsrOFDConnectionDate" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 8,
                            "Value" => $dtim,
                        ),
                    ),
                    "UsrOKPO" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => $verifity["okpo"],
                        ),
                    ),
                    "UsrOKTMO" => array(
                        "ExpressionType" => 2,
                        "Parameter" => array(
                            "DataValueType" => 1,
                            "Value" => $verifity["oktmo"],
                        ),
                    ),
                    "UsrBPMActDate" => array(
                        "ExpressionType" => 2,
                        "Parameter" =>
                            array(
                                "DataValueType" => 7,
                                "Value" => "\"$datatime\"",
                            )
                    )
                )
            ));
        $batshContragent[0] = $Account;
        //Комуникации
        foreach ($comunic as $comu => $val) {
            if (!empty($val["COMMUN_TYP"])) {
                $AccountCommunication = array(
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                    'RootSchemaName' => 'AccountCommunication',
                    'OperationType' => 1,
                    'ColumnValues' => array(
                        'Items' =>
                            array(
                                'Account' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 0,
                                                'Value' => $verifity["guidd"],
                                            ),
                                    ),
                                'Number' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 1,
                                                'Value' => $val["VALUE"],
                                            ),
                                    ),
                                'CommunicationType' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 0,
                                                'Value' => $val["COMMUN_TYP"],
                                            ),
                                    ),
                            ),
                    ),
                );
                $batshContragent[] = $AccountCommunication;
            }
        }
        //Множественные адреса
        foreach ($verifity["adress"] as $num => $adrval) {
            $addAddress = array(
                "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                "RootSchemaName" => "AccountAddress",
                "OperationType" => 1,
                "ColumnValues" => array(
                    "Items" => array(
                        'Account' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 0,
                                        'Value' => $verifity["guid"],
                                    ),
                            ),
                        'Address' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 1,
                                        'Value' => $adrval["adressValu"],
                                    ),
                            ),
                        'AddressType' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 0,
                                        'Value' => $adrval["typAdress"],
                                    ),
                            ),

                    )
                )
            );
            if(!empty($adrval)){
                $batshContragent[] = $addAddress;
            }

        }
        //Множественные банки
        foreach ($verifity["bank"] as $n => $b) {
            $bank = array(
                "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                "RootSchemaName" => "AccountBillingInfo",
                "OperationType" => 1,
                "ColumnValues" => array(
                    "Items" => array(
                        'Account' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 0,
                                        'Value' => $verifity["guid"],
                                    ),
                            ),
                        'Name' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 1,
                                        'Value' => $b["RQ_BANK_NAME"],
                                    ),
                            ),
                        'BillingInfo' =>
                            array(
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array(
                                        'DataValueType' => 1,
                                        'Value' => "Р/с: ".$b["RQ_ACC_NUM"]." Банк: ".$b["RQ_BANK_NAME"]." БИК: ".$b["RQ_BIK"]." К/с: ".$b["RQ_COR_ACC_NUM"],
                                    ),
                            ),

                    )
                )
            );
            if(!empty($b) and !empty($b["RQ_BANK_NAME"])){
                $batshContragent[] = $bank;
            }
        }

        $contragent = array(
            "items" => $batshContragent
        );

        //$Data = json_encode($contragent, true);
        //pre($Data);
        $que = QueryBpm::jsonDataBpmContr($contragent, BPM_URL_QUERY);
        if($que["status"] == 403){
            $q = QueryBpm::jsonDataBpmContr($contragent, BPM_URL_QUERY);
            $logger = Logger::getLogger('creatContragent','ofd.bitrix24/creatContragent.log');
            $logger->log(array($contragent,$que,$q));
            return ($q);
        }else{
            return ($que);
        }


    }
    /**
     * Удаление средств связи, удаление адресов, удаление реквизитов
     * @param $id
     */
    public static function deleteContrSush ($deleteComunic){

        $dbContragentGet = CrmBitrixBpm::GetListEx(
            array(),
            array('ID' => $deleteComunic["ID"]),
            false,
            false,
            array("TITLE","UF_CRM_COMPANY_GUID", "UF_CRM_1486035239", "UF_CRM_1486033688", "UF_CRM_1486035185", "COMPANY_TYPE", "INDUSTRY", "ASSIGNED_BY_ID", "UF_CRM_COMPANY_GUID")
        );
        while ($dbContragent = $dbContragentGet->GetNext()) {

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
                "ENTITY_ID" => $deleteComunic["ID"],
                "ENTITY_TYPE_ID" => CCrmOwnerType::Company,
                "PRESET_ID" => array(1,2)
            )
        ));
        $rows = $rser->fetchAll();
        $contragentInn = $rows["RQ_INN"];
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
                        $verifity["bank"][$k] = $vale;
                }

            }
            $adress = Bitrix\Crm\EntityRequisite::getAddresses($company['ID']);

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

        $up = metkaColTime($deleteComunic["ID"], CONTRAGENT_METKA_UPDATE);
        $metkaArrayNew = explode("/", $up);

        $updateData = $metkaArrayNew[0];

        //Y-m-d
        $UsrOFDConnectionDate = dataTimeformat($updateData);
        $UsrBPMActDate = convertTime($updateData);

        //Основная информация о контрагенте
        $Account_update = array(
            '__type' => 'Terrasoft.Nui.ServiceModel.DataContract.UpdateQuery',
            'RootSchemaName' => 'Account',
            'QueryType' => 1,
            'ColumnValues' =>
                array(
                    'Items' =>
                        array(
                            'UsrBPMActDate' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 7,
                                            'Value' => "\"$UsrBPMActDate\"",
                                        ),
                                ),
                            'UsrOFDConnectionDate' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 8,
                                            'Value' => "\"$UsrOFDConnectionDate\"",
                                        ),
                                ),
                        ),
                ),
            'Filters' =>
                array(
                    'FilterType' => 6,
                    'ComparisonType' => 0,
                    'Items' =>
                        array(
                            'FilterId' =>
                                array(
                                    'FilterType' => 1,
                                    'ComparisonType' => 3,
                                    'LeftExpression' =>
                                        array(
                                            'ExpressionType' => 0,
                                            'ColumnPath' => 'Id',
                                        ),
                                    'RightExpression' =>
                                        array(
                                            'ExpressionType' => 2,
                                            'Parameter' =>
                                                array(
                                                    'DataValueType' => 0,
                                                    'Value' => $deleteComunic["guid"],
                                                ),
                                        ),
                                ),
                        ),
                ),
        );
        $batshContragent[0] = $Account_update;
        $dbResMultiFields = CCrmFieldMulti::GetList(array(), array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $deleteComunic["ID"]));
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
            $comunic[$value["ID"]."_".$value["COMMUN_TYP"]] = $value["VALUE"];
            $comunics[] = $value;
        }

        //Комуникации
        if(!empty($deleteComunic["comunicetions_ar"]) and !empty($deleteComunic["guid"])){
            foreach ($deleteComunic["comunicetions_ar"] as $comu => $valcom) {
                $deleteCommunication = array (
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.DeleteQuery",
                    'RootSchemaName' => 'AccountCommunication',
                    'OperationType' => 3,
                    'Filters' =>
                        array (
                            'FilterType' => 6,
                            'ComparisonType' => 0,
                            'Items' =>
                                array (
                                    'FilterAccount' =>
                                        array (
                                            'FilterType' => 1,
                                            'ComparisonType' => 3,
                                            'LeftExpression' =>
                                                array (
                                                    'ExpressionType' => 0,
                                                    'ColumnPath' => 'Account.Id',
                                                ),
                                            'RightExpression' =>
                                                array (
                                                    'ExpressionType' => 2,
                                                    'Parameter' =>
                                                        array (
                                                            'DataValueType' => 0,
                                                            'Value' => $deleteComunic["guid"],
                                                        ),
                                                ),
                                        ),
                                    'FilterValue' =>
                                        array (
                                            'FilterType' => 1,
                                            'ComparisonType' => 3,
                                            'LeftExpression' =>
                                                array (
                                                    'ExpressionType' => 0,
                                                    'ColumnPath' => 'Number',
                                                ),
                                            'RightExpression' =>
                                                array (
                                                    'ExpressionType' => 2,
                                                    'Parameter' =>
                                                        array (
                                                            'DataValueType' => 1,
                                                            'Value' => $valcom["VALUE"],
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );
                if(!empty($valcom["VALUE"])){
                    $batshContragent[] = $deleteCommunication;
                }
            }
            foreach ($comunics as $com => $valc) {
                $AccountCommunic = array(
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                    'RootSchemaName' => 'AccountCommunication',
                    'OperationType' => 1,
                    'ColumnValues' => array(
                        'Items' =>
                            array(
                                'Account' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 0,
                                                'Value' => $deleteComunic["guid"],
                                            ),
                                    ),
                                'Number' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 1,
                                                'Value' => $valc["VALUE"],
                                            ),
                                    ),
                                'CommunicationType' =>
                                    array(
                                        'ExpressionType' => 2,
                                        'Parameter' =>
                                            array(
                                                'DataValueType' => 0,
                                                'Value' => $valc["COMMUN_TYP"],
                                            ),
                                    ),
                            ),
                    ),
                );
                if(!empty($valc["VALUE"])){
                    $batshContragent[] = $AccountCommunic;
                }

            }

        }

        //Множественные адреса
        if(!empty($deleteComunic["adress"]) and !empty($deleteComunic["guid"])) {
            foreach ($deleteComunic["adress"] as $keys => $valadres) {
                $adress = array(
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.DeleteQuery",
                    'RootSchemaName' => 'AccountAddress',
                    'OperationType' => 3,
                    'Filters' => array(
                        'FilterType' => 6,
                        'ComparisonType' => 0,
                        'Items' =>
                            array(
                                'FilterAccount' =>
                                    array(
                                        'FilterType' => 1,
                                        'ComparisonType' => 3,
                                        'LeftExpression' =>
                                            array(
                                                'ExpressionType' => 0,
                                                'ColumnPath' => 'Account.Id',
                                            ),
                                        'RightExpression' =>
                                            array(
                                                'ExpressionType' => 2,
                                                'Parameter' =>
                                                    array(
                                                        'DataValueType' => 0,
                                                        'Value' => $deleteComunic["guid"],
                                                    ),
                                            ),
                                    ),
                                'FilterValue' =>
                                    array(
                                        'FilterType' => 1,
                                        'ComparisonType' => 3,
                                        'LeftExpression' =>
                                            array(
                                                'ExpressionType' => 0,
                                                'ColumnPath' => 'AddressType',
                                            ),
                                        'RightExpression' =>
                                            array(
                                                'ExpressionType' => 2,
                                                'Parameter' =>
                                                    array(
                                                        'DataValueType' => 0,
                                                        'Value' => $valadres["typAdress"],
                                                    ),
                                            ),
                                    ),
                            ),
                    ),
                );
                if (!empty($valadres["typAdress"])) {
                    $batshContragent[] = $adress;
                }
            }
            foreach ($verifity["adress"] as $num => $adrval) {
                $addAddress = array(
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                    "RootSchemaName" => "AccountAddress",
                    "OperationType" => 1,
                    "ColumnValues" => array(
                        "Items" => array(
                            'Account' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $deleteComunic["guid"],
                                        ),
                                ),
                            'Address' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $adrval["adressValu"],
                                        ),
                                ),
                            'AddressType' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $adrval["typAdress"],
                                        ),
                                ),

                        )
                    )
                );

                if (!empty($adrval)) {
                    $batshContragent[] = $addAddress;
                }
            }
        }
        //Множественные банки
        if(!empty($deleteComunic["bank"]) and !empty($deleteComunic["guid"])){
            foreach ($deleteComunic["bank"] as $n => $b) {
                $bank = array (
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.DeleteQuery",
                    'RootSchemaName' => 'AccountBillingInfo',
                    'OperationType' => 3,
                    'Filters' =>
                        array (
                            'FilterType' => 6,
                            'ComparisonType' => 0,
                            'Items' =>
                                array (
                                    'FilterAccount' =>
                                        array (
                                            'FilterType' => 1,
                                            'ComparisonType' => 3,
                                            'LeftExpression' =>
                                                array (
                                                    'ExpressionType' => 0,
                                                    'ColumnPath' => 'Account.Id',
                                                ),
                                            'RightExpression' =>
                                                array (
                                                    'ExpressionType' => 2,
                                                    'Parameter' =>
                                                        array (
                                                            'DataValueType' => 0,
                                                            'Value' => $deleteComunic["guid"],
                                                        ),
                                                ),
                                        ),
                                    'FilterValue' =>
                                        array (
                                            'FilterType' => 1,
                                            'ComparisonType' => 11,
                                            'LeftExpression' =>
                                                array (
                                                    'ExpressionType' => 0,
                                                    'ColumnPath' => 'BillingInfo',
                                                ),
                                            'RightExpression' =>
                                                array (
                                                    'ExpressionType' => 2,
                                                    'Parameter' =>
                                                        array (
                                                            'DataValueType' => 1,
                                                            'Value' => $b,
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );
                if (!empty($b)) {
                    $batshContragent[] = $bank;
                }
            }
        }
        if(!empty($verifity["bank"]) and !empty($deleteComunic["guid"])){
            foreach ($verifity["bank"] as $n => $b) {
                $bank_add = array(
                    "__type" => "Terrasoft.Nui.ServiceModel.DataContract.InsertQuery",
                    "RootSchemaName" => "AccountBillingInfo",
                    "OperationType" => 1,
                    "ColumnValues" => array(
                        "Items" => array(
                            'Account' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $deleteComunic["guid"],
                                        ),
                                ),
                            'Name' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $b["RQ_BANK_NAME"],
                                        ),
                                ),
                            'BillingInfo' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => "Р/с: " . $b["RQ_ACC_NUM"] . " Банк: " . $b["RQ_BANK_NAME"] . " БИК: " . $b["RQ_BIK"] . " К/с: " . $b["RQ_COR_ACC_NUM"],
                                        ),
                                ),

                        )
                    )
                );
                if (!empty($b) and !empty($b["RQ_BANK_NAME"])) {
                    $batshContragent[] = $bank_add;
                }
            }
        }

        $logger = Logger::getLogger('comunik','ofd.bitrix24/comunik.log');
        $logger->log(array($deleteComunic["adress"],$verifity["adress"]));
        if(!empty($b) and !empty($deleteComunic["guid"]) or !empty($adrvals) and !empty($deleteComunic["guid"]) or !empty($valcom) and !empty($deleteComunic["guid"])){

            $contragentDel = array(
                "items" => $batshContragent
            );
            $que = QueryBpm::jsonDataBpmContr($contragentDel, BPM_URL_QUERY);
            if($que["status"] != 200){
                $que = QueryBpm::jsonDataBpmContr($contragentDel, BPM_URL_QUERY);
                metkaColTime($deleteComunic["ID"], CONTRAGENT_METKA_UPDATE, 1);
                metkaColTime($deleteComunic["ID"], CONTRAGENT_METKA_ADD,1);
                $logger = Logger::getLogger('deleteContragent-403','ofd.bitrix24/deleteContragent-403.log');
                $logger->log(array($deleteComunic,$que,$contragentDel));
            }else{
                $logger = Logger::getLogger('deleteContragentYes','ofd.bitrix24/deleteContragentYes.log');
                $jsonContragentDel = json_encode($contragentDel, JSON_UNESCAPED_UNICODE);
                $logger->log(array($deleteComunic,$que,$contragentDel));

            }

        }

        /*if($que["status"] == 403){
            $q = QueryBpm::jsonDataBpmContr($contragentDel, BPM_URL_QUERY);
            return ($q);
        }else{
            return ($que);
        }*/
        //pre($contragentDel);
        //exit;
        unset($_SESSION["DELETE_CONTR_LINE"][$deleteComunic["ID"]]);

        $deleteComunic["update"] == "Y";
        return ($deleteComunic["update"]);
    }
}