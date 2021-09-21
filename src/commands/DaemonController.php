<?php

namespace phuong17889\daemon\commands;

use React\EventLoop\Factory;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class DaemonController
 * Daemon controller for console
 *
 * @package yiicod\cron\commands
 */
abstract class DaemonController extends Controller {

	/**
	 * @var double delay time, default 1 second
	 */
	public $interval = 1;

	/**
	 * @return mixed
	 */
	abstract protected function worker();

	/**
	 * @return string daemon name
	 */
	abstract protected function name();

	/**
	 * Run daemon based on ReactPHP loop
	 */
	public function actionStart() {
		$this->stdout(sprintf("[%s] start daemon\n", $this->name()), Console::FG_GREEN);
		$pid = $this->getPid();
		if ($this->checkPid($pid)) {
			$loop = Factory::create();
			$loop->addPeriodicTimer($this->interval, function() {
				$this->worker();
			});
			$loop->run();
		}
	}

	/**
	 * Stop daemon
	 */
	public function actionStop() {
		$this->stdout(sprintf("[%s] stop daemon\n", $this->name()), Console::FG_RED);
		$file = \Yii::getAlias('@runtime/daemons/' . $this->name() . '.bin');
		if (file_exists($file)) {
			$current_pid = file_get_contents($file);
			if (file_exists("/proc/$current_pid")) {
				exec("kill $current_pid 2> /dev/null");
			}
		}
	}

	/**
	 * Restart daemon
	 */
	public function actionRestart() {
		$this->stdout(sprintf("[%s] restart daemon\n", $this->name()), Console::FG_BLUE);
		$this->actionStop();
		$this->actionStart();
	}

	/**
	 * @param $pid
	 *
	 * @return bool
	 */
	protected function checkPid($pid) {
		$file = \Yii::getAlias('@runtime/daemons/' . $this->name() . '.bin');
		if (!file_exists(dirname($file))) {
			mkdir(dirname($file), 0777);
		}
		if (file_exists($file)) {
			$current_pid = file_get_contents($file);
			if (file_exists("/proc/$current_pid")) {
				return false;
			}
		}
		file_put_contents($file, $pid);
		return true;
	}

	/**
	 * @return false|int
	 */
	protected function getPid() {
		try {
			$pid = pcntl_fork();
			if ($pid == - 1) {
				exit('Error while forking process.');
			} elseif ($pid) {
				return $pid;
			} else {
				$pid = getmypid();
			}
			return $pid;
		} catch (\Exception | \Error $e) {
			exit('This extension can be only run in Linux.');
		}
	}
}
