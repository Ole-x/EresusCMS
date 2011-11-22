<?php
/**
 * ${product.title} ${product.version}
 *
 * Модульные тесты
 *
 * @copyright 2011, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package Eresus
 * @subpackage Tests
 *
 * $Id$
 */

require_once __DIR__ . '/../bootstrap.php';
require_once TESTS_SRC_DIR . '/core/Config.php';
require_once TESTS_SRC_DIR . '/core/Kernel.php';
require_once TESTS_SRC_DIR . '/core/i18n.php';

/**
 * @package Eresus
 * @subpackage Tests
 */
class Eresus_i18n_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Log filename saver
	 *
	 * @var string
	 */
	protected $logSaver;

	/**
	 * Temporary log filename
	 *
	 * @var string
	 */
	protected $logFilename;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp()
	{
		$this->logSaver = ini_get('error_log');
		$TMP = isset($_ENV['TMP']) ? $_ENV['TMP'] : '/tmp';
		$this->logFilename = tempnam($TMP, 'eresus-core-');
		ini_set('error_log', $this->logFilename);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown()
	{
		@unlink($this->logFilename);
		@unlink(TESTS_SRC_DIR . '/lang/xx_XX.php');
		ini_set('error_log', $this->logSaver);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::setLocale
	 * @covers Eresus_i18n::getLocale
	 */
	public function test_setgetLocale()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->setMethods(array('getInstance'))->
			disableOriginalConstructor()->getMock();
		$i18n->setLocale('ru_RU');
		$this->assertEquals('ru_RU', $i18n->getLocale());
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::setLocale
	 * @expectedException InvalidArgumentException
	 */
	public function test_setLocale_invalid()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->setMethods(array('getInstance'))->
			disableOriginalConstructor()->getMock();
		$i18n->setLocale('ru');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::loadLocale
	 */
	public function test_loadLocale()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->setMethods(array('getInstance'))->
			disableOriginalConstructor()->getMock();

		$p_data = new ReflectionProperty('Eresus_i18n', 'data');
		$p_data->setAccessible(true);
		$p_data->setValue($i18n, array());

		$p_path = new ReflectionProperty('Eresus_i18n', 'path');
		$p_path->setAccessible(true);
		$p_path->setValue($i18n, TESTS_SRC_DIR . '/lang');

		/*
		Eresus_Config::set('eresus.cms.log.level', LOG_WARNING);
		$p_locale->setValue($i18n, 'xx_XX');
		$m_localeLazyLoad->invoke($i18n);
		$message = file_get_contents($this->logFilename);
		$this->assertContains('Can not load language file', $message);
		*/

		file_put_contents(TESTS_SRC_DIR . '/lang/xx_XX.php', '<?php return array();');
		$i18n->loadLocale('xx_XX');
		$this->assertTrue(is_array($p_data->getValue($i18n)));
		$this->assertArrayHasKey('xx_XX', $p_data->getValue($i18n));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::localeLazyLoad
	 */
	public function test_localeLazyLoad()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->setMethods(array('getInstance'))->
			disableOriginalConstructor()->getMock();
		$m_localeLazyLoad = new ReflectionMethod('Eresus_i18n', 'localeLazyLoad');
		$m_localeLazyLoad->setAccessible(true);

		$p_locale = new ReflectionProperty('Eresus_i18n', 'locale');
		$p_locale->setAccessible(true);
		$p_locale->setValue($i18n, null);

		$p_data = new ReflectionProperty('Eresus_i18n', 'data');
		$p_data->setAccessible(true);
		$p_data->setValue($i18n, array());

		$p_path = new ReflectionProperty('Eresus_i18n', 'path');
		$p_path->setAccessible(true);
		$p_path->setValue($i18n, TESTS_SRC_DIR . '/lang');

		/*
		Eresus_Config::set('eresus.cms.log.level', LOG_WARNING);
		$p_locale->setValue($i18n, 'xx_XX');
		$m_localeLazyLoad->invoke($i18n);
		$message = file_get_contents($this->logFilename);
		$this->assertContains('Can not load language file', $message);
		*/

		$m_localeLazyLoad->invoke($i18n);
		$this->assertTrue(is_array($p_data->getValue($i18n)));
		$this->assertArrayHasKey('ru_RU', $p_data->getValue($i18n));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::get
	 */
	public function test_get()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->
			setMethods(array('getInstance', 'localeLazyLoad'))->disableOriginalConstructor()->getMock();

		$p_data = new ReflectionProperty('Eresus_i18n', 'data');
		$p_data->setAccessible(true);
		$p_data->setValue($i18n, array(
			'ru_RU' => array(
				'messages' => array(
					'global' => array(
						'A' => 'B',
					),
					'some.context' => array(
						'A' => 'C',
					)
				)
			)
		));

		$i18n->setLocale('xx_XX');
		$this->assertEquals('A', $i18n->get('A'));
		$i18n->setLocale('ru_RU');
		$this->assertEquals('Z', $i18n->get('Z'));
		$this->assertEquals('B', $i18n->get('A'));
		$this->assertEquals('C', $i18n->get('A', 'some.context'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_i18n::translit
	 */
	public function test_translit()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->
			setMethods(array('getInstance'))->disableOriginalConstructor()->getMock();

		$p_path = new ReflectionProperty('Eresus_i18n', 'path');
		$p_path->setAccessible(true);
		$p_path->setValue($i18n, TESTS_SRC_DIR . '/lang');

		$i18n->setLocale('ru_RU');
		$this->assertEquals('test', $i18n->translit('тест'));

		$this->assertEquals('test', $i18n->translit('test!', 'en_US'));
	}
	//-----------------------------------------------------------------------------

	/**
	 *
	 */
	public function test_i18n()
	{
		$i18n = $this->getMockBuilder('Eresus_i18n')->setMethods(array('get'))->
			disableOriginalConstructor()->getMock();
		$i18n->expects($this->once())->method('get')->with('phrase', 'context');
		Eresus_Tests::setStatic('Eresus_Kernel', new sfServiceContainerBuilder(), 'sc');
		Eresus_Kernel::sc()->setService('i18n', $i18n);

		i18n('phrase', 'context');
	}
	//-----------------------------------------------------------------------------
	/* */
}
