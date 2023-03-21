<!-- BEGIN: USERTRANSLATE -->
	{FILE "{PHP.cfg.themes_dir}/{PHP.usr.theme}/warnings.tpl"}

	<!-- BEGIN: TRANSLATE -->
	{UPT_AJAX_BEGIN}

		<!-- BEGIN: ROW -->
		{UPT_DATE|cot_date('d.m.Y H:i:s', $this)}: <a href="{UPT_PAG_CATURL}">{UPT_PAG_CATTITLE}</a> | <a href="{UPT_URL}">{UPT_TITLE}</a> -> <a href="{UPT_PAG_URL}">{UPT_PAG_SHORTTITLE}</a><br />
		<!-- END: ROW -->
		<div class="pagnav">{UPT_PAGENAV_PREV} {UPT_PAGENAV} {UPT_PAGENAV_NEXT}</div>
		{PHP.L.Total} : {UPT_TOTALITEMS}, {PHP.L.Onpage} : {UPT_COUNT_ON_PAGE}

	{UPT_AJAX_END}
	<!-- END: TRANSLATE -->

	<!-- BEGIN: NONE -->
	{PHP.L.plu_none_usr_pag_translate}
	<!-- END: NONE -->
<!-- END: USERTRANSLATE -->