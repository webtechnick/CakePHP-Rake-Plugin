<?php
/**
  * A rake like test reporter for CakePHP
  * 
  * This extends the built in testsuite shell and overwrites the cake_cli_reporter 
  * for a rails+rake type feel.  Tests are run and feedback is presented to the user
  * passed tests are represented as ".", errors, exceptions, and skips are buffered 
  * output until all tests are complete.   Durring the tests constant feedback is given
  * to the user.  Each pass/fail/etc.. during testing is represented in like so:
  * 
  * pass      -> .
  * fail      -> f
  * error     -> e
  * exception -> x
  * skip      -> s
  *
  * If the verbose option is given all passes are also buffered and outputted.
  * 
  * @author Nick Baker
  * @license MIT
  * @example cake rake app all verbose
  * @example cake rake app case models/user cov verbose
  */
require_once(CAKE . 'console' . DS . 'libs'. DS . 'testsuite.php');

App::import('Vendor', 'Rake.rake_reporter');
class RakeShell extends TestSuiteShell {
  /**
    * Stores if the user wishes to receive verbose feedback
    * 
    * @var boolean
    * @access public
    */
  var $verbose = false;
  
  
  /**
    * Parse the arguments given into the Shell object properties.
    *
    * @return void
    * @access public
    */
  function parseArgs(){
    parent::parseArgs();
    
    if(!empty($this->args)){
      foreach($this->args as $arg){
        if($arg == 'verbose'){
          $this->verbose = true;
          return;
        }
      }
    }
  }

  /**
    * Help screen
    */
  function help(){
    parent::help();
    $this->out();
		$this->out('Verbose Output: ');
		$this->out("Append 'verbose' to any of the above to see verbose output of tests");
  }
  
  /**
    * Executes the tests depending on our settings
    *
    * @return void
    * @access private
    */
	function __run() {
		$Reporter = new RakeReporter('utf-8', array(
			'app' => $this->Manager->appTest,
			'plugin' => $this->Manager->pluginTest,
			'group' => ($this->type === 'group'),
			'codeCoverage' => $this->doCoverage,
			'verbose' => $this->verbose
		));

		if ($this->type == 'all') {
			return $this->Manager->runAllTests($Reporter);
		}

		if ($this->doCoverage) {
			if (!extension_loaded('xdebug')) {
				$this->out(__('You must install Xdebug to use the CakePHP(tm) Code Coverage Analyzation. Download it from http://www.xdebug.org/docs/install', true));
				$this->_stop(0);
			}
		}

		if ($this->type == 'group') {
			$ucFirstGroup = ucfirst($this->file);
			if ($this->doCoverage) {
				require_once CAKE . 'tests' . DS . 'lib' . DS . 'code_coverage_manager.php';
				CodeCoverageManager::init($ucFirstGroup, $Reporter);
				CodeCoverageManager::start();
			}
			$result = $this->Manager->runGroupTest($ucFirstGroup, $Reporter);
			return $result;
		}

		$folder = $folder = $this->__findFolderByCategory($this->category);
		$case = $this->__getFileName($folder, $this->isPluginTest);

		if ($this->doCoverage) {
			require_once CAKE . 'tests' . DS . 'lib' . DS . 'code_coverage_manager.php';
			CodeCoverageManager::init($case, $Reporter);
			CodeCoverageManager::start();
		}
		$result = $this->Manager->runTestCase($case, $Reporter);
		return $result;
	}
}
?>