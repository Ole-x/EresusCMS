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

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once TESTS_SRC_DIR . '/core/classes.php';
require_once TESTS_SRC_DIR . '/core/Kernel.php';

/**
 * @package Eresus
 * @subpackage Tests
 */
class Plugins_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers Plugins::autoload
	 */
	public function test_autoload()
	{
		$plugins = new Plugins;
		$plugins->list['foo'] = array();

		$GLOBALS['Eresus'] = new stdClass();
		$GLOBALS['Eresus']->root = TESTS_FIXT_DIR . '/core/Plugins/';

		$this->assertTrue($plugins->autoload('Foo_Bar_Baz'));
		$this->assertTrue(class_exists('Foo_Bar_Baz', false));

		$this->assertFalse($plugins->autoload('Foo_Bar'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers Eresus_Kernel::autoload
	 * @expectedException LogicException
	 * /
	public function test_autoload_failed()
	{
		$this->assertFalse(Eresus_Kernel::autoload('Eresus_Unexistent'));
	}
	//-----------------------------------------------------------------------------

	/* */
}
