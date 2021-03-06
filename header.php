<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_GET["RELOAD"]) && $_GET["RELOAD"] == "Y")
{
	return; //Live Feed Ajax
}
else if (strpos($_SERVER["REQUEST_URI"], "/historyget/") > 0)
{
	return;
}
else if (isset($_GET["IFRAME"]) && $_GET["IFRAME"] == "Y" && !isset($_GET["SONET"]))
{
	//For the task iframe popup
	$APPLICATION->SetPageProperty("BodyClass", "task-iframe-popup");
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/bitrix24.js", true);
	return;
}

CModule::IncludeModule("intranet");

$APPLICATION->GroupModuleJS("timeman","im");
$APPLICATION->GroupModuleJS("webrtc","im");
$APPLICATION->GroupModuleJS("pull","im");
$APPLICATION->GroupModuleCSS("timeman","im");
$APPLICATION->MoveJSToBody("im");
$APPLICATION->MoveJSToBody("timeman");
$APPLICATION->SetUniqueJS('bx24', 'template');
$APPLICATION->SetUniqueCSS('bx24', 'template');

$isCompositeMode = defined("USE_HTML_STATIC_CACHE");
$isIndexPage = $APPLICATION->GetCurPage(true) == SITE_DIR."index.php";

if ($isCompositeMode && $isIndexPage)
{
	define("BITRIX24_INDEX_COMPOSITE", true);
}

if ($isCompositeMode)
{
	$APPLICATION->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");
}

function showJsTitle()
{
	$GLOBALS["APPLICATION"]->AddBufferContent("getJsTitle");
}

function getJsTitle()
{
	$title = $GLOBALS["APPLICATION"]->GetTitle("title", true);
	$title = html_entity_decode($title, ENT_QUOTES, SITE_CHARSET);
	$title = CUtil::JSEscape($title);
	return $title;
}

$isDiskEnabled = \Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?\Bitrix\Main\Localization\Loc::loadMessages($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".SITE_TEMPLATE_ID."/header.php");?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?if (IsModuleInstalled("bitrix24")):?>
<meta name="apple-itunes-app" content="app-id=561683423" />
<link rel="apple-touch-icon-precomposed" href="/images/iphone/57x57.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/iphone/72x72.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/iphone/114x114.png" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/iphone/144x144.png" />
<?endif;

$APPLICATION->ShowHead();
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/bitrix24.js", true);
?>
<link rel="stylesheet" href="bstrap.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<style>
.sidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    background-color: #eef2f4;
    overflow-x: hidden;
    transition: 0.5s;
    padding-top: 50px;
	padding-left: 0px;
	margin-right:inherit;
    text-align:left;
	font-family:"OpenSans", Helvetica, Arial, sans-serif;
	vertical-align: top;
	z-index:200;
	
}

.sidenav_inner {
    height: 100%;
    width: 0;
    position: absolute;
	padding-left: 15px;
    text-align:left;
	font-family:"OpenSans", Helvetica, Arial, sans-serif;
	
}

.sidenav a {
    padding: 0px 8px 8px 32px;
    text-decoration: none;
    font-size: 15px;
    color: #666666;
    display: block;
    transition: 0.3s

}

.sidenav a:hover{
    color: #f1f1f1;
}

.sidenav .closebtn {
    position: absolute;
    top: 0;
    right: 25px;
    font-size: 36px;
    margin-left: 50px;
}

@media screen and (max-height: 450px) {
  .sidenav {padding-top: 15px;}
  .sidenav a {font-size: 18px;}
}
</style>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="jquery-3.1.1.min.js"></script>
<title><? if (!$isCompositeMode || $isIndexPage) $APPLICATION->ShowTitle()?></title>
</head>

<body class="template-bitrix24">
<?
if ($isCompositeMode && !$isIndexPage)
{
	$frame = new \Bitrix\Main\Page\FrameStatic("title");
	$frame->startDynamicArea();
	?><script type="text/javascript">document.title = "<?showJsTitle()?>";</script><?
	$frame->finishDynamicArea();
}

$profile_link = (CModule::IncludeModule("extranet") && SITE_ID == CExtranet::GetExtranetSiteID()) ? SITE_DIR."contacts/personal" : SITE_DIR."company/personal";
?>
<table class="bx-layout-table">
	<tr>
		<td class="bx-layout-header">
			<?
			if ((!IsModuleInstalled("bitrix24") || $USER->IsAdmin()) && !defined("SKIP_SHOW_PANEL"))
				$APPLICATION->ShowPanel();
			?>
			<div id="header">
				<div id="header-inner">
					<?
					//This component is used for menu-create-but.
					//We have to include the component before bitrix:timeman for composite mode.
					if (CModule::IncludeModule('tasks') && CBXFeatures::IsFeatureEnabled('Tasks')):
						$APPLICATION->IncludeComponent(
							"bitrix:tasks.iframe.popup",
							".default",
							array(
								"ON_TASK_ADDED" => "#SHOW_ADDED_TASK_DETAIL#",
								"ON_TASK_CHANGED" => "BX.DoNothing",
								"ON_TASK_DELETED" => "BX.DoNothing"
							),
							null,
							array("HIDE_ICONS" => "Y")
						);
					endif;

					if (!CModule::IncludeModule("extranet") || CExtranet::GetExtranetSiteID() != SITE_ID)
					{
						if (!IsModuleInstalled("timeman") ||
							!$APPLICATION->IncludeComponent('bitrix:timeman', 'bitrix24', array(), false, array("HIDE_ICONS" => "Y" ))
						)
						{
							$APPLICATION->IncludeComponent('bitrix:planner', 'bitrix24', array(), false, array("HIDE_ICONS" => "Y" ));
						}
					}
					else
					{
						CJSCore::Init("timer");?>
						<div class="timeman-wrap">
							<span id="timeman-block" class="timeman-block">
								<span class="bx-time" id="timeman-timer"></span>
							</span>
						</div>
						<script type="text/javascript">BX.ready(function() {
							BX.timer.registerFormat("bitrix24_time", B24.Timemanager.formatCurrentTime);
							BX.timer({
								container: BX("timeman-timer"),
								display : "bitrix24_time"
							});
						});</script>
					<?
					}
					?>
					<!--suppress CheckValidXmlInScriptTagBody -->
					<script type="text/javascript" data-skip-moving="true">
						(function() {
							var isAmPmMode = <?=(IsAmPmMode() ? "true" : "false") ?>;
							var time = document.getElementById("timeman-timer");
							var hours = new Date().getHours();
							var minutes = new Date().getMinutes();
							if (time)
							{
								time.innerHTML = formatTime(hours, minutes, 0, isAmPmMode);
							}
							else if (document.addEventListener)
							{
								document.addEventListener("DOMContentLoaded", function() {
									time.innerHTML = formatTime(hours, minutes, 0, isAmPmMode);
								});
							}

							function formatTime(hours, minutes, seconds, isAmPmMode)
							{
								var ampm = "";
								if (isAmPmMode)
								{

									ampm = hours >= 12 ? "PM" : "AM";
									ampm = '<span class="time-am-pm">' + ampm + '</span>';
									hours = hours % 12;
									hours = hours ? hours : 12;
								}
								else
								{
									hours = hours < 10 ? "0" + hours : hours;
								}

								return	'<span class="time-hours">' + hours + '</span>' + '<span class="time-semicolon">:</span>' +
									'<span class="time-minutes">' + (minutes < 10 ? "0" + minutes : minutes) + '</span>' + ampm;
							}
						})();
					</script>
					<div class="header-logo-block">
						<span class="header-logo-block-util"></span>
						<?if (IsModuleInstalled("bitrix24")):?>
							<a id="logo_24_a" href="<?=SITE_DIR?>" title="<?=GetMessage("BITRIX24_LOGO_TOOLTIP")?>" class="logo"><?
								$clientLogo = COption::GetOptionInt("bitrix24", "client_logo", "");?>
								<span id="logo_24_text" <?if ($clientLogo):?>style="display:none"<?endif?>>
									<span class="logo-text"><?=htmlspecialcharsbx(COption::GetOptionString("bitrix24", "site_title", ""))?></span><?
									if(COption::GetOptionString("bitrix24", "logo24show", "Y") !=="N"):?><span class="logo-color">24</span><?endif?>
								</span>
								<img id="logo_24_img" src="<?if ($clientLogo) echo CFile::GetPath($clientLogo)?>" <?if (!$clientLogo):?>style="display:none;"<?endif?>/>
							</a>
						<?else:?>
							<?
							$logoID = COption::GetOptionString("main", "wizard_site_logo", "", SITE_ID);
							?>
                            <span id="logo_24_a" style="font-size:30px;cursor:pointer;color:#FFFFFF" onclick="toggleNavBar()" class="logoNav">&#9776;</span>
							<a id="logo_24_a" href="<?=SITE_DIR?>" title="<?=GetMessage("BITRIX24_LOGO_TOOLTIP")?>" class="logo">
								<?if ($logoID):?>
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_name.php"), false);?>
								<?else:?>
									<span id="logo_24_text">
										<span class="logo-text"><?=htmlspecialcharsbx(COption::GetOptionString("main", "site_name", ""));?></span>
										<span class="logo-color">24</span>
									</span>
								<?endif?>
							</a>
						<?endif?>
						<?
						$GLOBALS["LEFT_MENU_COUNTERS"] = array();
						if (CModule::IncludeModule("im") && CBXFeatures::IsFeatureEnabled('WebMessenger'))
						{
							if (defined('BX_IM_FULLSCREEN'))
							{?>
								<span class="header-informers-wrap" id="im-container">
									<span id="im-informer-messages" class="header-informers header-informer-messages" onclick="B24.showMessagePopup(this)"></span><span onclick="B24.showNotifyPopup(this)" id="im-informer-events" class="header-informers header-informer-events"></span>
									<?if (COption::GetOptionString('bitrix24', 'network', 'N') == 'Y'):
										$networkLink = "https://www.bitrix24.net/?user_lang=".LANGUAGE_ID."&utm_source=b24&utm_medium=itop&utm_campaign=BITRIX24%2FITOP";
									?>
										<span id="b24network-informer-events" class="header-informers header-informer-network" onclick="window.open('<?=$networkLink?>','_blank');"></span>
									<?endif?>
								</span>
								<script type="text/javascript">function bxImBarRedraw(){}</script>
							<?
							}
							else
							{
								$APPLICATION->IncludeComponent("bitrix:im.messenger", "", Array(
									"RECENT" => "Y",
									"PATH_TO_SONET_EXTMAIL" => SITE_DIR."company/personal/mail/"
								), false, Array("HIDE_ICONS" => "Y"));
							}
						} ?>
					</div>

					<?$APPLICATION->IncludeComponent("bitrix:search.title", ".default", Array(
						"NUM_CATEGORIES" => "5",
						"TOP_COUNT" => "5",
						"CHECK_DATES" => "N",
						"SHOW_OTHERS" => "Y",
						"PAGE" => "#SITE_DIR#search/index.php",
						"CATEGORY_0_TITLE" => GetMessage("BITRIX24_SEARCH_EMPLOYEE"),
						"CATEGORY_0" => array(
							0 => "intranet",
						),
						"CATEGORY_1_TITLE" => GetMessage("BITRIX24_SEARCH_DOCUMENT"),
						"CATEGORY_1" => array(
							0 => "iblock_library",
						),
						"CATEGORY_1_iblock_library" => array(
							0 => "all",
						),
						"CATEGORY_2_TITLE" => GetMessage("BITRIX24_SEARCH_GROUP"),
						"CATEGORY_2" => array(
							0 => "socialnetwork",
						),
						"CATEGORY_2_socialnetwork" => array(
							0 => "all",
						),
						"CATEGORY_3_TITLE" => GetMessage("BITRIX24_SEARCH_MICROBLOG"),
						"CATEGORY_3" => array(
							0 => "microblog", 1 => "blog",
						),
						"CATEGORY_4_TITLE" => "CRM",
						"CATEGORY_4" => array(
							0 => "crm",
						),
						"CATEGORY_OTHERS_TITLE" => GetMessage("BITRIX24_SEARCH_OTHER"),
						"SHOW_INPUT" => "N",
						"INPUT_ID" => "search-textbox-input",
						"CONTAINER_ID" => "search",
						"USE_LANGUAGE_GUESS" => (LANGUAGE_ID == "ru") ? "Y" : "N"
						),
						false
					);?>

					<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array(
						"PATH_TO_SONET_PROFILE" => $profile_link."/user/#user_id#/",
						"PATH_TO_SONET_PROFILE_EDIT" => $profile_link."/user/#user_id#/edit/",
						"PATH_TO_SONET_EXTMAIL_SETUP" => $profile_link."/mail/?page=home",
						"PATH_TO_SONET_EXTMAIL_MANAGE" => $profile_link."/mail/?page=manage"
						),
						false
					);?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="bx-layout-cont">
			<table class="bx-layout-inner-table">
				<colgroup>
					<col class="bx-layout-inner-left" />
					<col class="bx-layout-inner-center" />
				</colgroup>
				<tr class="bx-layout-inner-top-row">
					<td class="bx-layout-inner-left">
                    <div id="mySidenav" class="sidenav">
                    <div class="sidenav_inner">
						<div id="menu">
							<?
							if (!(
								CModule::IncludeModule('extranet')
								&& SITE_ID === CExtranet::GetExtranetSiteID()
							))
							{
								$APPLICATION->IncludeComponent(
									"bitrix:socialnetwork.group.iframe.popup",
									".default",
									array(
										"PATH_TO_GROUP" => SITE_DIR."workgroups/group/#group_id#/",
										"PATH_TO_GROUP_CREATE" => SITE_DIR."company/personal/user/".$USER->GetID()."/groups/create/",
										"IFRAME_POPUP_VAR_NAME" => "groupCreatePopup",
										"ON_GROUP_ADDED" => "BX.DoNothing",
										"ON_GROUP_CHANGED" => "BX.DoNothing",
										"ON_GROUP_DELETED" => "BX.DoNothing"
									),
									null,
									array("HIDE_ICONS" => "Y")
								);

								$popupName = "create-group-popup";
								$APPLICATION->IncludeComponent(
									"bitrix:socialnetwork.group_create.popup",
									".default",
									array(
										"NAME" => $popupName,
										"PATH_TO_GROUP_EDIT" => SITE_DIR."company/personal/user/".$USER->GetID()."/groups/create/",
									),
									null,
									array("HIDE_ICONS" => "Y")
								);
							}
							?>

							<?if (
								$USER->IsAuthorized()
								&& (
									CBXFeatures::IsFeatureEnabled('Calendar')
									|| CBXFeatures::IsFeatureEnabled('Workgroups')
									|| CBXFeatures::IsFeatureEnabled('PersonalFiles')
									|| CBXFeatures::IsFeatureEnabled('PersonalPhoto')
								)
							):?>
							<div class="menu-create-but" onclick="BX.addClass(this, 'menu-create-but-active');BX.PopupMenu.show('create-menu', this, [
								<?if((CModule::IncludeModule('bitrix24') && CBitrix24::isInvitingUsersAllowed() || !IsModuleInstalled("bitrix24") && $USER->CanDoOperation('edit_all_users'))&& CModule::IncludeModule('intranet')):?>
								{ text : '<?=GetMessage("BITRIX24_INVITE")?>', className : 'invite-employee', onclick : function() { this.popupWindow.close(); <?=CIntranetInviteDialog::ShowInviteDialogLink()?>} },
								<?endif?>
								<?if(CBXFeatures::IsFeatureEnabled('Tasks') && CModule::IncludeModule('socialnetwork')):?>
								<?
								if(IsModuleInstalled('extranet') && SITE_ID == COption::GetOptionString("extranet", "extranet_site"))
								{
									$urlPrefix = '/extranet/contacts/personal';
								}
								else
								{
									$urlPrefix = SITE_DIR.'company/personal';
								}
								?>
								{ text : '<?=GetMessage("BITRIX24_TASK_CREATE")?>', className : 'create-task', href: '<?=$urlPrefix?>/user/<?=$USER->GetID()?>/tasks/task/edit/0/'},
								<?endif?>
								<?if (!(CModule::IncludeModule('extranet') && SITE_ID === CExtranet::GetExtranetSiteID())):?>
									<?if (CBXFeatures::IsFeatureEnabled('Calendar')):?>
								{ text : '<?=GetMessage("BITRIX24_EVENT_CREATE")?>', className : 'create-event', href : '<?=SITE_DIR?>company/personal/user/<?=$USER->GetID()?>/calendar/?EVENT_ID=NEW'},
									<?endif?>
								{ text : '<?=GetMessage("BITRIX24_BLOG_CREATE")?>', className : 'create-write-blog', href : '<?=SITE_DIR?>company/personal/user/<?=$USER->GetID()?>/blog/edit/new/'},
									<?if (CBXFeatures::IsFeatureEnabled('Workgroups') && CModule::IncludeModule('socialnetwork') && (CSocNetUser::IsCurrentUserModuleAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork", false, "Y", "Y", array(SITE_ID, false)) >= "K")):?>
								{ text : '<?=GetMessage("BITRIX24_GROUP_CREATE")?>', className : 'create-group', onclick : function() {this.popupWindow.close(); if (BX.SGCP) { BX.SGCP.ShowForm('create', '<?=$popupName?>', event); } else { return false; } } },
									<?endif?>
									<?if (CBXFeatures::IsFeatureEnabled('PersonalFiles')):
										$newFileLink = $isDiskEnabled ? SITE_DIR.'company/personal/user/'.$USER->GetID().'/blog/edit/new/?POST_MESSAGE=&changePostFormTab=file' : SITE_DIR."company/personal/user/".$USER->GetID()."/files/lib/?file_upload=Y";
									?>
								{ text : '<?=GetMessage("BITRIX24_FILE_CREATE")?>', className : 'create-download-files', href : '<?=$newFileLink?>' },
									<?endif?>
									<?if (CBXFeatures::IsFeatureEnabled('PersonalPhoto')):?>
								{ text : '<?=GetMessage("BITRIX24_PHOTO_CREATE")?>', className : 'create-download-photo', href : '<?=SITE_DIR?>company/personal/user/<?=$USER->GetID()?>/photo/photo/0/action/upload/'}
									<?endif?>
								<?else:?>
								{ text : '<?=GetMessage("BITRIX24_BLOG_CREATE")?>', className : 'create-write-blog', href : '<?=SITE_DIR?>contacts/personal/user/<?=$USER->GetID()?>/blog/edit/new/'},
									<?if (CBXFeatures::IsFeatureEnabled('PersonalFiles')):
										$newFileLink = $isDiskEnabled ? SITE_DIR.'contacts/personal/user/'.$USER->GetID().'/blog/edit/new/?POST_MESSAGE=&changePostFormTab=file' : SITE_DIR."contacts/personal/user/".$USER->GetID()."/files/lib/?file_upload=Y";
									?>
								{ text : '<?=GetMessage("BITRIX24_FILE_CREATE")?>', className : 'create-download-files', href : '<?=$newFileLink?>' },
									<?endif?>
								<?endif;?>
								],
								{
									offsetLeft: 47,
									offsetTop: 10,
									angle : true,

									events : {
										onPopupClose : function(popupWindow)
										{
											BX.removeClass(this.bindElement, 'menu-create-but-active');
										}
									}
								})"><?=GetMessage("BITRIX24_CREATE")?></div>
								<?endif;?>

								<?if (IsModuleInstalled("bitrix24")) :?>
									<?$APPLICATION->IncludeComponent("bitrix:menu", "vertical_multilevel", array(
											"ROOT_MENU_TYPE" => "superleft",
											"MENU_CACHE_TYPE" => "Y",
											"MENU_CACHE_TIME" => "604800",
											"MENU_CACHE_USE_GROUPS" => "N",
											"MENU_CACHE_USE_USERS" => "Y",
											"CACHE_SELECTED_ITEMS" => "N",
											"MENU_CACHE_GET_VARS" => array(),
											"MAX_LEVEL" => "1",
											"CHILD_MENU_TYPE" => "superleft",
											"USE_EXT" => "Y",
											"DELAY" => "N",
											"ALLOW_MULTI_SELECT" => "N"
										),
										false
									);?>
								<?else:?>
									<?$APPLICATION->IncludeComponent("bitrix:menu", "vertical_multilevel", array(
											"ROOT_MENU_TYPE" => "top",
											"MENU_CACHE_TYPE" => "Y",
											"MENU_CACHE_TIME" => "604800",
											"MENU_CACHE_USE_GROUPS" => "N",
											"MENU_CACHE_USE_USERS" => "Y",
											"CACHE_SELECTED_ITEMS" => "N",
											"MENU_CACHE_GET_VARS" => array(),
											"MAX_LEVEL" => "2",
											"CHILD_MENU_TYPE" => "left",
											"USE_EXT" => "Y",
											"DELAY" => "N",
											"ALLOW_MULTI_SELECT" => "N"
										),
										false
									);?>
								<?endif;?>
						</div>
                  </div>
            </div>
					</td>
					<td class="bx-layout-inner-center" id="content-table">
					<?
					if ($isCompositeMode && !$isIndexPage)
					{
						$dynamicArea = new \Bitrix\Main\Page\FrameStatic("workarea");
						$dynamicArea->setAssetMode(\Bitrix\Main\Page\AssetMode::STANDARD);
						$dynamicArea->setContainerId("content-table");
						$dynamicArea->setStub('
							<table class="bx-layout-inner-inner-table">
								<colgroup>
									<col class="bx-layout-inner-inner-cont">
								</colgroup>
								<tr class="bx-layout-inner-inner-top-row">
									<td class="bx-layout-inner-inner-cont">
										<div class="pagetitle-wrap"></div>
									</td>
								</tr>
								<tr>
									<td class="bx-layout-inner-inner-cont">
										<div id="workarea">
											<div id="workarea-content">
												<div class="workarea-content-paddings">
													<div class="b24-loader" id="b24-loader"><div class="b24-loader-curtain"></div></div>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</table>
							<script>B24.showLoading();</script>'
						);
						$dynamicArea->startDynamicArea();
					}

					if ($isIndexPage)
					{
						$APPLICATION->SetPageProperty("BodyClass", "start-page no-paddings");
					}

					?>
						<table class="bx-layout-inner-inner-table <?$APPLICATION->ShowProperty("BodyClass");?>">
							<colgroup>
								<col class="bx-layout-inner-inner-cont">
							</colgroup>
							<tr class="bx-layout-inner-inner-top-row">
								<td class="bx-layout-inner-inner-cont">
									<?$APPLICATION->ShowViewContent("above_pagetitle")?>
									<div class="pagetitle-wrap">
										<div class="pagetitle-menu" id="pagetitle-menu"><?$APPLICATION->ShowViewContent("pagetitle")?></div>
										<h1 class="pagetitle" id="pagetitle"><span class="pagetitle-inner"><?$APPLICATION->ShowTitle(false);?></span></h1>
										<div class="pagetitle-content-topEnd">
											<div class="pagetitle-content-topEnd-corn"></div>
										</div>
									</div>
									<?$APPLICATION->ShowViewContent("below_pagetitle")?>
								</td>
							</tr>
							<tr>
								<td class="bx-layout-inner-inner-cont">

									<div id="workarea">
										<?if($APPLICATION->GetProperty("HIDE_SIDEBAR", "N") != "Y"):
											?><div id="sidebar"><?
											if (IsModuleInstalled("bitrix24")):
												$GLOBALS['INTRANET_TOOLBAR']->Disable();
											else:
												$GLOBALS['INTRANET_TOOLBAR']->Enable();
												$GLOBALS['INTRANET_TOOLBAR']->Show();
											endif;

											$APPLICATION->ShowViewContent("sidebar");
											$APPLICATION->ShowViewContent("sidebar_tools_1");
											$APPLICATION->ShowViewContent("sidebar_tools_2");
											?></div>
										<?endif?>
										<div id="workarea-content">
											<div class="workarea-content-paddings">
											<?$APPLICATION->ShowViewContent("topblock")?>
											<?CPageOption::SetOptionString("main.interface", "use_themes", "N"); //For grids?>

<script>

function toggleNavBar(){
	var navBar = document.getElementById("mySidenav").style.width;
	if (navBar == '17%')
	{
		closeNav();
	}
	else
	{
		openNav();
		//mySidenav.width = '18';
	}
}

function openNav() {
    document.getElementById("mySidenav").style.width = "17%";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0%";
}

</script>