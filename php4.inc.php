<?php
/*
 * Copyright (c) 2010 Björn Ahrens
 *
 * This file is part of "Unprintable Blog"
 *
 * "Unprintable Blog" is based on wp-mpdf 2.5 by Florian Krauthan.
 *
 * "Unprintable Blog" is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * "Unprintable Blog" is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with "Unprintable Blog". If not, see <http://www.gnu.org/licenses/>.
 */

	
	if(!function_exists('file_put_contents')) {
		function file_put_contents($file, $var) {
			$fp = fopen($file, 'w');
			if($fp) {
				fputs($fp, $var);
				fclose($fp);
			}
		}
	}
?>
