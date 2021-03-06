<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_domain.php 26548 2011-12-15 02:46:34Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = in_array($operation, array('global', 'app', 'root')) ? $operation : 'global';
$current = array($operation => 1);

shownav('global', 'setting_domain');
showsubmenu('setting_domain', array(
	array('setting_domain_base', 'domain', $current['global']),
	array('setting_domain_app', 'domain&operation=app', $current['app']),
	array('setting_domain_root', 'domain&operation=root', $current['root']),
));
$navs = $_G['setting']['navs'];
if($operation == 'app') {

	if(!submitcheck('submit')) {
		$appkeyarr = array(
			'portal' => $navs[1]['navname'],
			'forum' => $navs[2]['navname'],
			'group' => $navs[3]['navname'],
			'home' => $navs[4]['navname'],
			'mobile' => $lang['mobile'],
			'default' => $lang['default']
		);
		showtips('setting_domain_app_tips');

		showformheader('domain&operation=app');
		showtableheader();
		showsubtitle(array('name', 'setting_domain_app_domain'));
		$app = array();
		foreach($appkeyarr as $key => $desc) {
			showtablerow('', array('class="td25"', ''), array(
					$desc,
					"<input type=\"text\" class=\"txt\" style=\"width:50%;\" name=\"appnew[$key]\" value=\"".$_G['setting']['domain']['app'][$key]."\">".($key == 'mobile' ? cplang('setting_domain_app_mobile_tips') : '')
				));
		}
		showsubmit('submit');
		showtablefooter();
		showformfooter();
	} else {
		$olddomain = $_G['setting']['domain']['app'];
		$_G['setting']['domain']['app'] = array();
		foreach($_GET['appnew'] as $appkey => $domain) {
			if(preg_match('/^((http|https|ftp):\/\/|\.)|(\/|\.)$/i', $domain)) {
				cpmsg('setting_domain_http_error', '', 'error');
			}
			if(!empty($domain) && in_array($domain, $_G['setting']['domain']['app'])) {
				cpmsg('setting_domain_repeat_error', '', 'error');
			}
			$_G['setting']['domain']['app'][$appkey] = $domain;
		}

		if($_GET['appnew']['mobile'] != $olddomain['mobile']) {
			C::t('common_nav')->update_by_identifier('mobile', array('url' => (!$_GET['appnew']['mobile'] ? 'forum.php?mobile=yes' : 'http://'.$_GET['appnew']['mobile'])));
		}

		C::t('common_setting')->update('domain',$_G['setting']['domain']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=domain&operation=app', 'succeed');
	}

} elseif($operation == 'root') {

	$roottype = array(
		'home' => $lang['domain_home'],
		'group' => $navs[3]['navname'],
		'forum' => $lang['domain_forum'],
		'topic' => $lang['domain_topic'],
		'channel' => $lang['channel'],
	);
	if(!submitcheck('submit')) {
		showtips('setting_domain_root_tips');
		showformheader('domain&operation=root');
		showtableheader();
		showsubtitle(array('name', 'setting_domain_app_domain'));
		foreach($roottype as $type => $desc) {
			$domainroot = $_G['setting']['domain']['root'][$type];
			showtablerow('', array('class="td25"', ''), array(
					$desc,
					"<input type=\"text\" class=\"txt\" style=\"width:50%;\" name=\"domainnew[$type]\" value=\"$domainroot\">"
				));
		}
		showsubmit('submit');
		showtablefooter();
		showformfooter();
	} else {
		$oldroot = $_G['setting']['domain']['root'];
		$_G['setting']['domain']['root'] = array();
		foreach($_GET['domainnew'] as $idtype => $domain) {
			if(preg_match('/^((http|https|ftp):\/\/|\.)|(\/|\.)$/i', $domain)) {
				cpmsg('setting_domain_http_error', '', 'error');
			}
			if($_G['setting']['domain']['root'][$idtype] != $domain) {
				$updatetype = $idtype == 'forum' ? array('forum', 'channel') : $idtype;
				C::t('common_domain')->update_by_idtype($updatetype, array('domainroot' => $domain));
			}
			$_G['setting']['domain']['root'][$idtype] = $domain;

		}
		C::t('common_setting')->update('domain', $_G['setting']['domain']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=domain&operation=root', 'succeed');
	}
} else {
	if(!submitcheck('domainsubmit')) {

		showtips('setting_domain_base_tips');
		showformheader("domain");
		showtableheader();
		showsetting('setting_domain_allow_space', 'settingnew[allowspacedomain]', $_G['setting']['allowspacedomain'], 'radio');
		showsetting('setting_domain_allow_group', 'settingnew[allowgroupdomain]', $_G['setting']['allowgroupdomain'], 'radio');
		showsetting('setting_domain_hold_domain', 'settingnew[holddomain]', $_G['setting']['holddomain'], 'text');
		showsubmit('domainsubmit');
		showtablefooter();
		showformfooter();
	} else {

		$settings = $_GET['settingnew'];
		$settings['allowspacedomain'] = (float)$settings['allowspacedomain'];
		$settings['allowgroupdomain'] = (float)$settings['allowgroupdomain'];
		if($settings) {
			C::t('common_setting')->update_batch($settings);
			updatecache('setting');

		}
		cpmsg('setting_update_succeed', 'action=domain', 'succeed');
	}
}
?>