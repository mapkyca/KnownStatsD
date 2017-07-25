<?php

namespace IdnoPlugins\StatsD {


    class StatsDStatisticsCollector extends \Idno\Stats\StatisticsCollector {

	private $bucket = "";
	private $port = 8125;
	private $host = 'localhost';
	private $enabled = false;
	private $samplerate = 1;

	public function __construct() {

	    $config = \Idno\Core\Idno::site()->config();
	    if (!empty($config)) {
		if (empty($config->statsd_bucket))
		    $this->bucket = $config->host;
		else
		    $this->bucket = $config->statsd_bucket;

		if (!empty($config->statsd_port))
		    $this->port = $config->statsd_port;

		if (!empty($config->statsd_host))
		    $this->host = $config->statsd_host;
		
		if (!empty($config->statsd_samplerate))
		    $this->samplerate = $config->statsd_samplerate;

		if (!empty($config->statsd_enabled))
		    $this->enabled = $config->statsd_enabled;
	    }
	}

	public function decrement($stat) {
	    
	    $stat = $this->normaliseStatName("{$this->bucket}.$stat"); // Sanitise string, add bucket
	    
	    $this->updateStats($stat, -1, $this->samplerate, 'c');
	}

	public function gauge($stat, $value) {
	    
	    $stat = $this->normaliseStatName("{$this->bucket}.$stat"); // Sanitise string, add bucket
	    
	    $this->updateStats($stat, $value, 1, 'g');
	    
	}

	public function increment($stat) {
	    
	    $stat = $this->normaliseStatName("{$this->bucket}.$stat"); // Sanitise string, add bucket
	    
	    $this->updateStats($stat, 1, $this->samplerate, 'c');
	    
	}

	public function set($stat, $value) {
	    
	    $stat = $this->normaliseStatName("{$this->bucket}.$stat"); // Sanitise string, add bucket
	    
	    $this->updateStats($stat, $value, 1, 's');
	    
	}

	public function timing($stat, $time) {
	    
	    $stat = $this->normaliseStatName("{$this->bucket}.$stat"); // Sanitise string, add bucket
	    
	    $this->updateStats($stat, $time, $this->samplerate, 'ms');
	}

	protected function normaliseStatName($stat) {
	    $stat = str_replace(':', '.', $stat);
	    
	    return $stat;
	}
	
	/**
	 * Updates one or more stats.
	 *
	 * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
	 * @param int|1 $delta The amount to increment/decrement each metric by.
	 * @param float|1 $sampleRate the rate (0-1) for sampling.
	 * @param string|c $metric The metric type ("c" for count, "ms" for timing, "g" for gauge, "s" for set)
	 * @return boolean
	 */
	protected function updateStats($stats, $delta = 1, $sampleRate = 1, $metric = 'c') {
	    if (!is_array($stats)) {
		$stats = [$stats];
	    }

	    $data = [];

	    foreach ($stats as $stat) {
		$data[$stat] = "$delta|$metric";
	    }

	    $this->send($data, $sampleRate);
	}

	/**
	 * Send a stat over UDP, ignoring errors
	 * @param array $data Name value pairs 
	 * @param type $sampleRate
	 * @return type
	 */
	protected function send($data, $sampleRate = 1) {

	    // Allow this to be turned on and off in config
	    if (!$this->enabled)
		return;

	    // sampling
	    $sampledData = array();
	    if ($sampleRate < 1) {
		foreach ($data as $stat => $value) {
		    if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
			$sampledData[$stat] = "$value|@$sampleRate";
		    }
		}
	    } else {
		$sampledData = $data;
	    }
	    if (empty($sampledData)) {
		return;
	    }
	    
	    // Wrap this in a try/catch - failures in any of this should be silently ignored
	    try {
		$host = $this->host;
		$port = $this->port;

		$fp = fsockopen("udp://$host", $port, $errno, $errstr);
		if (!$fp) {
		    return;
		}
		foreach ($sampledData as $stat => $value) {
		    fwrite($fp, "$stat:$value");
		}
		fclose($fp);
		
	    } catch (Exception $e) {
		error_log($e->getMessage());
	    }
	}

    }

}