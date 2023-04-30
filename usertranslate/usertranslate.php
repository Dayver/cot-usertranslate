<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=users.details.tags,ajax
Tags=users.details.tpl:{USERS_DETAILS_USERTRANSLATE}
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if (cot_module_active('page') && cot_plugin_active('i18n'))
{
	require_once cot_langfile('usertranslate');
	require_once cot_incfile('usertranslate', 'plug', 'resources');
	require_once cot_incfile('page', 'module');
	require_once cot_incfile('i18n', 'plug');

	$work_now = !COT_AJAX ? $t->hasTag('USERS_DETAILS_USERTRANSLATE') : false;
	if ($work_now || COT_AJAX)
	{
		$usertranslate = new XTemplate(cot_tplfile('usertranslate', 'plug'));

		$usr_tr_id = null;
		if (isset($urr['user_id']) && !empty($urr['user_id']))
		{
			$usr_tr_id = (int) $urr['user_id'];
		}
		elseif (COT_AJAX)
		{
			$usr_tr_id = cot_import('utid', 'G', 'INT');
			if ($usr_tr_id)
			{
				$sql = Cot::$db->query('SELECT user_id FROM '.Cot::$db->users." WHERE user_id='$usr_tr_id' LIMIT 1");
				if (!$sql->rowCount())
				{
					cot_error('plu_user_not_found');
				}
			}
			else
			{
				cot_error('plu_user_id_empty');
			}
		}

		if (!empty($usr_tr_id))
		{
			$pts = cot_import('pts', 'G', 'ALP');
			if (empty($pts) || !Cot::$db->fieldExists(Cot::$db->i18n_pages, "ipage_".$pts))
			{
				$pts = 'date';
			}

			$pto = cot_import('pto', 'G', 'ALP');
			$pto = empty($pto) ? 'DESC' : $pto;

			$ptp = cot_import('ptp', 'G', 'INT');
			$ptp = empty($ptp) ? 0 : (int) $ptp;

			list($pgt, $ptp, $ptp_url) = cot_import_pagenav('ptp', Cot::$cfg['plugin']['usertranslate']['countonpage']);

			$utQuery = ' FROM ' . Cot::$db->i18n_pages . ' AS t ' .
				'LEFT JOIN ' . Cot::$db->pages . ' AS p ON t.ipage_id = p.page_id  ' .
				"WHERE p.page_state = 0 AND p.page_cat <> 'system' AND t.ipage_translatorid = :userId";
			$utQueryParams = ['userId' => $usr_tr_id];
			$totalitems = Cot::$db->query('SELECT COUNT(*) ' . $utQuery, $utQueryParams)->fetchColumn();

			$pagination_params = ['m' => 'details', 'utid' => $usr_tr_id, 'pts' => $pts, 'pto' => $pto];
			$pagenav = cot_pagenav(
				'users',
				$pagination_params,
				$ptp,
				$totalitems,
				Cot::$cfg['plugin']['usertranslate']['countonpage'],
				'ptp',
				'',
				Cot::$cfg['plugin']['usertranslate']['ajax'],
				"usr_translated_pag",
				'plug',
				['r' => 'usertranslate', 'utid' => $usr_tr_id, 'pts' => $pts, 'pto' => $pto]
			);

			$up_ajax_begin = $up_ajax_end = '';
			if (Cot::$cfg['plugin']['usertranslate']['ajax'] && !COT_AJAX)
			{
				$up_ajax_begin = "<div id='usr_translated_pag'>";
				$up_ajax_end = "</div>";
			}
			$stlcat = 'normal';
			$stldat = 'normal';
			if ($pts == 'cat')
			{
				$stlcat = 'bold';
			}
			elseif ($pts == 'date')
			{
				$stldat = 'bold';
			}

			$sqlusertranslate = Cot::$db->query(
				'SELECT t.*, p.* ' . $utQuery . " ORDER BY t.ipage_$pts $pto " .
					'LIMIT ' . (int) Cot::$cfg['plugin']['usertranslate']['countonpage'] . " OFFSET $ptp",
				$utQueryParams
			);

			if ($sqlusertranslate->rowCount() == 0)
			{
				//cot_message('plu_none_usr_pag_translate', 'warning');
				$usertranslate->parse("USERTRANSLATE.NONE");
			}
			else
			{
				$jj = 0;
				while ($row = $sqlusertranslate->fetch())
				{
					if (cot_auth('page', $row['page_cat'], 'R'))
					{
						$jj++;
						$usertranslate->assign(cot_generate_pagetags($row, 'UPT_PAG_'));
						$usertranslate->assign([
							'UPT_ID' => $row['ipage_id'],
							'UPT_LOCALE' => $row['ipage_locale'],
							'UPT_TRANSLATORNAME' => $row['ipage_translatorname'],
							'UPT_DATE' => $row['ipage_date'],
							'UPT_TITLE' => $row['ipage_title'],
							'UPT_DESC' => $row['ipage_desc'],
							'UPT_TEXT' => $row['ipage_text'],

							'UPT_URL' => (empty($row['page_alias'])) ?
											cot_url('page', 'c='.$row['page_cat'].'&id='.$row['page_id'].'&l='.$row['ipage_locale']) :
											cot_url('page', 'c='.$row['page_cat'].'&al='.$row['page_alias'].'&l='.$row['ipage_locale']),
							'UPT_CATURL' => cot_url('page', "c=".$row['page_cat'].'&l='.$row['ipage_locale']),

							'UPT_ODDEVEN' => cot_build_oddeven($jj),
							'UPT_NUM' => $jj
						]);
						$usertranslate->parse("USERTRANSLATE.TRANSLATE.ROW");
					}
				}

				$setDat = ' style="font-weight:'.$stldat.'"';
				$setCat = ' style="font-weight:'.$stlcat.'"';
				if (Cot::$cfg['plugin']['usertranslate']['ajax'])
				{
					$setDat = " OnClick=\"return ajaxSend({url: '" . cot_url('plug', ['r' => 'usertranslate', 'id' => $usr_tr_id, 'ps' => 'date']) .
						"', data: '&dp=" . $ptp_url . "', divId: 'usr_translated_pag', errMsg: '" . Cot::$L['plu_msg500'] . "'});\" " .
						'style="font-weight:' . $stldat . '"';

					$setCat = " OnClick=\"return ajaxSend({url: '" . cot_url('plug', ['r' => 'usertranslate', 'id' => $usr_tr_id, 'ps' => 'cat']) .
						"', data: '&dp=" . $ptp_url . "', divId: 'usr_translated_pag', errMsg: '" . Cot::$L['plu_msg500'] . "'});\" " .
						'style="font-weight:' . $stlcat . '"';
				}

				$usertranslate->assign([
					"UPT_AJAX_BEGIN" => $up_ajax_begin,
					"UPT_AJAX_END" => $up_ajax_end,
					"UPT_PAGENAV" => $pagenav['main'],
					"UPT_PAGENAV_PREV" => $pagenav['prev'],
					"UPT_PAGENAV_NEXT" => $pagenav['next'],
					"UPT_TOTALITEMS" => $totalitems,
					"UPT_COUNT_ON_PAGE" => $jj,
					"UPT_USER_ID" => $usr_tr_id,
					"UPT_PAGE_ID" => $ptp,
					"UPT_ORDER_SET_DAT" => $setDat,
					"UPT_ORDER_SET_CAT" => $setCat,
				]);
				$usertranslate->parse("USERTRANSLATE.TRANSLATE");
			}
		}
		else
		{
			cot_error('plu_user_id_empty');
		}

		cot_display_messages($usertranslate);

		$usertranslate->parse("USERTRANSLATE");
		$user_pags_tranlate = $usertranslate->text("USERTRANSLATE");

		if (COT_AJAX)
		{
			cot_sendheaders();
			echo $user_pags_tranlate;
			exit();
		}
		else
		{
			$t->assign("USERS_DETAILS_USERTRANSLATE", $user_pags_tranlate);
		}
	}
}