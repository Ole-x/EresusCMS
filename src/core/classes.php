<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2004, Михаил Красильников <mihalych@vsepofigu.ru>
 * @copyright 2007, Eresus Project, http://eresus.ru/
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
 *
 * $Id$
 */





/**
 * Класс для работы с расширениями системы
 *
 * @package Eresus
 */
class EresusExtensions
{
 /**
	* Загруженные расширения
	*
	* @var array
	*/
	var $items = array();
 /**
	* Определение имени расширения
	*
	* @param string $class     Класс расширения
	* @param string $function  Расширяемая функция
	* @param string $name      Имя расширения
	*
	* @return mixed  Имя расширения или false если подходящего расширения не найдено
	*/
	function get_name($class, $function, $name = null)
	{
		$result = false;
		if (isset(Eresus_CMS::getLegacyKernel()->conf['extensions']))
		{
			if (isset(Eresus_CMS::getLegacyKernel()->conf['extensions'][$class]))
			{
				if (isset(Eresus_CMS::getLegacyKernel()->conf['extensions'][$class][$function]))
				{
					$items = Eresus_CMS::getLegacyKernel()->conf['extensions'][$class][$function];
					reset($items);
					$result = isset($items[$name]) ? $name : key($items);
				}
			}
		}

		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Загрузка расширения
	*
	* @param string $class     Класс расширения
	* @param string $function  Расширяемая функция
	* @param string $name      Имя расширения
	*
	* @return mixed  Экземпляр класса EresusExtensionConnector или false если не удалось загрузить расширение
	*/
	function load($class, $function, $name = null)
	{
		$result = false;
		$name = $this->get_name($class, $function, $name);

		if (isset($this->items[$name]))
		{
			$result = $this->items[$name];
		}
			else
		{
			$filename = Eresus_CMS::getLegacyKernel()->froot.'ext-3rd/' . $name .
				'/eresus-connector.php';
			if (is_file($filename))
			{
				include_once $filename;
				$class = $name.'Connector';
				if (class_exists($class))
				{
					$this->items[$name] = new $class();
					$result = $this->items[$name];
				}
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
}
