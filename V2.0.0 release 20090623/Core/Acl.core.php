<?php
class SCN_Acl
{
	public static function checkAccess($resource, $role)
	{
		$ACO = SCN_Configure::get('ACO.'.$resource);
		if (!$ACO) {
			$n = count(SCN_Configure::get('ACO'));
			SCN_Configure::set('ACO.'.$resource, pow(2,$n));
			SCN_Configure::save('ACO');
		}
	}
}
?>