<?php namespace Illuminate\Contracts\Queue;
interface Job {
	public function fire();
	public function delete();
	public function release($delay = 0);
	public function attempts();
	public function getName();
	public function getQueue();
}
