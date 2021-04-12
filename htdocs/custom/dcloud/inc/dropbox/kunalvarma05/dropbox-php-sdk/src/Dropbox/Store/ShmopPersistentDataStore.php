<?php
/* Copyright (C) 2014-2017 Regis Houssin  <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

namespace Kunnu\Dropbox\Store;

class ShmopPersistentDataStore implements PersistentDataStoreInterface
{
	/**
	 * Session Variable Prefix
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Create a new ShmopPersistentDataStore instance
	 *
	 * @param string $prefix Shmop Variable Prefix
	 */
	public function __construct($prefix = "DBAPI_")
	{
		$this->prefix = $prefix;
	}

	/**
	 * Get a value from the store
	 *
	 * @param  string $key Data Key
	 *
	 * @return string|null
	 */
	public function get($key)
	{
		$key = $this->prefix . $key;

		if (function_exists("shmop_read"))
		{
			$shmkey = base_convert(hash("crc32b", $key), 16, 10);
			$handle = @shmop_open($shmkey,'a',0,0);
			if ($handle)
			{
				$my_string = trim(@shmop_read($handle,0,0));
				if (! empty($conf->global->DCLOUD_SHMOP_MEMCOMPRESS_LEVEL) && function_exists('gzuncompress')){
					$my_string = @gzuncompress($my_string);
				}
				if ($my_string)
				{
					@shmop_close($handle);
					return $my_string;
				}
			}
		}

		return false;
	}

	/**
	 * Set a value in the store
	 * @param string $key   Data Key
	 * @param string $value Data Value
	 *
	 * @return void
	 */
	public function set($key, $value)
	{
		$key = $this->prefix . $key;

		if (function_exists("shmop_write"))
		{
			$shmkey = base_convert(hash("crc32b", $key), 16, 10);
			$fdata = $value;
			if (! empty($conf->global->DCLOUD_SHMOP_MEMCOMPRESS_LEVEL) && function_exists('gzcompress')){
				$fdata = @gzcompress($fdata, (int) $conf->global->DCLOUD_SHMOP_MEMCOMPRESS_LEVEL);
			}
			$fsize = strlen($fdata);
			$handle = @shmop_open($shmkey,'c',0644,$fsize);
			if ($handle)
			{
				$shm_bytes_written = @shmop_write($handle, $fdata, 0);
				if ($shm_bytes_written == $fsize)
				{
					@shmop_close($handle);
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Clear the key from the store
	 *
	 * @param $key Data Key
	 *
	 * @return void
	 */
	public function clear()
	{
		$key = $this->prefix . $key;

		if (function_exists("shmop_delete"))
		{
			$shmkey = base_convert(hash("crc32b", $key), 16, 10);
			$handle = @shmop_open($shmkey,'a',0,0);
			if ($handle)
			{
				if (!@shmop_delete($handle))
				{
					@shmop_close($handle);
					return false;
				}
				else
				{
					@shmop_close($handle);
					return true;
				}
			}
		}

		return false;
	}
}