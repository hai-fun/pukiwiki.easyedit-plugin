<?php

// guiedit.inc.php, v 1.63.2 2009/04/20 23:06:00 upk Exp $
// easyedit.inc.php,v 1.09 2021/01/29 22:48:00 K Exp $
// $Id: easyedit.inc.php,v 1.10 2021/12/30 00:00:00 haifun Exp $
/** 
* @link http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/easyedit.inc.php
* @author K
* @license http://www.gnu.org/licenses/gpl.ja.html GNU General Public License Version 2 or later (GPL)
*/

// PHP8対応、細かいところを修正 byはいふん

// define('PLUGIN_EASYEDIT_SKIN_FILE', SKIN_DIR . "xxx.css");
define('PLUGIN_EASYEDIT_SKIN_FILE', "./skin/pukiwiki.css");

define('EASYEDIT_LIB_PATH', './easyedit/');
define('PREG_EASYEDIT_LIB_PATH', '.\/easyedit\/');

define('PLUGIN_EASYEDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');

function plugin_easyedit_action()
{
	global $vars, $_title_edit;
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	// Create initial pages
	plugin_easyedit_setup_initial_pages();

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page, true, true);
	check_readable($page, true, true);

	if (isset($vars['preview'])) {
		return plugin_easyedit_preview($vars['msg']);
	} else if (isset($vars['template'])) {
		return plugin_easyedit_preview_with_template();
	} else if (isset($vars['write'])) {
		return plugin_easyedit_write();
	} else if (isset($vars['cancel'])) {
		return plugin_easyedit_cancel();
	}
	$postdata = @join('', get_source($page));
	if ($postdata === '') $postdata = auto_template($page);
	$postdata = remove_author_info($postdata);
	return array('msg'=>$_title_edit, 'body'=>easyedit_form($page, $postdata));
}

/**
 * Preview with template
 */
function plugin_easyedit_preview_with_template()
{
	global $vars;
	$msg = '';
	$page = isset($vars['page']) ? $vars['page'] : '';
	// Loading template
	$template_page;
	if (isset($vars['template_page']) && is_page($template_page = $vars['template_page'])) {
		if (is_page_readable($template_page)) {
			$msg = remove_author_info(get_source($vars['template_page'], TRUE, TRUE));
			// Cut fixed anchors
			$msg = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $msg);
		}
	}
	return plugin_easyedit_preview($msg);
}


/**
 * Preview
 *
 * @param msg preview target
 */
 /*
function plugin_easyedit_inline_replace($msg){
    $msg = str_replace("<strong>",'\'\'',$msg);
    $msg = str_replace("</strong>",'\'\'',$msg);
    $msg = str_replace("<em>",'\'\'\'',$msg);
    $msg = str_replace("</em>",'\'\'\'',$msg);
    $msg = str_replace("<s>",'%%',$msg);
    $msg = str_replace("</s>",'%%',$msg);
    $msg = str_replace("<u>",'%%%',$msg);
    $msg = str_replace("</u>",'%%%',$msg);
    $msg = str_replace("<br />",'&br;',$msg);
    if(exist_plugin("sup")){
            // 上付き文字
        $msg = preg_replace("/<sup>(.*?)<\/sup>/", "&sup{" . "$1" . "};", $msg);
    }else{
        $msg = preg_replace("/<sup>(.*?)<\/sup>/", "SUP{" . "$1" . "}", $msg);
    }
	if(exist_plugin("sub")){
        // 下付き文字・添え字
        $msg = preg_replace("/<sub>(.*?)<\/sub>/", "&sub{"."$1"."};", $msg);
    }else{
        $msg = preg_replace("/<sub>(.*?)<\/sub>/", "SUB{"."$1"."}", $msg);
        
    }
    //$msg = str_replace("<blockquote>",'>',$msg);
    //$msg = str_replace("</blockquote>",'<',$msg);
	if (preg_match_all("/<span\sstyle=\"color:(.*?);\"><span\sstyle=\"background-color:(.*?);\">(.*?)<\/span><\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&color(".$value[1].",".$value[2]."){".$value[3]."};",$msg);
        }
    }
	if (preg_match_all("/<span\sstyle=\"background-color:(.*?);\"><span\sstyle=\"color:(.*?);\">(.*?)<\/span><\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&color(".$value[2].",".$value[1]."){".$value[3]."};",$msg);
        }
    }
	if (preg_match_all("/<span\sstyle=\"color:(.*?);\">(.*?)<\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&color(".$value[1]."){".$value[2]."};",$msg);
        }
    }
	if (preg_match_all("/<span\sstyle=\"background-color:(.*?);\">(.*?)<\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&color(,".$value[1]."){".$value[2]."};",$msg);
        }
    }
	if (preg_match_all("/<span\sstyle=\"font-size:(.*?)px;\">(.*?)<\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&size(".$value[1]."){".$value[2]."};",$msg);
        }
    }
    if (preg_match_all("/<a\shref=\"(.*?)\">(.*?)<\/a>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            if ($value[1] == $value[2]){
                $msg = str_replace($value[0],"[[".$value[1]."]]",$msg);
            }else{
                $msg = str_replace($value[0],"[[".$value[2].">".$value[1]."]]",$msg);
            }
        }
    }
    / *
	if (preg_match_all("/<img\salt=\"\"\ssrc=\"(.+?)\"\sstyle=\"height:(.+?);\swidth:(.+?)\"\s\/>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&ref(".$value[1].");",$msg);
        }
    }
   	if (preg_match_all("/<img\salt=\"(.+?)\"\ssrc=\"(.+?)\"\sstyle=\"height:(.+?);\swidth:(.+?)\"\s\/>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"&ref(".$value[2].");",$msg);
        }
    }
    * /
    if (preg_match_all("/<span\sclass=\"note\"><img\salt=\"Note\"\ssrc=\"".PREG_EASYEDIT_LIB_PATH."plugins\/note\/icons\/note2.png\"\s\/>(.+?)<\/span>/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"((".$value[1]."))",$msg);
        }
    }
    $msg = str_replace('<span class="comment" style="color:#355e3b"><img src="'.EASYEDIT_LIB_PATH.'plugins/comment/icons/comment.png" />',"//",$msg);
    $msg = str_replace('</span>',"",$msg);
    $msg  = str_replace("&gt;",">",$msg);
    $msg  = str_replace("&lt;","<",$msg);
    $msg = plugin_easyedit_img2emoji($msg);
    $msg  = str_replace("&amp;","&",$msg);
    return $msg;
}
*/

/*
function plugin_easyedit_img2emoji($msg){
    $msg = preg_replace("/<img alt=\"(.*)\" src=\"(.*)\" type=\"emoji\"(.*) \/>/u","&$1;",$msg);
    return $msg;
}
*/

//絵文字用
/*
function plugin_easyedit_emoji2img($msg){
    global $easyedit_emoji_list;
    foreach ($easyedit_emoji_list as $value){
        $value_array = explode(',',$value);
        if (isset($value_array[1])){
            $emojitext = true;
        }else{
            $value_array[1] = $value;
        }
        if ($emojitext){
            $msg = preg_replace("/".preg_quote($value_array[1])."/u",'<img alt="'.$value_array[0].'" src="'.IMAGE_DIR.'face/'.$value_array[0].'.png" type="emoji" />',$msg);
        }else{
            $msg = preg_replace("/&#038;".preg_quote($value_array[1]).";/u",'<img alt="'.$value_array[0].'" src="'.IMAGE_DIR.'face/'.$value_array[0].'.png" type="emoji" />',$msg);
        }
    }
    return $msg;
}
function plugin_easyedit_inline_replace2($msg){
	if (preg_match_all("/\'\'\'(.+?)\'\'\'/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace("'''".$value[1]."'''","<em>".$value[1]."</em>",$msg);
        }
    }
	if (preg_match_all("/\'\'(.+?)\'\'/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace("''".$value[1]."''","<strong>".$value[1]."</strong>",$msg);
        }
    }
	if (preg_match_all("/\%\%\%(.+?)\%\%\%/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace("%%%".$value[1]."%%%","<u>".$value[1]."</u>",$msg);
        }
    }
	if (preg_match_all("/\%\%(.+?)\%\%/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace("%%".$value[1]."%%","<s>".$value[1]."</s>",$msg);
        }
    }
    $msg = str_replace("&br;",'<br />',$msg);
    //$msg = preg_replace("^#br$",'<br />',$msg);
    / *
    $msg = preg_replace("/&gt;(.*)&lt;/us",'<blockquote><p class="quotation">$1</p></blockquote>',$msg);
    $msg = preg_replace("/&gt;(.*)\n/u",'<blockquote><p class="quotation">$1</p></blockquote>',$msg);* /
    if (preg_match_all("/COLOR\((.*?)\):(.*?)\n/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"color:".$value[1].";\">".$value[2]."</span>",$msg);
        }
    }
    if (preg_match_all("/SIZE\((.*?)\):(.*?)\n/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"font-size:".$value[1]."px;\">".$value[2]."</span>",$msg);
        }
    }
    if (preg_match_all("/&#038;size\((.*?)\)\{(.*?)\};/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"font-size:".$value[1]."px;\">".$value[2]."</span>",$msg);
        }
    }
	if (preg_match_all("/&#038;color\((.*?),(.*?)\)\{(.*?)\};/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"color:".$value[1].";\"><span style=\"background-color:".$value[2].";\">".$value[3]."</span></span>",$msg);
        }
    }
	if (preg_match_all("/&#038;color\(,(.*?)\)\{(.*?)\};/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"background-color:".$value[1].";\">".$value[2]."</span>",$msg);
        }
    }
	if (preg_match_all("/&#038;color\((.*?)\)\{(.*?)\};/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
            $msg = str_replace($value[0],"<span style=\"color:".$value[1].";\">".$value[2]."</span>",$msg);
        }
    }
    /*
	if (preg_match_all("/&#038;ref\((.+?)\);/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
        	if(is_url($value[1])){
	            $msg = str_replace($value[0],"<img alt=\"\" src=\"".$value[1]."\" style=\"height:auto; width:auto\" />",$msg);
	        }else{
	        	$msg = str_replace($value[0],"<img alt=\"添付ファイルは編集中、表示されません。\" src=\"".$value[1]."\" style=\"height:auto; width:auto\" />",$msg);
	        }
        }
    }
    if (preg_match_all("/&#035;ref\((.+?)\)/u", $msg, $match_msg,PREG_SET_ORDER)){
        foreach ($match_msg as $value) {
        	if(is_url($value[1])){
	            $msg = str_replace($value[0],"<img alt=\"\" src=\"".$value[1]."\" style=\"height:auto; width:auto\" />",$msg);
	        }else{
	        	$msg = str_replace($value[0],"<img alt=\"添付ファイルは編集中、表示されません。\" src=\"".$value[1]."\" style=\"height:auto; width:auto\" />",$msg);
	        }
	    }
    }
    * /
    $msg = plugin_easyedit_emoji2img($msg);
    return $msg;
}
*/

function plugin_easyedit_preview($msg)
{
	global $vars;
	global $_title_preview, $_msg_preview, $_msg_preview_delete;
	
	$page = isset($vars['page']) ? $vars['page'] : '';
	
    //EasyEdit
    require_once(EASYEDIT_LIB_PATH . 'guiedit/htmlv2wiki.php');
    $msg = htmlv2wiki($msg);
    //$msg = plugin_easyedit_inline_replace($msg);
    //--------
	$msg = preg_replace(PLUGIN_EASYEDIT_FREEZE_REGEX, '', $msg);
	$postdata = $msg;

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = $_msg_preview . '<br />' . "\n";
	if ($postdata === '')
		$body .= '<strong>' . $_msg_preview_delete . '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	
	$body .= easyedit_form($page, $msg, $vars['digest'], FALSE);

	return array('msg'=>$_title_preview, 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
function plugin_easyedit_inline()
{
	static $usage = '&easyedit(pagename#anchor[[,noicon],nolabel])[{label}];';

	global $vars, $fixed_heading_anchor_edit;

	if (PKWK_READONLY) return ''; // Show nothing 

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);

	$page = array_shift($args);
	if ($page === NULL) $page = '';
	$_noicon = $_nolabel = FALSE;
	foreach($args as $arg){
		switch(strtolower($arg)){
		case ''       :                   break;
		case 'nolabel': $_nolabel = TRUE; break;
		case 'noicon' : $_noicon  = TRUE; break;
		default       : return $usage;
		}
	}

	// Separate a page-name and a fixed anchor
	list($s_page, $id, $editable) = anchor_explode($page, TRUE);

	// Default: This one
	if ($s_page == '') $s_page = isset($vars['page']) ? $vars['page'] : '';

	// $s_page fixed
	$isfreeze = is_freeze($s_page);
	$ispage   = is_page($s_page);

	// Paragraph edit enabled or not
	$short = htmlsc('Edit');
	if ($fixed_heading_anchor_edit && $editable && $ispage && ! $isfreeze) {
		// Paragraph editing
		$id    = rawurlencode($id);
		$title = htmlsc(sprintf('Edit %s', $page));
		$icon = '<img src="' . IMAGE_DIR . 'paraedit.png' .
			'" width="9" height="9" alt="' .
			$short . '" title="' . $title . '" /> ';
		$class = ' class="anchor_super"';
	} else {
		// Normal editing / unfreeze
		$id    = '';
		if ($isfreeze) {
			$title = 'Unfreeze %s';
			$icon  = 'unfreeze.png';
		} else {
			$title = 'EasyEdit %s';
			$icon  = 'edit.png';
		}
		$title = htmlsc(sprintf($title, $s_page));
		$icon = '<img src="' . IMAGE_DIR . $icon .
			'" width="20" height="20" alt="' .
			$short . '" title="' . $title . '" />';
		$class = '';
	}
	if ($_noicon) $icon = ''; // No more icon
	if ($_nolabel) {
		if (!$_noicon) {
			$s_label = '';     // No label with an icon
		} else {
			$s_label = $short; // Short label without an icon
		}
	} else {
		if ($s_label == '') $s_label = $title; // Rich label with an icon
	}

	// URL
	$script = get_base_uri();
	if ($isfreeze) {
		$url   = $script . '?cmd=unfreeze&amp;page=' . rawurlencode($s_page);
	} else {
		$s_id = ($id == '') ? '' : '&amp;id=' . $id;
		$url  = $script . '?cmd=easyedit&amp;page=' . rawurlencode($s_page) . $s_id;
	}
	$atag  = '<a' . $class . ' href="' . $url . '" title="' . $title . '">';
	static $atags = '</a>';

	if ($ispage) {
		// Normal edit link
		return $atag . $icon . $s_label . $atags;
	} else {
		// Dangling edit link
		return '<span class="noexists">' . $atag . $icon . $atags .
			$s_label . $atag . '?' . $atags . '</span>';
	}
}

// Write, add, or insert new comment
function plugin_easyedit_write()
{
	global $vars;
	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
	global $notimeupdate, $_msg_invalidpass, $do_update_diff_table;

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';
    //EasyEdit
    require_once(EASYEDIT_LIB_PATH . 'guiedit/htmlv2wiki.php');
    $vars['msg'] = htmlv2wiki($vars['msg']);
    //$vars['msg'] = plugin_easyedit_inline_replace($vars['msg']);

    //--------
	$vars['msg'] = preg_replace(PLUGIN_EASYEDIT_FREEZE_REGEX, '', $vars['msg']);
	$msg = & $vars['msg']; // Reference

	$retvars = array();

	// Collision Detection
	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);
	if ($digest !== $oldpagemd5) {
		$vars['digest'] = $oldpagemd5; // Reset

		$original = isset($vars['original']) ? $vars['original'] : '';
		$old_body = remove_author_info($oldpagesrc);
		list($postdata_input, $auto) = do_update_diff($old_body, $msg, $original);

		$retvars['msg' ] = $_title_collided;
		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided) . "\n";
		$retvars['body'] .= $do_update_diff_table;
		$retvars['body'] .= easyedit_form($page, $postdata_input, $oldpagemd5, FALSE);
		return $retvars;
	}

	// Action?
	if ($add) {
		// Add
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $msg . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $msg;
		}
	} else {
		// Edit or Remove
		$postdata = & $msg; // Reference
	}

	// NULL POSTING, OR removing existing page
	if ($postdata === '') {
		page_write($page, $postdata);
		$retvars['msg' ] = $_title_deleted;
		$retvars['body'] = str_replace('$1', htmlsc($page), $_title_deleted);
		return $retvars;
	}

	// $notimeupdate: Checkbox 'Do not change timestamp'
	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		$retvars['body'] .= easyedit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimeupdate != 0 && $notimestamp);
	pkwk_headers_sent();
	header('Location: ' . get_page_uri($page, PKWK_URI_ROOT));
	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_easyedit_cancel()
{
	global $vars;
	pkwk_headers_sent();
	header('Location: ' . get_page_uri($vars['page'], PKWK_URI_ROOT));
	exit;
}

/**
 * Setup initial pages
 */
function plugin_easyedit_setup_initial_pages()
{
	global $autoalias;

	// Related: Rename plugin
	if (exist_plugin('rename') && function_exists('plugin_rename_setup_initial_pages')) {
		plugin_rename_setup_initial_pages();
	}
	// AutoTicketLinkName page
	init_autoticketlink_def_page();
	// AutoAliasName page
	if ($autoalias) {
		init_autoalias_def_page();
	}
}
function easyedit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
{
	global $vars, $rows, $cols;
	global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
	global $_btn_template, $_btn_load, $load_template_func;
	global $notimeupdate;
	global $_msg_edit_cancel_confirm, $_msg_edit_unloadbefore_message;
	global $rule_page;
    //CKEditor & EasyEdit
    $EASYEDIT_LIB_PATH = EASYEDIT_LIB_PATH;
    $PLUGIN_EASYEDIT_SKIN_FILE = PLUGIN_EASYEDIT_SKIN_FILE;
    $ckeditor = <<<EOD
    <div id="editor"></div>
    <script>var guiedit_skin_dir = "{$PLUGIN_EASYEDIT_SKIN_FILE}";</script>
    <script src="{$EASYEDIT_LIB_PATH}ckeditor.js"></script>
    EOD;
    //--------
	$script = get_base_uri();
	if ($digest === FALSE) $digest = md5(join('', get_source($page)));
	$refer = $template = '';
	$addtag = $add_top = '';
	if(isset($vars['add'])) {
		global $_btn_addtop;
		$addtag  = '<input type="hidden" name="add"    value="true" />';
		$add_top = isset($vars['add_top']) ? ' checked="checked"' : '';
		$add_top = '<input type="checkbox" name="add_top" ' .
			'id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
			'  <label for="_edit_form_add_top">' .
				'<span class="small">' . $_btn_addtop . '</span>' .
			'</label>';
	}
	if($load_template_func && $b_template) {
		$template_page_list = get_template_page_list();
		$tpages = array(); // Template pages
		foreach($template_page_list as $p) {
			$ps = htmlsc($p);
			$tpages[] = '   <option value="' . $ps . '">' . $ps . '</option>';
		}
		if (count($template_page_list) > 0) {
			$s_tpages = join("\n", $tpages);
		} else {
			$s_tpages = '   <option value="">(no template pages)</option>';
		}
		$template = <<<EOD
  <select name="template_page">
   <option value="">-- $_btn_template --</option>
$s_tpages
  </select>
  <input type="submit" name="template" value="$_btn_load" accesskey="r" />
  <br />
EOD;

		if (isset($vars['refer']) && $vars['refer'] != '')
			$refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlsc($page);
	$s_digest    = htmlsc($digest);
	//$s_postdata  = htmlsc($refer . $postdata); → 不具合の原因
	$s_postdata  = $refer . $postdata;
	$s_original  = isset($vars['original']) ? htmlsc($vars['original']) : $s_postdata;
	$b_preview   = isset($vars['preview']); // TRUE when preview
	$btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;
    require_once(EASYEDIT_LIB_PATH . 'guiedit/wiki2htmlv.php');

    $s_postdata = guiedit_convert_html($s_postdata);
    //$s_postdata = plugin_easyedit_inline_replace2($s_postdata);

	$add_notimestamp = '';
	if ($notimeupdate != 0) {
		global $_btn_notchangetimestamp;
		$checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';
		if ($notimeupdate == 2) {
			$add_notimestamp = '   ' .
				'<input type="password" name="pass" size="12" />' . "\n";
		}
		$add_notimestamp = '<input type="checkbox" name="notimestamp" ' .
			'id="_edit_form_notimestamp" value="true"' . $checked_time . ' />' . "\n" .
			'   ' . '<label for="_edit_form_notimestamp"><span class="small">' .
			$_btn_notchangetimestamp . '</span></label>' . "\n" .
			$add_notimestamp .
			'&nbsp;';
	}
	$h_msg_edit_cancel_confirm = htmlsc($_msg_edit_cancel_confirm);
	$h_msg_edit_unloadbefore_message = htmlsc($_msg_edit_unloadbefore_message);
	$body = <<<EOD
<div class="edit_form">
 <form action="$script" method="post" class="_plugin_easyedit_easyedit_form" style="margin-bottom:0;">
$template
  $addtag
  <input type="hidden" name="cmd"    value="easyedit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" id="_msg_edit_cancel_confirm" value="$h_msg_edit_cancel_confirm" />
  <input type="hidden" id="_msg_edit_unloadbefore_message" value="$h_msg_edit_unloadbefore_message" />
  <textarea id="editor" class="ckeditor" name="msg" rows="$rows" cols="$cols">$s_postdata</textarea>
  <br />
  <div style="float:left;">
   <input type="submit" name="preview" value="$btn_preview" accesskey="p" />
   <input type="submit" name="write"   value="$_btn_update" accesskey="s" />
   $add_top
   $add_notimestamp
  </div>
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </form>
 <form action="$script" method="post" class="_plugin_edit_cancel" style="margin-top:0;">
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" value="$_btn_cancel" accesskey="c" />
 </form>
</div>
{$ckeditor}
EOD;

	$body .= '<ul><li><a href="' .
		get_page_uri($rule_page) .
		'" target="_blank">' . $_msg_help . '</a></li></ul>';
	return $body;
}
