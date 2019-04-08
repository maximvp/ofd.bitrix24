<?php

use Bitrix\Main\Loader;

Loader::includeModule("iblock");
Loader::includeModule("crm");

class CIblockEvent
{
    public function propElementUpdate(&$arFields)
    {
        $elementId = $arFields["ID"];
        // получаем ID свойств элемента
        $arPropertiesId = array_keys($arFields["PROPERTY_VALUES"]);

        $arElSelect = Array("ID","NAME","IBLOCK_ID","ACTIVE","PROPERTY_*");

        $arElFilter = Array("IBLOCK_ID" => $arFields["IBLOCK_ID"], "ID" => $elementId);
        $dbElem = CIBlockElement::GetList(Array(), $arElFilter, false, false, $arElSelect);
        while($ob = $dbElem->GetNextElement())
        {
            $arFieldsOld = $ob->GetFields();
            $arProps = $ob->GetProperties();
            $clonTiket["Subject"] = $arFieldsOld["NAME"];
            $clonTiket["UsrINN"] = $arProps["UsrINN"]["VALUE"];
            $clonTiket["KOMPANIYA_KLIENTA"] = $arProps["KOMPANIYA_KLIENTA"]["ID"];
            $clonTiket["LID_KLIENTA"] = $arProps["LID_KLIENTA"]["ID"];
            $clonTiket["E_MAIL"] = trim($arProps["E_MAIL"]["VALUE"]);
            $clonTiket["UsrPhoneNumber"] = trim($arProps["UsrPhoneNumber"]["VALUE"]);
            $clonTiket["Contact_display"] = trim($arProps["Contact_display"]["VALUE"]);
            $clonTiket["Contact"] = trim($arProps["Contact"]["VALUE"]);
            $clonTiket["Priority"] = trim($arProps["Priority"]["VALUE"]);
            $clonTiket["Priority_display"] = trim($arProps["Priority_display"]["VALUE"]);
            $clonTiket["Category"] = trim($arProps["Category"]["VALUE"]);
            $clonTiket["Category_display"] = trim($arProps["Category_display"]["VALUE"]);
            $clonTiket["UsrCaseType"] = trim($arProps["UsrCaseType"]["VALUE"]);
            $clonTiket["UsrCaseType_display"] = trim($arProps["UsrCaseType_display"]["VALUE"]);
            $clonTiket["Contact"] = trim($arProps["Category"]["VALUE"]);
            $clonTiket["Account"] = trim($arProps["Account"]["VALUE"]);
            $clonTiket["Account_display"] = trim($arProps["Account_display"]["VALUE"]);

            $clonTiket["ConfItem"] = trim($arProps["ConfItem"]["VALUE"]);
            $clonTiket["UsrConfLocation"] = trim($arProps["UsrConfLocation"]["VALUE"]);
            $clonTiket["RegisteredOn"] = trim($arProps["DATA_I_VREMYA"]["VALUE"]);
            $clonTiket["Number"] = trim($arProps["NUM_OBRASHCHENIYA_V_SERVICEDESK"]["VALUE"]);
            $clonTiket["Id"] = trim($arProps["ID_ZADACHI_PO_OBRASHCHENIYU"]["VALUE"]);
            $clonTiket["Origin"] = trim($arProps["Origin"]["VALUE"]);
            $clonTiket["idOldTiket"] = trim($arFieldsOld["ID"]);
        }

        

            $dbProperties = CIBlockProperty::GetList(
                Array("sort" => "asc", "name" => "asc"),
                Array("IBLOCK_ID" => $arFields["IBLOCK_ID"], "=ID" => $arPropertiesId)
            );
            while($arProperties = $dbProperties->GetNext())
            {
                // проверяем свойство на соответствие CODE
                if($arProperties["CODE"] == "ISTORIYA_RABOTY_S_OBRASHCHENIEM")
                {
                    $propValue = $arFields["PROPERTY_VALUES"][$arProperties["ID"]];
                    foreach($propValue as $propValueItem)
                    {
                        $istoriyaObrasc[] = $propValueItem["VALUE"]["TEXT"];
                    }
                    $istoriyaObrasc = implode(",", $istoriyaObrasc);
                    $clonTiket["Symptoms"] = $istoriyaObrasc;
                }
                if($arProperties["CODE"] == "KATEGORIYA")
                {
                    $propValue = $arFields["PROPERTY_VALUES"][$arProperties["ID"]];
                    $arEnumList = CIBlockPropertyEnum::GetByID($propValue);
                    $categoryObraschXmlId = $arEnumList["XML_ID"];
                    $clonTiket["KATEGORIYA"] = $categoryObraschXmlId;
                }
                if($arProperties["CODE"] == "STATUS_OBRASHCHENIYA")
                {
                    $propValueSt = $arFields["PROPERTY_VALUES"][$arProperties["ID"]];
                    $arEnumListSt = CIBlockPropertyEnum::GetByID($propValueSt);
                    $statusObraschXmlId = $arEnumListSt["XML_ID"];
                    $clonTiket["Status"] = $statusObraschXmlId;
                    $idPropSt = $arEnumListSt["PROPERTY_ID"];
                }
                if($arProperties["CODE"] == "TEKST_SOOBSHCHENIYA")
                {
                    $propValue = end($arFields["PROPERTY_VALUES"][$arProperties["ID"]]);
                    $textSoobsc = HTMLToTxt($propValue["VALUE"]["TEXT"],"",array(),false);
                }
                if($arProperties["CODE"] == "REASON_CLOSURE")
                {
                    $propValue = $arFields["PROPERTY_VALUES"][$arProperties["ID"]];
                    $arEnumList = CIBlockPropertyEnum::GetByID($propValue);
                    $reasonClosureXmlId = $arEnumList["XML_ID"];
                    $clonTiket["reason_closure"] = $reasonClosureXmlId;
                }
                if($arProperties["CODE"] == "GRUPPY_OTVETSTVENNYKH")
                {
                    $propValueId = $arFields["PROPERTY_VALUES"][$arProperties["ID"]];
                    $arEnumListOtv = CIBlockPropertyEnum::GetByID($propValueId);
                    $gruppiOtvetstXmlId = $arEnumListOtv["XML_ID"];
                    $idProp = $arEnumListOtv["PROPERTY_ID"];
                }
            }


            $res = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $elementId, "sort", "asc", array("CODE" => "GRUPPY_OTVETSTVENNYKH"));
                while ($ob = $res->GetNext())
                {
                    $gruppiOtvetstXmlIdOld = $ob['VALUE_XML_ID'];
                }

            $resd = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $elementId, "sort", "asc", array("CODE" => "STATUS_OBRASHCHENIYA"));
                while ($obs = $resd->GetNext())
                    {
                        $statusXmlIdOld = $obs['VALUE_XML_ID'];
                    }
            if($statusXmlIdOld == ID_STATUS_CLOSE and !empty($textSoobsc)){
                session_name('STATUS_CLOSE');
                session_start();
                $_SESSION["STATUS_CLOSE"][$propValueSt]["propValueSt"] = $propValueSt;
                $_SESSION["STATUS_CLOSE"][$idPropSt]["idPropSt"] = $idPropSt;
                //pre($_SESSION["GROUP_EXPLUATEION"]);
                //exit;

            }
            if($statusXmlIdOld != ID_STATUS_CLOSE ){
                if($_SESSION["STATUS_CLOSE"][$propValueSt] or $_SESSION["STATUS_CLOSE"][$idPropSt]){
                    unset($_SESSION["STATUS_CLOSE"][$propValueSt]);
                    unset($_SESSION["STATUS_CLOSE"][$idPropSt]);
                }
            }
            if($gruppiOtvetstXmlId == ID_GROUP_EXPLUATEION){
                session_name('GROUP_EXPLUATEION');
                session_start();
                $_SESSION["GROUP_EXPLUATEION"][$propValueId]["propValueId"] = $propValueId;
                $_SESSION["GROUP_EXPLUATEION"][$idProp]["idProp"] = $idProp;
                //pre($_SESSION["GROUP_EXPLUATEION"]);
                //exit;

            }

            if(!empty($gruppiOtvetstXmlId) and $gruppiOtvetstXmlId != $gruppiOtvetstXmlIdOld and $gruppiOtvetstXmlId != ID_GROUP_EXPLUATEION){
                $clonTiket["Group"] = $gruppiOtvetstXmlId;
                if($_SESSION["GROUP_EXPLUATEION"][$idProp] or $_SESSION["GROUP_EXPLUATEION"][$propValueId]){
                    unset($_SESSION["GROUP_EXPLUATEION"][$propValueId]);
                    unset($_SESSION["GROUP_EXPLUATEION"][$idProp]);
                }

                switch ($gruppiOtvetstXmlId)
                {
                    case (ID_GROUP_SUPPORT):
                        (int)$clonTiket["iblocId"] = returnIdCodeIblock(IBLOK_TWO_LINE);
                        break;
                    case (ID_GROUP_SALE):
                        (int)$clonTiket["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_SALE);
                        break;
                    case (ID_GROUP_SALE_PARTNER):
                        (int)$clonTiket["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_SALE_PARTNER);
                        break;
                    case (ID_GROUP_MARKETING):
                        (int)$clonTiket["iblocId"] = returnIdCodeIblock(IBLOK_OTDEL_MARKET);
                        break;
                    default:
                        (int)$clonTiket["iblocId"] = returnIdCodeIblock(IBLOK_TWO_LINE);
                }

                //пересоздание и удаление тикета
                if($clonTiket["iblocId"] != $arFields["IBLOCK_ID"]){
                    $rezClon = TiketBitrix::add($clonTiket);
                    if($rezClon == "Y"){
                        CIBlockElement::Delete($elementId);
                    }
                }

            }
            //pre($arFields["RIGHTS"]);
            //exit();
            if($statusObraschXmlId == ID_STATUS_RE_OPEN){
                global $APPLICATION;
                $APPLICATION->ThrowException("Данный статус обращения устанавливается в bpm'online!");
                return false;
            }
            if($statusObraschXmlId == ID_STATUS_CLOSE and empty($textSoobsc)){
                global $APPLICATION;
                $APPLICATION->ThrowException("При выборе статуса: задача закрыта, нужно указать решение при закрытии обращения!");
                return false;
            }
            if($statusObraschXmlId == ID_STATUS_CANCELLED and empty($reasonClosureXmlId)) {
                global $APPLICATION;
                $APPLICATION->ThrowException('При выборе статуса: отменено, нужно указать причину отмены!');
                return false;
            }


    }
public function iblockElementUpdate(&$arFields)
    {
        // получаем ID свойств элемента
        $arPropertiesId = array_keys($arFields["PROPERTY_VALUES"]);

        // получаем CODE инфоблока и сравниваем с кодами инфоблоков обращений
        $dbIBlock = CIBlock::GetByID($arFields["IBLOCK_ID"]);
        if($arIBlock = $dbIBlock->GetNext())
        {
            $iblockCode = $arIBlock["CODE"];
            if($iblockCode == IBLOK_TWO_LINE or $iblockCode == IBLOK_OTDEL_MARKET or $iblockCode == IBLOK_OTDEL_SALE or $iblockCode == IBLOK_OTDEL_SALE_PARTNER)
            {
                // по ID элемента выбираем все поля элемента и сравниваем с теми, что были отправлены. Если есть изменения, то отправляем запрос в bpm
                $elementId = $arFields["ID"];
                $triggerUpdate = false;

                $arElSelect = Array("ID", "NAME");
                if(!empty($arPropertiesId))
                {
                    foreach($arPropertiesId as $propertyId)
                    {
                        $arElSelect[] = "PROPERTY_".$propertyId;
                    }
                }
                $arElFilter = Array("IBLOCK_ID" => $arFields["IBLOCK_ID"], "ID" => $elementId);
                $dbElem = CIBlockElement::GetList(Array(), $arElFilter, false, false, $arElSelect);
                while($arElem = $dbElem->GetNext())
                {
                    // сравниваем имена
                    if($arElem["NAME"] != $arFields["NAME"]) $triggerUpdate = true;

                    // сравниваем свойства
                    if(!empty($arFields["PROPERTY_VALUES"]))
                    {
                        foreach($arFields["PROPERTY_VALUES"] as $keyProperty => $property)
                        {
                            $propertyValue = end($property);
                            $propertyValue = $propertyValue["VALUE"];

                            // для несписочных свойств
                            if(!isset($arElem["PROPERTY_".$keyProperty."_ENUM_ID"]))
                            {
                                // если поле не типа техt/html
                                if(!is_array($propertyValue))
                                {
                                    if($arElem["PROPERTY_".$keyProperty."_VALUE"] != $propertyValue) $triggerUpdate = true;
                                }
                                else
                                {
                                    // для текстовых
                                    if($propertyValue["TEXT"])
                                    {
                                        if($arElem["PROPERTY_".$keyProperty."_VALUE"]["TEXT"] != $propertyValue["TEXT"]) $triggerUpdate = true;
                                    }
                                }
                            }
                            // для списочного свойства
                            else
                            {
                                if($arElem["PROPERTY_".$keyProperty."_ENUM_ID"] != $propertyValue) $triggerUpdate = true;
                            }
                        }
                    }
                }

                // отсылка данных в bpm
                if($triggerUpdate)
                {
                    switch ($iblockCode){
                        case (IBLOK_TWO_LINE):
                            $grup_soc = GRUP_SOC_SUPPORT;
                            break;
                        case (IBLOK_OTDEL_MARKET):
                            $grup_soc = GRUP_SOC_MARKETING;
                            break;
                        case (IBLOK_OTDEL_SALE):
                            $grup_soc = GRUP_SOC_SALE;
                            break;
                        case (IBLOK_OTDEL_SALE_PARTNER):
                            $grup_soc = GRUP_SOC_SALE_PARTNER;
                            break;
                    }
                    $elementId = $arFields["ID"];
                    $IBLOCK_ID = $arFields["IBLOCK_ID"];

                    $arElSelect = Array("ID","NAME","IBLOCK_ID","PROPERTY_UsrINN","PROPERTY_idOldTiket","PROPERTY_ID_ZADACHI_PO_OBRASHCHENIYU","PROPERTY_TEKST_SOOBSHCHENIYA","PROPERTY_ISTORIYA_RABOTY_S_OBRASHCHENIEM","PROPERTY_KOMPANIYA_KLIENTA","PROPERTY_LID_KLIENTA","PROPERTY_E_MAIL","PROPERTY_UsrPhoneNumber");

                    $arElFilter = Array("IBLOCK_ID" => $IBLOCK_ID, "ID" => $elementId);
                    $dbElem = CIBlockElement::GetList(Array(), $arElFilter, false, false, $arElSelect);

                    while($ob = $dbElem->GetNextElement())
                    {
                        $arFieldsOld = $ob->GetFields();
                        $arProps = $ob->GetProperties();
                        $historys = $arProps["ISTORIYA_RABOTY_S_OBRASHCHENIEM"]["VALUE"];
                        $reshenie = htmlspecialcharsBack($arProps["TEKST_SOOBSHCHENIYA"]["VALUE"]["TEXT"]);
                        $guid = trim($arProps["ID_ZADACHI_PO_OBRASHCHENIYU"]["VALUE"]);
                        $idOldTiket = $arFieldsOld["ID"];
                    }
                    $href = "https://".$_SERVER["HTTP_HOST"]."/workgroups/group/".$grup_soc."/lists/".$IBLOCK_ID."/element/0/".$elementId."/";
//pre($arProps);
        //exit;
                    //CIBlockElement::GetPropertyValues();
                    if(is_array($historys) and count($historys) > 1){
                        foreach ($historys as $val=>$hist){
                            $his[] = $hist["TEXT"];
                        }
                        $history = implode(",", $his);
                    }else{
                        $history = $historys[0]["TEXT"];
                    }
                    //формирование данных на первичное обновление данных тикета на стороне bpm
                    $link = array();
                    $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "STATUS_OBRASHCHENIYA"));
                    while ($ob = $res->GetNext())
                    {
                        $link["status"] = $ob['VALUE_XML_ID'];
                    }
                    $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "GRUPPY_OTVETSTVENNYKH"));
                    while ($ob = $res->GetNext())
                    {
                        $link["grup_owner"] = $ob['VALUE_XML_ID'];
                    }
                    $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "REASON_CLOSURE"));
                    while ($ob = $res->GetNext())
                    {
                        $link["reason_closure"] = $ob['VALUE_XML_ID'];
                    }
                    
                    $link["idOldTiket"] = $idOldTiket;
                    $link["href"] = $href;
                    $link["id"] = $guid;
                    $link["history"] = $history;
                    if($link["grup_owner"] == ID_GROUP_EXPLUATEION){
                        $link["owner"] = null;
                    }else{
                        $link["owner"] = BPM_MANAGER_VLASENKO;
                    }

                    $link["reshenie"]  = $reshenie;
                    //pre($link);
                    //exit;
                    $tiketUpdate = CrmBitrixBpmUpdate::tiketUpdate($link);
		    CIBlockElement::SetPropertyValueCode($elementId, "idOldTiket", $elementId);
                    $tikUp["tiketUpdate"] = $tiketUpdate;
                    $tikUp["link"] = $link;
                    $logger = Logger::getLogger('ProblemUpdateTiket','ofd.bitrix24/ProblemUpdateTiket.log');
                    $logger->log($tikUp);
                }
            }
        }
    }
    public function iblockElementAdd(&$arFields){

        // получаем CODE инфоблока и сравниваем с кодами инфоблоков обращений
        $dbIBlock = CIBlock::GetByID($arFields["IBLOCK_ID"]);
        if($arIBlock = $dbIBlock->GetNext())
        {
            $iblockCode = $arIBlock["CODE"];
            if($iblockCode == IBLOK_TWO_LINE or $iblockCode == IBLOK_OTDEL_MARKET or $iblockCode == IBLOK_OTDEL_SALE or $iblockCode == IBLOK_OTDEL_SALE_PARTNER)
            {
                switch ($iblockCode){
                    case (IBLOK_TWO_LINE):
                        $grup_soc = GRUP_SOC_SUPPORT;
                        break;
                    case (IBLOK_OTDEL_MARKET):
                        $grup_soc = GRUP_SOC_MARKETING;
                        break;
                    case (IBLOK_OTDEL_SALE):
                        $grup_soc = GRUP_SOC_SALE;
                        break;
                    case (IBLOK_OTDEL_SALE_PARTNER):
                        $grup_soc = GRUP_SOC_SALE_PARTNER;
                        break;
                }
                $elementId = $arFields["ID"];
                $IBLOCK_ID = returnIdCodeIblock ($iblockCode);
                $arElSelect = Array("ID","NAME","IBLOCK_ID","PROPERTY_UsrINN","PROPERTY_idOldTiket","PROPERTY_Origin","PROPERTY_UsrCaseType","PROPERTY_ID_ZADACHI_PO_OBRASHCHENIYU","PROPERTY_TEKST_SOOBSHCHENIYA","PROPERTY_ISTORIYA_RABOTY_S_OBRASHCHENIEM","PROPERTY_KOMPANIYA_KLIENTA","PROPERTY_LID_KLIENTA","PROPERTY_E_MAIL","PROPERTY_UsrPhoneNumber");

                $arElFilter = Array("IBLOCK_ID" => $IBLOCK_ID, "ID" => $elementId);
                $dbElem = CIBlockElement::GetList(Array(), $arElFilter, false, false, $arElSelect);

                while($ob = $dbElem->GetNextElement())
                {
                    $arFieldsOld = $ob->GetFields();
                    $arProps = $ob->GetProperties();
                    $titleLid = $arFieldsOld["NAME"];
                    $idOldTiket = $arProps["idOldTiket"]["VALUE"];
                    $inn = $arProps["UsrINN"]["VALUE"];
                    $id = $arProps["KOMPANIYA_KLIENTA"]["ID"];
                    $idLid = $arProps["LID_KLIENTA"]["ID"];
                    $email = trim($arProps["E_MAIL"]["VALUE"]);
                    $phone = trim($arProps["UsrPhoneNumber"]["VALUE"]);
                    $contactName = trim($arProps["Contact_display"]["VALUE"]);
                    $historys = $arProps["ISTORIYA_RABOTY_S_OBRASHCHENIEM"]["VALUE"];
                    $reshenie = htmlspecialcharsBack($arProps["TEKST_SOOBSHCHENIYA"]["VALUE"]["TEXT"]);
                    $guid = trim($arProps["ID_ZADACHI_PO_OBRASHCHENIYU"]["VALUE"]);
                    $Origin = $arProps["Origin"]["VALUE"];
                }
                $href = "https://".$_SERVER["HTTP_HOST"]."/workgroups/group/".$grup_soc."/lists/".$IBLOCK_ID."/element/0/".$elementId."/";
                $nameLink = "<a href='$href' >Ссылка на обращение в Битрикс24</a>";

                
                CIBlockElement::GetPropertyValues();
                if(is_array($historys) and count($historys) > 1){
                    foreach ($historys as $val=>$hist){
                                $his[] = $hist["TEXT"];
                    }
                    $history = implode(",", $his);
                }else{
                    $history = $historys[0]["TEXT"];
                }

                //формирование данных на первичное обновление данных тикета на стороне bpm
                $link = array();
                $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "STATUS_OBRASHCHENIYA"));
                while ($ob = $res->GetNext())
                {
                    $link["status"] = $ob['VALUE_XML_ID'];
                }
                $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "GRUPPY_OTVETSTVENNYKH"));
                while ($ob = $res->GetNext())
                {
                    $link["grup_owner"] = $ob['VALUE_XML_ID'];
                }
                $res = CIBlockElement::GetProperty($IBLOCK_ID, $elementId, "sort", "asc", array("CODE" => "REASON_CLOSURE"));
                while ($ob = $res->GetNext())
                {
                    $link["reason_closure"] = $ob['VALUE_XML_ID'];
                }
                $link["idOldTiket"] = $idOldTiket;
                $link["href"] = $href;
                $link["id"] = $guid;
                $link["history"] = $history;
                $link["owner"] = BPM_MANAGER_VLASENKO;
                $link["reshenie"] =  $reshenie;

                $idContr = RestBpmBitrix::verify($inn);
                $contr_id = $idContr["id"];

                if($contr_id){
                    CIBlockElement::SetPropertyValues($elementId, $IBLOCK_ID, $contr_id, $id);
                }else{
                    $dbResMultiFields = CCrmFieldMulti::GetList(
                        array('ID' => 'asc'),
                        array('ENTITY_ID' => 'LEAD', 'TYPE_ID' => 'EMAIL')
                    );
                    while ($arMultiFields = $dbResMultiFields->Fetch()) {
                        $comunicetions[] = $arMultiFields;
                    }
                    if($email) {
                        foreach ($comunicetions as $com => $value) {
                            if ($value["TYPE_ID"] == "EMAIL") {
                                switch ($value["VALUE"]) {
                                    case $email:
                                        $email_id = $value["ELEMENT_ID"];
                                       // break;
                                }
                            }
                        }
                        
                        if(!empty($email_id)){
                            CIBlockElement::SetPropertyValues($elementId, $IBLOCK_ID, $email_id, $idLid);
                        }

                    }

                    if($phone) {
                        $dbResMultiFields = CCrmFieldMulti::GetList(
                            array('ID' => 'asc'),
                            array('ENTITY_ID' => 'LEAD', 'TYPE_ID' => 'PHONE')
                        );
                        while ($arMultiFields = $dbResMultiFields->Fetch()) {
                            $comunicetions[] = $arMultiFields;
                        }
                        foreach ($comunicetions as $com => $value) {
                            if ($value["TYPE_ID"] == "PHONE") {
                                switch (trim($value["VALUE"])) {
                                    case trim($phone):
                                        $phone_id = $value["ELEMENT_ID"];
                                        break;

                                }

                            }

                        }
                        if(!empty($phone_id)){
                            CIBlockElement::SetPropertyValues($elementId, $IBLOCK_ID, $phone_id, $idLid);
                        }

                    }

                    if(empty($email_id) and empty($phone_id)){
                        switch ($Origin){
                            case (strtolower(BPM_ORIGIN_CALL)):
                                $surs = ID_ORIGIN_CALL;
                                break;
                            case (strtolower(BPM_ORIGIN_EMAIL)):
                                $surs = ID_ORIGIN_EMAIL;
                                break;
                            default:
                                $surs = ID_ORIGIN_CALL;
                        }
                        if(empty($email)){
                            $arr=preg_split('/\(|\)(, *)?/',$contactName,-1,PREG_SPLIT_NO_EMPTY);
                            foreach($arr as $key => $val) {
                                $output = filter_var($val, FILTER_VALIDATE_EMAIL);
                                if($output) {
                                     $email = $output;
                                }
                            }
                        }

                        //$ifoContact  = explode(" ", $contactName); $ifoContact[0]
                        $arFields = Array(
                            "TITLE" => "Обращения на 2-ую линию",
                            "COMPANY_TITLE" => "",
                            "NAME" => $contactName,
                            "LAST_NAME" => "",
                            "SECOND_NAME" => "",
                            "POST" => "",
                            "ADDRESS" => "",
                            "COMMENTS" => $nameLink."<br>".$history,
                            "SOURCE_DESCRIPTION" => "Источник servicedesk",
                            "STATUS_DESCRIPTION" => "",
                            "OPPORTUNITY" => "",
                            "CURRENCY_ID" => "",
                            "PRODUCT_ID" => "",
                            "SOURCE_ID" => $surs,
                            "STATUS_ID" => "NEW",
                            "ASSIGNED_BY_ID" => BITRIX24_MANAGER_BUNTOVA,
                            "FM" => Array(
                                "EMAIL" => Array(
                                    "n1" => Array(
                                        "VALUE" => $email,
                                        "VALUE_TYPE" => "WORK",
                                    )
                                ),
                                "PHONE" => Array(
                                    "n1" => Array(
                                        "VALUE" => $phone,
                                        "VALUE_TYPE" => "WORK",
                                    )
                                ),
                            ),
                            "UF_CHANNEL" => ID_CHANNEL,
                        );
                    }
					
					//Устанавливаем состояние лида в [ACTIVITY]
                    $arFields["UF_STATE_LEAD"] = getIdElementListState ('XML_STATE_LEAD_ACTIVITY');
					
                    $oLead = new CCrmLead;
                    $LidID=$oLead->Add($arFields);
                    if($LidID){
                        CIBlockElement::SetPropertyValues($elementId, $IBLOCK_ID, $LidID, $idLid);
                    }

                    $logger = Logger::getLogger('LeadAdd','ofd.bitrix24/LeadAdd.log');
                    $logger->log(array($LidID,$arFields));
                }
                $tiketUpdate = CrmBitrixBpmUpdate::tiketUpdate($link);
		CIBlockElement::SetPropertyValueCode($elementId, "idOldTiket", $elementId);
                $tikUp["tiketUpdate"] = $tiketUpdate;
                $tikUp["link"] = $link;
                if(!empty($link["idOldTiket"])){
                    $logger = Logger::getLogger('eventOldTiketCreat','ofd.bitrix24/eventOldTiketCreat.log');
                    $logger->log($tikUp);
                }else{
                    $logger = Logger::getLogger('eventNewTiketCreat','ofd.bitrix24/eventNewTiketCreat.log');
                    $logger->log($tikUp);
                }
                
            }
        }
    }
}


