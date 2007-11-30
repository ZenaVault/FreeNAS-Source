<?php
/*
	array.inc
	Copyright (c) 2007 Volker Theile (votdev@gmx.de)
	All rights reserved.

	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2007 Olivier Cochard-Labb� <olivier@freenas.org>.
	All rights reserved.

	Based on m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

// Search in a multidimensional array.
function array_search_ex($needle, $haystack, $key) {
  foreach($haystack as $haystackval => $value) {
    $found = false;
    if(is_array($needle) && is_array($key)) {
      foreach($needle as $n => $needlev) {
        $found = ($value[$key[$n]] == $needlev);
        if(false == $found) {
          break;
        }
      }
    } else {
      $found = ($value[$key] == $needle);
    }
    if(true == $found) {
      return $haystackval;
    }
  }
  return false;
}

// Sort an array by values using a user-defined key.
// Returns TRUE on success or FALSE on failure.
// array - The array to sort
// key - the key used as sort criteria
function array_sort_key(array &$array, $key) {
	global $_key;

	// Declare local compare function.
	if(!function_exists(_array_sort_key_cmp)) {
		function _array_sort_key_cmp($a, $b) {
			global $_key;
			return strcmp($a[$_key], $b[$_key]);
		}
	}

	// Sort array. Set key used my compare function. Finally unset useless global variable.
	$_key = $key;
	$result = usort($array, "_array_sort_key_cmp");
	unset($_key);

	return $result;
}
?>