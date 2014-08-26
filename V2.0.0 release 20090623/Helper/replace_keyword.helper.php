<?php

function replace_keyword($str, $word, $class='keyword')
{
	return str_replace($word, '<span class="' . $class . '">' . $word . '</span>', $str);
}

?>