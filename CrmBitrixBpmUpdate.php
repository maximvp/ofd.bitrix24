<?

class CrmBitrixBpmUpdate extends CCrmCompany
{

    public static function contragenUpdate($verifity = array())
    {
        $dbContragentGet = CrmBitrixBpmUpdate::GetListEx(
            array(),
            array('ID' => $verifity["id"]),
            false,
            false,
            array("ID","DATE_MODIFY", "TITLE", "UF_CRM_COMPANY_GUID", CONTRAGENT_METKA_ADD, CONTRAGENT_METKA_UPDATE, "UF_CRM_1486035239", "UF_CRM_1486033688", "UF_CRM_1486035185", "COMPANY_TYPE", "INDUSTRY", "ASSIGNED_BY_ID")
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
            switch ($dbContragent["ASSIGNED_BY_ID"]) {
                case "396":
                    $manager = BPM_MANAGER_BUNTOVA;
                    break;
                case "410":
                    $manager = BPM_MANAGER_GRIBANOVA;
                    break;
                case "398":
                    $manager = BPM_MANAGER_KOPEYKINA;
                    break;
                case "285":
                    $manager = BPM_MANAGER_LUTAY;
                    break;
                case "399":
                    $manager = BPM_MANAGER_SERGEENKO;
                    break;
                case "346":
                    $manager = BPM_MANAGER_GDANOVA;
                    break;
                case "302":
                    $manager = BPM_MANAGER_ZAMARAEVA;
                    break;
                case "426":
                    $manager = BPM_MANAGER_KOLOMYCEV;
                    break;
                case "275":
                    $manager = BPM_MANAGER_SHEMYKIN;
                    break;
                case "402":
                    $manager = BPM_MANAGER_GAVRILOV;
                    break;
                case "300":
                    $manager = BPM_MANAGER_GUREV;
                    break;
                case "357":
                    $manager = BPM_MANAGER_DEREVYNSKIY;
                    break;
                case "403":
                    $manager = BPM_MANAGER_KRYLOV;
                    break;
                case "347":
                    $manager = BPM_MANAGER_MAKSIMOV;
                    break;
                case "342":
                    $manager = BPM_MANAGER_MALCEV;
                    break;
                case "259":
                    $manager = BPM_MANAGER_MASLENNIKOVA;
                    break;
                case "400":
                    $manager = BPM_MANAGER_POPOV;
                    break;
                case "161":
                    $manager = BPM_MANAGER_FEOKTISOVA;
                    break;
                case "301":
                    $manager = BPM_MANAGER_SMOLYKOV;
                    break;
                case "311":
                    $manager = BPM_MANAGER_STEBLIN;
                    break;
                case "401":
                    $manager = BPM_MANAGER_TABAKOV;
                    break;
                case "299":
                    $manager = BPM_MANAGER_COKOLENKO;
                    break;
                case "388":
                    $manager = BPM_MANAGER_SHKURENKOV;
                    break;
                case "406":
                    $manager = BPM_MANAGER_YKOVLEVA;
                    break;
                default:
                    $manager = BPM_MANAGER_BUNTOVA;
            }
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
            $verifity["dateModify"] = $dbContragent["DATE_MODIFY"];
            $verifity["activity"] = $activity;
            $verifity["Ownership"] = $Ownership;
            $verifity["dataOfd"] = $dataOfd;
            $verifity["manager"] = $manager;
            $verifity["typCompany"] = $typCompany;
            $verifity["id"] = $dbContragent["ID"];
            $verifity["guidd"] = $dbContragent["UF_CRM_COMPANY_GUID"];
            if (empty($verifity["ogrn"])) {
                $verifity["ogrn"] = $verifity["ogrnip"];
            }
            if (!empty($verifity["dataOfd"])) {
                $dtim = null;
            } else {
                $dt = dataTimeformat("Y");
                $dtim = "\"$dt\"";
            }
            if (empty($verifity["fulname"])) {
                $verifity["fulname"] = trim($dbContragent["TITLE"]);
            }
        }
        $req = new \Bitrix\Crm\EntityRequisite();
        $rser = $req->getList(array(
            "filter" => array(
                "ENTITY_ID" => $verifity["id"],
                "ENTITY_TYPE_ID" => CCrmOwnerType::Company,
                "PRESET_ID" => array(1, 2)
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
        $metka_time = metkaColTime($verifity["id"], CONTRAGENT_METKA_ADD);
        $metka_time_new = metkaColTime($verifity["id"], CONTRAGENT_METKA_UPDATE);
        $metkaArray = explode("/", $metka_time);
        $metkaArrayNew = explode("/", $metka_time_new);
        $updateData = $metkaArrayNew[0];


        $updateMetkaBank = count($rowsd);
        $updateMetkaAdress = count($adress);
        $updateMetkaSvyz = count($comunicetions);


        $addData = $metkaArray[0];
        $addMetkaBank = $metkaArray[1];
        $addMetkaAdress = $metkaArray[2];
        $addMetkaSvyz = $metkaArray[3];

        $marginMetkaSvyz = $updateMetkaSvyz - $addMetkaSvyz;
        $marginMetkaBank = $updateMetkaBank - $addMetkaBank;
        $marginMetkaAdress = $updateMetkaAdress - $addMetkaAdress;

        //Y-m-d
        $UsrOFDConnectionDate = dataTimeformat($updateData);
        $UsrBPMActDate = convertTime($updateData);
        //$verifity "id","guid","inn","kpp","fulname","ogrn","okpo","oktmo"

        //Основная информация о контрагенте
        $Account_update = array(
            '__type' => 'Terrasoft.Nui.ServiceModel.DataContract.UpdateQuery',
            'RootSchemaName' => 'Account',
            'QueryType' => 1,
            'ColumnValues' =>
                array(
                    'Items' =>
                        array(
                            'Name' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => htmlspecialcharsBack($verifity["fulname"]),
                                        ),
                                ),
                            'Usraidiagent' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $verifity["inn"],
                                        ),
                                ),
                            'UsrKPP' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $verifity["kpp"],
                                        ),
                                ),
                            'Ownership' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $verifity["Ownership"],
                                        ),
                                ),
                            'Type' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $verifity["typCompany"],
                                        ),
                                ),
                            'Industry' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $verifity["activity"],
                                        ),
                                ),
                            'Owner' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 0,
                                            'Value' => $verifity["manager"],
                                        ),
                                ),
                            'UsrOGRN' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $verifity["ogrn"],
                                        ),
                                ),
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
                            'UsrOKPO' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $verifity["okpo"],
                                        ),
                                ),
                            'UsrOKTMO' =>
                                array(
                                    'ExpressionType' => 2,
                                    'Parameter' =>
                                        array(
                                            'DataValueType' => 1,
                                            'Value' => $verifity["oktmo"],
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
                                                    'Value' => $verifity["guid"],
                                                ),
                                        ),
                                ),
                        ),
                ),
        );
        $batshContragent[0] = $Account_update;
        //Комуникации
        if ($marginMetkaSvyz > 0) {
            $comunic = array_reverse($comunic);
            $comunic = array_splice($comunic, -$marginMetkaSvyz);
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
        }

        //Множественные адреса
        foreach ($verifity["adress"] as $numdel => $adrdel) {
            $deletAddress = array(
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
                                                    'Value' => $verifity["guid"],
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
                                                    'Value' => $adrdel["typAdress"],
                                                ),
                                        ),
                                ),
                        ),
                ),
            );
            if (!empty($adrdel)) {
                $batshContragent[] = $deletAddress;
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
            if (!empty($adrval)) {
                $batshContragent[] = $addAddress;
            }
        }


        //Множественные банки
        foreach ($verifity["bank"] as $ndel => $bdel) {
            $bankdel = array (
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
                                                        'Value' => $verifity["guid"],
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
                                                        'Value' => $bdel["RQ_ACC_NUM"],
                                                    ),
                                            ),
                                    ),
                            ),
                    ),
            );
            if (!empty($bdel["RQ_ACC_NUM"])) {
                $batshContragent[] = $bankdel;
            }
        }
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
                                        'Value' => "Р/с: " . $b["RQ_ACC_NUM"] . " Банк: " . $b["RQ_BANK_NAME"] . " БИК: " . $b["RQ_BIK"] . " К/с: " . $b["RQ_COR_ACC_NUM"],
                                    ),
                            ),

                    )
                )
            );
            if (!empty($b) and !empty($b["RQ_BANK_NAME"])) {
                $batshContragent[] = $bank;
            }
        }

        $contragent = array(
            "items" => $batshContragent
        );

        $que = QueryBpm::jsonDataBpmContr($contragent, BPM_URL_QUERY);
        if($que["status"] !=200){
            $q = QueryBpm::jsonDataBpmContr($contragent, BPM_URL_QUERY);
            $logger = Logger::getLogger('ContragentUpdate','ofd.bitrix24/ContragentUpdate.log');
            $jsonContragentDel = json_encode($contragent, JSON_UNESCAPED_UNICODE);
            //$logger->log(array($q,$contragent));
            $logger->log(array($q,$jsonContragentDel));
            metkaAddDelet($verifity["id"]);
            return ($q);
        }else{
            $logger = Logger::getLogger('ContragentUpdate','ofd.bitrix24/ContragentUpdate.log');
            $logger->log(array($que,$contragent));
            metkaAddDelet($verifity["id"]);
            return ($que);
        }
    }
    public static function tiketUpdate($link = array()){
        if($link["status"] == ID_STATUS_CANCELLED){
            $ColumnValues = array (
                'Items' =>
                    array (
                        'Status' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["status"],
                                    ),
                            ),
                        'Owner' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["owner"],
                                    ),
                            ),
                        // в статусе отмена
                        'ClosureCode' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["reason_closure"],
                                    ),
                            ),
                        'Group' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["grup_owner"],
                                    ),
                            ),
                        'UsrHistory' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["history"],
                                    ),
                            ),
                        'UsrLinkCasePageBitrix' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["href"],
                                    ),
                            ),
                    ),
            );
        }
        elseif ($link["status"] == ID_STATUS_CLOSE){
            $ColumnValues = array (
                'Items' =>
                    array (
                        'Status' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["status"],
                                    ),
                            ),
                        'Owner' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["owner"],
                                    ),
                            ),
                        'Group' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["grup_owner"],
                                    ),
                            ),
                        // в статусе решено(задача закрыта)
                        'Solution' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["reshenie"],
                                    ),
                            ),
                        'UsrHistory' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["history"],
                                    ),
                            ),
                        'UsrLinkCasePageBitrix' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["href"],
                                    ),
                            ),
                    ),
            );
        }
        elseif ($link["status"] == ID_STATUS_RE_OPEN and $link["idOldTiket"] != "reopen"){
            $ColumnValues = array (
                'Items' =>
                    array (
                        'Status' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["status"],
                                    ),
                            ),/**/
                        'Owner' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["owner"],
                                    ),
                            ),
                        'Group' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["grup_owner"],
                                    ),
                            ),
                        'Solution' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["reshenie"],
                                    ),
                            ),
                        'UsrHistory' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["history"],
                                    ),
                            ),
                        'UsrLinkCasePageBitrix' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["href"],
                                    ),
                            ),
                    ),
            );
        }
        elseif(empty($link["idOldTiket"]) and $link["idOldTiket"] != "reopen"){
            $ColumnValues = array (
                'Items' =>
                    array (
                        'Owner' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["owner"],
                                    ),
                            ),
                        'UsrLinkCasePageBitrix' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["href"],
                                    ),
                            ),
                    ),
            );
        }
        elseif($link["idOldTiket"] != "reopen" and !empty($link["idOldTiket"])){
            $ColumnValues = array (
                'Items' =>
                    array (
                        'Status' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["status"],
                                    ),
                            ),
                        'Owner' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["owner"],
                                    ),
                            ),
                        'Group' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 0,
                                        'Value' => $link["grup_owner"],
                                    ),
                            ),
                        'UsrHistory' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["history"],
                                    ),
                            ),/**/
                        'UsrLinkCasePageBitrix' =>
                            array (
                                'ExpressionType' => 2,
                                'Parameter' =>
                                    array (
                                        'DataValueType' => 1,
                                        'Value' => $link["href"],
                                    ),
                            ),
                    ),
            );
        }

        $tiketUp = array (
            'RootSchemaName' => 'Case',
            'QueryType' => 1,
            'ColumnValues' => $ColumnValues,
            'Filters' =>
                array (
                    'FilterType' => 6,
                    'ComparisonType' => 0,
                    'Items' =>
                        array (
                            'FilterId' =>
                                array (
                                    'FilterType' => 1,
                                    'ComparisonType' => 3,
                                    'LeftExpression' =>
                                        array (
                                            'ExpressionType' => 0,
                                            'ColumnPath' => 'Id',
                                        ),
                                    'RightExpression' =>
                                        array (
                                            'ExpressionType' => 2,
                                            'Parameter' =>
                                                array (
                                                    'DataValueType' => 0,
                                                    'Value' => $link["id"],
                                                ),
                                        ),
                                ),
                        ),
                ),
        );

        if(!empty($link["id"])){
            $que = QueryBpm::jsonDataBpm($tiketUp, BPM_URL_UPDATE);
            $tiketUp = json_encode($tiketUp);
            $logger = Logger::getLogger('jsonTiketOut','ofd.bitrix24/jsonTiketOut.json');
            $logger->log(array($tiketUp, $que));
        }
        if($que["status"] == 403 or $que["status"] == 0){
            $que = QueryBpm::jsonDataBpm($tiketUp, BPM_URL_UPDATE);
            $tiketUp = json_encode($tiketUp);
            $logger = Logger::getLogger('jsonTiketOut','ofd.bitrix24/jsonTiketOut.json');
            $logger->log(array($tiketUp, $que));
        }
        return ($que);

    }
}
