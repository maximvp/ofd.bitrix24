<?

class TiketBpmBitrix{
    public static function  formass($arg = array()){
        foreach ($arg as $key=>$val){
            if($key == "Status" || $key == "Priority" || $key == "Origin" || $key == "Contact" || $key == "Group" || $key == "Account" || $key == "UsrCaseType" || $key == "Owner" || $key == "Category"){
                foreach ($val as $k=>$v){
                    if($k == "value"){
                        $rez[$key] = $v;
                    }
                    if($k == "displayValue"){
                        $rez[$key."_display"] = $v;
                    }
                }
            }else{
                $rez[$key] = $val;
            }
        }
        return ($rez);
    }
    public static function tiketStat($guidTiket, $id){
        $arSelect = array("ID","IBLOCK_ID");
        $arFilter = Array("IBLOCK_ID" => $id, "PROPERTY_ID_ZADACHI_PO_OBRASHCHENIYU" => $guidTiket);
        $dbElem = CIBlockElement::GetList(Array(), $arFilter, false, false, array());
        while($arElem = $dbElem->GetNext())
        {
            if(!empty($arElem["ID"])){
                $result = $arElem["ID"];
            }else{
                $result = null;
            }
        }
        return ($result);
    }
    public static function getTiket($id){
        $tiket = array (
            'RootSchemaName' => 'Case',
            'QueryType' => 0,
            'Columns' =>
                array (
                    'Items' =>
                        array (
                            'UsrINN' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'UsrINN',
                                        ),
                                ),
                            'Status' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Status',
                                        ),
                                ),
                            'Subject' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Subject',
                                        ),
                                ),
                            'Symptoms' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Symptoms',
                                        ),
                                ),
                            'Number' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Number',
                                        ),
                                ),
                            'RegisteredOn' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'RegisteredOn',
                                        ),
                                ),
                            'Priority' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Priority',
                                        ),
                                ),
                            'ConfItem' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'ConfItem',
                                        ),
                                ),
                            'UsrConfLocation' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'UsrConfLocation',
                                        ),
                                ),
                            'Account' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Account',
                                        ),
                                ),
                            'Contact' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Contact',
                                        ),
                                ),
                            'UsrCaseType' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'UsrCaseType',
                                        ),
                                ),
                            'Group' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Group',
                                        ),
                                ),
                            'Category' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Category',
                                        ),
                                ),
                            'UsrPhoneNumber' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'UsrPhoneNumber',
                                        ),
                                ),
                            'Origin' =>
                                array (
                                    'Expression' =>
                                        array (
                                            'ColumnPath' => 'Origin',
                                        ),
                                ),
                        ),
                ),
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
                                                    'Value' => $id,
                                                ),
                                        ),
                                ),
                        ),
                ),
        );

        $arrTeket = QueryBpm::jsonDataBpm($tiket, BPM_URL_SELECT);
        if($arrTeket["status"] == 403){
            $arrTeket = QueryBpm::jsonDataBpmContr($tiket, BPM_URL_SELECT);
        }
        $rezult["status"] = $arrTeket["status"];
        if($rezult["status"] != 200){
            return ($arrTeket);
        }

        foreach ($arrTeket["success"]["rows"] as $row => $key) {
            $tiketBitrix[$row] = $key;
        }

        $format = self::formass($key);
        if(array_key_exists('Id', $format)){
            switch ($format["Group"]) {
                case mb_strtolower(ID_GROUP_SUPPORT):
                    $format["iblocId"] = returnIdCodeIblock(IBLOK_TWO_LINE);
                    break;
                case mb_strtolower(ID_GROUP_SALE):
                    $format["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_SALE);
                    break;
                case mb_strtolower(ID_GROUP_SALE_PARTNER):
                    $format["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_SALE_PARTNER);
                    break;
                case mb_strtolower(ID_GROUP_MARKETING):
                    $format["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_MARKET);
                    break;
                default:
                    $format["iblocId"] = returnIdCodeIblock(IBLOK_TWO_LINE);
            }
            if($format["Status"] == strtoupper(ID_STATUS_RE_OPEN)){
                $format["reopen"] = true;
            }else{
                $format["reopen"] = null;
            }
            $arSelect = Array("ID");
            $arFilter = Array("IBLOCK_ID"=>IntVal($format["iblocId"]), "PROPERTY_ID_ZADACHI_PO_OBRASHCHENIYU"=>$format["id"], "ACTIVE"=>"Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            while($ob = $res->GetNextElement())
            {
                $arFields = $ob->GetFields();
            }

            if(!empty($arFields["ID"])){
                $format["idElem"] = $arFields["ID"];
                $rezult["add"] = TiketBitrix::update($format);
            }else{
                $rezult["add"] = TiketBitrix::add($format);
            }

            $logger = Logger::getLogger('tiketAdd','ofd.bitrix24/tiketAdd.log');
            $logger->log(array($arrTeket, $format));
            return ($rezult);

        }

    }
}