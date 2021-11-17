<?php namespace Illuminate\Contracts\Queue;
interface Queue {
	public function push($job, $data = '', $queue = null);
	public function pushRaw($payload, $queue = null, array $options = array());
	public function later($delay, $job, $data = '', $queue = null);
	public function pushOn($queue, $job, $data = '');
	public function laterOn($queue, $delay, $job, $data = '');
	public function pop($queue = null);
}
