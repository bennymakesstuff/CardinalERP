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

class MemcachePersistentDataStore implements PersistentDataStoreInterface
{
	/**
	 * Session Variable Prefix
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Memcache server host
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Memcache server port
	 *
	 * @var string
	 */
	protected $port;


	/**
	 * Create a new MemcachePersistentDataStore instance
	 *
	 * @param string $prefix Memcache Variable Prefix
	 */
	public function __construct($prefix = "DBAPI_")
	{
		global $conf;

		$this->prefix = $prefix;

		$serveraddress = (! empty($conf->global->MEMCACHED_SERVER)?$conf->global->MEMCACHED_SERVER:(! empty($conf->global->DCLOUD_MEMCACHED_SERVER)?$conf->global->DCLOUD_MEMCACHED_SERVER:'127.0.0.1:11211'));
		$tmparray=explode(':',$serveraddress);

		$this->host = $tmparray[0];
		$this->port = (! empty($tmparray[1])?$tmparray[1]:11211);
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

		// Using a memcached server
		if (class_exists('Memcached'))
		{
			$m = new \Memcached();
			$result = $m->addServer($this->host, $this->port);
			$data = $m->get($key);
			$rescode = $m->getResultCode();
			if ($rescode == 0) {
				return $data;
			}
		}
		else if (class_exists('Memcache'))
		{
			$m = new \Memcache();
			$result = $m->addServer($this->host, $this->port);
			$data = $m->get($key);
			if ($data) {
				return $data;
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

		// Using a memcached server
		if (class_exists('Memcached'))
		{
			$m = new \Memcached();
			$result = $m->addServer($this->host, $this->port);
			$m->set($key, $value);
			$rescode = $m->getResultCode();
			if ($rescode == 0) {
				return true;
			}
		}
		else if (class_exists('Memcache'))
		{
			$m = new \Memcache();
			$result = $m->addServer($this->host, $this->port);
			$result = $m->set($key, $value);
			if ($result) {
				return true;
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
	public function clear($key)
	{
		$key = $this->prefix . $key;

		// Using a memcached server
		if (class_exists('Memcached'))
		{
			$m = new \Memcached();
			$result = $m->addServer($this->host, $this->port);
			$m->delete($key);
			$rescode = $m->getResultCode();
			if ($rescode == 0) {
				return true;
			}
		}
		else if (class_exists('Memcache'))
		{
			$m = new \Memcache();
			$result = $m->addServer($this->host, $this->port);
			$result = $m->delete($key);
			if ($result) {
				return true;
			}
		}

		return false;
	}
}