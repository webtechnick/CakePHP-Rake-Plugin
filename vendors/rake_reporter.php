<?php
/**
 * Rails test reporter.
 * PHP version 4 and 5
 * 
 * @author        Nick Baker
 * @copyright     2010 webtechnick
 * @link          http://www.webtechnick.com
 * @license       MIT
 */
	if (version_compare(PHP_VERSION, '4.4.4', '<=') ||
		PHP_SAPI == 'cgi') {
		define('STDOUT', fopen('php://stdout', 'w'));
		define('STDERR', fopen('php://stderr', 'w'));
		register_shutdown_function(create_function('', 'fclose(STDOUT); fclose(STDERR); return true;'));
	}

App::import('Vendor', 'simpletest' . DS . 'reporter');
include_once CAKE . 'tests' . DS . 'lib'. DS . 'reporter' . DS . 'cake_base_reporter.php';

/**
 * Minimal command line test displayer. Writes fail details to STDERR. Returns 0
 * to the shell if all tests pass, ST_FAILS_RETURN_CODE if any test fails.
 *
 * @package cake
 * @subpackage cake.tests.libs.reporter
 */
class RakeReporter extends CakeBaseReporter {
/**
 * separator string for fail, error, exception, and skip messages.
 *
 * @var string
 */
	var $separator = '->';

/**
 * array of 'request' parameters
 *
 * @var array
 */
	var $params = array();

/**
* string of errors,exceptions,and anything else we want to output at the end of the tests
*
* @var string
*/
	var $buffer = null;

/**
 * Constructor
 *
 * @param string $separator 
 * @param array $params 
 * @return void
 */
	function RakeReporter($charset = 'utf-8', $params = array()) {
		$this->CakeBaseReporter($charset, $params);
	}

	function setFailDetailSeparator($separator) {
		$this->separator = $separator;
	}

/**
 * Paint fail faildetail to STDERR.
 *
 * @param string $message Message of the fail.
 * @return void
 * @access public
 */
	function paintFail($message) {
		parent::paintFail($message);
		$message .= $this->_getBreadcrumb();
		$this->buffer .= 'FAIL' . $this->separator . $message;
		fwrite(STDERR, 'f');
	}
	
/**
 * Paint pass passdetail to STDOUT.
 *
 * @param string $message Message of the fail.
 * @return void
 * @access public
 */	
	function paintPass($message){
		parent::paintPass($message);
		if(isset($this->params['verbose']) && $this->params['verbose']) {
			$message .= $this->_getBreadcrumb();
			$this->buffer .= 'PASS' . $this->separator . $message;
		}
		fwrite(STDOUT, '.');
	}

/**
 * Paint PHP errors to STDERR.
 *
 * @param string $message Message of the Error
 * @return void
 * @access public
 */
	function paintError($message) {
		parent::paintError($message);
		$message .= $this->_getBreadcrumb();
		$this->buffer .= 'ERROR' . $this->separator . $message;
		fwrite(STDERR, 'e');
	}

/**
 * Paint exception faildetail to STDERR.
 *
 * @param string $message Message of the Error
 * @return void
 * @access public
 */
	function paintException($exception) {
		parent::paintException($exception);
		$message .= sprintf('Unexpected exception of type [%s] with message [%s] in [%s] line [%s]',
			get_class($exception),
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		);
		$message .= $this->_getBreadcrumb();
		$this->buffer .= 'EXCEPTION' . $this->separator . $message;
		fwrite(STDERR, 'x');
	}

/**
 * Get the breadcrumb trail for the current test method/case
 *
 * @return string The string for the breadcrumb
 */
	function _getBreadcrumb() {
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		$out = "\n\tin " . implode("\n\tin ", array_reverse($breadcrumb));
		$out .= "\n\n";
		return $out;
	}

/**
 * Paint a test skip message
 *
 * @param string $message The message of the skip
 * @return void
 */
	function paintSkip($message) {
		parent::paintSkip($message);
		$this->buffer .= 'SKIP' . $this->separator . $message . "\n\n";
		fwrite(STDOUT, 's');
	}

/**
 * Paint a footer with test case name, timestamp, counts of fails and exceptions.
 */
	function paintFooter($test_name) {
		$buffer = "\n" . $this->buffer;
		$buffer .=  $this->getTestCaseProgress() . '/' . $this->getTestCaseCount() . ' test cases complete: ';

		if (0 < ($this->getFailCount() + $this->getExceptionCount())) {
			$buffer .= $this->getPassCount() . " passes";
			if (0 < $this->getFailCount()) {
				$buffer .= ", " . $this->getFailCount() . " fails";
			}
			if (0 < $this->getExceptionCount()) {
				$buffer .= ", " . $this->getExceptionCount() . " exceptions";
			}
			$buffer .= ".\n";
			$buffer .= $this->_timeStats();
			fwrite(STDOUT, $buffer);
		} else {
			fwrite(STDOUT, $buffer . $this->getPassCount() . " passes.\n" . $this->_timeStats());
		}

		if (
			isset($this->params['codeCoverage']) && 
			$this->params['codeCoverage'] && 
			class_exists('CodeCoverageManager')
		) {
			CodeCoverageManager::report();
		}
	}

/**
 * Get the time and memory stats for this test case/group
 *
 * @return string String content to display
 * @access protected
 */
	function _timeStats() {
		$out = 'Time taken by tests (in seconds): ' . $this->_timeDuration . "\n";
		if (function_exists('memory_get_peak_usage')) {
			$out .= 'Peak memory use: (in bytes): ' . number_format(memory_get_peak_usage()) . "\n";
		}
		return $out;
	}
}
?>