<?
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
Loader::includeModule("iblock");

class TiketBitrix
{

    public static $arrProp = Array(
        "VALUE" => ""
    );
    public static function idPropertyGuid($iblock, $code, $guid){
        $db_enum_list = CIBlockProperty::GetPropertyEnum($code, Array(), Array("IBLOCK_ID"=>$iblock, "EXTERNAL_ID"=>$guid));
        if($ar_enum_list = $db_enum_list->GetNext())
        {
            $result = $ar_enum_list["ID"];
        }
        return ($result);
    }

    public static function add($guidTiket)
    {

        $Iblock = $guidTiket["iblocId"];
        $guidStatus = strtoupper($guidTiket["Status"]);
	    $guidGrup = strtoupper($guidTiket["Group"]);
        $datTime = strtotime($guidTiket["RegisteredOn"]);
        $prop = array();

        $prop['Origin'] = $guidTiket["Origin"];
        $prop["idOldTiket"] = $guidTiket["idOldTiket"];
        if(empty($guidTiket["Owner_display"])){
            $guidTiket["Owner_display"] = BITRIX24_MANAGER_VLASENKO;
            $guidTiket["Owner"] = ownerIdGuid (BITRIX24_MANAGER_VLASENKO);
        }
        $prop['Owner_display'] = $guidTiket["Owner_display"];
        $prop['Owner'] = $guidTiket["Owner"];

        $prop['DATA_I_VREMYA'] = date("d.m.Y H:i:s", $datTime);
        $prop['ID_ZADACHI_PO_OBRASHCHENIYU'] = $guidTiket["Id"];
        $prop['NUM_OBRASHCHENIYA_V_SERVICEDESK'] = $guidTiket["Number"];
        $prop["ISTORIYA_RABOTY_S_OBRASHCHENIEM"] = array(date("m.d.y H:i:s").": ".$guidTiket["Symptoms"]);

        $prop['Priority'] = $guidTiket["Priority"];
        $prop['Priority_display'] = $guidTiket["Priority_display"];
        $prop['ConfItem'] = $guidTiket["ConfItem"];
        $prop['UsrConfLocation'] = $guidTiket["UsrConfLocation"];
        $prop['Contact'] = $guidTiket["Contact"];
        $prop['Contact_display'] = $guidTiket["Contact_display"];
        $prop['Account'] = $guidTiket["Account"];
        $prop['Account_display'] = $guidTiket["Account_display"];
        $prop['UsrCaseType'] = $guidTiket["UsrCaseType"];
        $prop['UsrCaseType_display'] = $guidTiket["UsrCaseType_display"];

        $prop['UsrPhoneNumber'] = $guidTiket["UsrPhoneNumber"];
        $prop['UsrCaseType_display'] = $guidTiket["UsrCaseType_display"];
        $prop['Category'] = $guidTiket["Category"];
        $prop['Category_display'] = $guidTiket["Category_display"];
        $prop['UsrINN'] = $guidTiket["UsrINN"];


        $prop['REASON_CLOSURE'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "REASON_CLOSURE", $guidTiket["reason_closure"]));
        $prop['GRUPPY_OTVETSTVENNYKH'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "GRUPPY_OTVETSTVENNYKH", $guidGrup));
        $prop['STATUS_OBRASHCHENIYA'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "STATUS_OBRASHCHENIYA", $guidStatus));
        $prop['KATEGORIYA'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "KATEGORIYA", $guidTiket["KATEGORIYA"]));
        $prop['TEKST_SOOBSHCHENIYA'] = $guidTiket["TEKST_SOOBSHCHENIYA"];

        $arFields = Array(
            "DATE_CREATE" => date("d.m.Y H:i:s"),
            "IBLOCK_ID" => $Iblock,
            "PROPERTY_VALUES" => $prop,
            "NAME" => $guidTiket["Subject"],
            "ACTIVE" => "Y"
        );
        //$logger = Logger::getLogger('creatTiket','ofd.bitrix24/creatTiket.log');
        //$logger->log($guidTiket);
        $el = new CIBlockElement;
        if($ELEMENT_ID = $el->Add($arFields))
            $result = "Y";
        else
            $result = $el->LAST_ERROR;
        return $result;
    }

    public static function histories ($BL_ID, $ELEMENT_ID, $newText){
        $objDateTime = new DateTime();
        
        if(CModule::IncludeModule("iblock"))
        {
            $VALUES[] = array(
                "VALUE" => array(
                    "TEXT"=>$objDateTime->toString().": ".$newText,
                    "TYPE"=>"TEXT"
                ));
            $res = CIBlockElement::GetProperty($BL_ID, $ELEMENT_ID, Array("sort"=>"asc"), array("CODE" => "ISTORIYA_RABOTY_S_OBRASHCHENIEM"));
            while ($ob = $res->GetNext())
            {
                $text = $ob['VALUE'];
                $VALUES[] = array(
                    "VALUE" => $text);
            }

            $result = CIBlockElement::SetPropertyValueCode($ELEMENT_ID, "ISTORIYA_RABOTY_S_OBRASHCHENIEM", $VALUES);
            return ($result);
        }
    }

    public static function update($guidTiket){
        $guidStatus = strtoupper($guidTiket["Status"]);
        $guidOwner = strtoupper($guidTiket["Group"]);
        $datTime = strtotime($guidTiket["RegisteredOn"]);
        $prop = array();
        $Iblock = $guidTiket["iblocId"];
        $Origin = strtoupper($guidTiket["Origin"]);
        $prop['Origin'] = $Origin;
        $oldTiket = strtoupper($guidTiket["idOldTiket"]);
        if($guidTiket["reopen"] == null){
            $prop["idOldTiket"] = "";
        }else{
            $prop["idOldTiket"] = "reopen";
        }
        if(empty($guidTiket["Owner_display"])){
            $guidTiket["Owner_display"] = BITRIX24_MANAGER_VLASENKO;
            $guidTiket["Owner"] = ownerIdGuid (BITRIX24_MANAGER_VLASENKO);
        }
        $prop['Owner_display'] = $guidTiket["Owner_display"];
        $prop['Owner'] = $guidTiket["Owner"];

        $prop['DATA_I_VREMYA'] = date("d.m.Y H:i:s", $datTime);
        $prop['ID_ZADACHI_PO_OBRASHCHENIYU'] = $guidTiket["Id"];
        $prop['NUM_OBRASHCHENIYA_V_SERVICEDESK'] = $guidTiket["Number"];

        $prop['Priority'] = $guidTiket["Priority"];
        $prop['Priority_display'] = $guidTiket["Priority_display"];
        $prop['ConfItem'] = $guidTiket["ConfItem"];
        $prop['UsrConfLocation'] = $guidTiket["UsrConfLocation"];
        $prop['Contact'] = $guidTiket["Contact"];
        $prop['Contact_display'] = $guidTiket["Contact_display"];
        $prop['Account'] = $guidTiket["Account"];
        $prop['Account_display'] = $guidTiket["Account_display"];
        $prop['UsrCaseType'] = $guidTiket["UsrCaseType"];
        $prop['UsrCaseType_display'] = $guidTiket["UsrCaseType_display"];

        $prop['UsrPhoneNumber'] = $guidTiket["UsrPhoneNumber"];
        $prop['UsrCaseType_display'] = $guidTiket["UsrCaseType_display"];
        $prop['Category'] = $guidTiket["Category"];
        $prop['Category_display'] = $guidTiket["Category_display"];
        $prop['UsrINN'] = $guidTiket["UsrINN"];


        $prop['REASON_CLOSURE'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "REASON_CLOSURE", $guidTiket["reason_closure"]));
        $prop['GRUPPY_OTVETSTVENNYKH'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "GRUPPY_OTVETSTVENNYKH", $guidOwner));
        $prop['STATUS_OBRASHCHENIYA'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "STATUS_OBRASHCHENIYA", $guidStatus));
        $prop['KATEGORIYA'] =  array("EXTERNAL_ID" => self::idPropertyGuid($Iblock, "KATEGORIYA", $guidTiket["KATEGORIYA"]));
        $PRODUCT_ID = $guidTiket["idElem"];
        $prop['TEKST_SOOBSHCHENIYA'] = $guidTiket["TEKST_SOOBSHCHENIYA"];

        $newText = trim($guidTiket["Symptoms"]);

        $arFields = Array(
            "DATE_CREATE" => date("d.m.Y H:i:s"),
            "IBLOCK_ID" => $Iblock,
            "PROPERTY_VALUES" => $prop,
            "NAME" => $guidTiket["Subject"],
            "ACTIVE" => "Y"
        );

        $el = new CIBlockElement;

        if($ELEMENT_ID = $el->Update($PRODUCT_ID, $arFields)){
            $result = "U";

        } else{
            $result = $el->LAST_ERROR;
        }
        self::histories($Iblock, $PRODUCT_ID, $newText);
        return $result;
    }
}
