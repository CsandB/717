<? if ( ! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?
$arMenu = array();
foreach ($arResult["SITES"] as $key => $arSite) {
    $arMenu[] = array(
        $arSite["NAME"],
        $arSite["DIR"],
        array(),
        array(),
        "",
    );
}
$GLOBALS["arMenuSites"] = $arMenu;
?>
