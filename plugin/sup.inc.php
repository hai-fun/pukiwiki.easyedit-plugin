<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: sup.inc.php,v 1.0 2003/06/25 00:00:00 reimy Exp $
//

function plugin_sup_inline()
{
	if (func_num_args() != 1)
	{
		return FALSE;
	}
	
	list($body) = func_get_args();
	
	if ($body == '')
	{
		return FALSE;
	}

	return "<span style=\"vertical-align:super\">$body</span>";
}
?>