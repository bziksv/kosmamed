<?
use Bitrix\Main\Localization\Loc;
use    Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = 'r52.qrcode';

Loader::includeModule($module_id);
$aTabs = array(
    array(
        "DIV"       => "edit",
        "TAB"       => Loc::getMessage("R52_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("R52_OPTIONS_TAB_NAME"),
        "OPTIONS" => array(
            Loc::getMessage("R52_OPTIONS_TAB_COMMON"),
            array(
                "LOGO_PATH",
                Loc::getMessage("R52_OPTIONS_TAB_LOGO"),
                "/bitrix/modules/r52.qrcode/src/img/1.png",
                array("text")
            ),
        )
    )
);

if($request->isPost() && check_bitrix_sessid()){

    foreach($aTabs as $aTab){

        foreach($aTab["OPTIONS"] as $arOption){

            if(!is_array($arOption)){

                continue;
            }

            if($arOption["note"]){

                continue;
            }

            if($request["apply"]){

                $optionValue = $request->getPost($arOption[0]);

                if($arOption[0] == "switch_on"){

                    if($optionValue == ""){

                        $optionValue = "N";
                    }
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }elseif($request["default"]){

                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin();?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post">

    <?
    foreach($aTabs as $aTab){

        if($aTab["OPTIONS"]){

            $tabControl->BeginNextTab();

//         __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            foreach ($aTab["OPTIONS"] as $option) {
                if(is_array($option)) {
                    echo '
                    <tr>
                        <td valign="top" width="40%" class="adm-detail-content-cell-l">'.$option[1].'</td>
                        <td valign="top" nowrap="" class="adm-detail-content-cell-r">
                            <input id="__FD_PARAM_LOGO_DARK" name="'.$option[0].'" size="25" value="'.COption::GetOptionString('r52.qrcode', $option[0]).'" type="text" style="float: left;">
                            <input value="..." type="button" onclick="window.BX_FD_LOGO_DARK();">
                            <script>
                                setTimeout(function(){
                                    if (BX("bx_fd_input_logo_dark"))
                                        BX("bx_fd_input_logo_dark").onclick = window.BX_FD_LOGO_DARK;
                                }, 200);
                                window.BX_FD_ONRESULT_LOGO_DARK = function(filename, filepath)
                                {
                                    var oInput = BX("__FD_PARAM_LOGO_DARK");
                                    if (typeof filename == "object")
                                        oInput.value = filename.src;
                                    else
                                        oInput.value = (filepath + "/" + filename).replace(/\/\//ig, \'/\');
                                }
                            </script>
                        </td>
                    </tr>';
                }

            }


        }
    }

    $tabControl->Buttons();
    ?>

    <input type="submit" name="apply" value="<? echo(Loc::GetMessage("R52_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />
    <input type="submit" name="default" value="<? echo(Loc::GetMessage("R52_OPTIONS_INPUT_DEFAULT")); ?>" />

    <?
    echo(bitrix_sessid_post());

    ?>

</form>

<?
$tabControl->End();


?>
<script>
    window.BX_FD_LOGO_DARK = function(bLoadJS, Params)
    {
        if (!Params)
            Params = {};

        var UserConfig;
        UserConfig =
            {
                site : 'es',
                path : '/upload',
                view : 'list',
                sort : 'type',
                sort_order : 'asc'
            };
        if (!window.BXFileDialog)
        {
            if (bLoadJS !== false)
                BX.loadScript('/bitrix/js/main/file_dialog.js?16582409387359');
            return setTimeout(function(){window['BX_FD_LOGO_DARK'](false, Params)}, 50);
        }

        var oConfig =
            {
                submitFuncName : 'BX_FD_LOGO_DARKResult',
                select : 'F',
                operation: 'O',
                showUploadTab : true,
                showAddToMenuTab : false,
                site : 'es',
                path : '/upload',
                lang : 'ru',
                fileFilter : '',
                allowAllFiles : true,
                saveConfig : true,
                sessid: "<?=bitrix_sessid()?>",
                checkChildren: true,
                genThumb: true,
                zIndex: 2500				};

        if(window.oBXFileDialog && window.oBXFileDialog.UserConfig)
        {
            UserConfig = oBXFileDialog.UserConfig;
            oConfig.path = UserConfig.path;
            oConfig.site = UserConfig.site;
        }

        if (Params.path)
            oConfig.path = Params.path;
        if (Params.site)
            oConfig.site = Params.site;

        oBXFileDialog = new BXFileDialog();
        oBXFileDialog.Open(oConfig, UserConfig);
    };
    window.BX_FD_LOGO_DARKResult = function(filename, path, site, title, menu)
    {
        let inp = document.querySelector('#__FD_PARAM_LOGO_DARK')
        inp.setAttribute('value', path + '/' + filename)
        console.log(inp)
        path = jsUtils.trim(path);
        path = path.replace(/\\/ig,"/");
        path = path.replace(/\/\//ig,"/");
        if (path.substr(path.length-1) == "/")
            path = path.substr(0, path.length-1);
        var full = (path + '/' + filename).replace(/\/\//ig, '/');
        if (path == '')
            path = '/';

        var arBuckets = [];
        if(arBuckets[site])
        {
            full = arBuckets[site] + filename;
            path = arBuckets[site] + path;
        }

        if ('F' == 'D')
            name = full;

        BX_FD_ONRESULT_LOGO_DARK(filename, path, site, title || '', menu || '');							};

</script>
