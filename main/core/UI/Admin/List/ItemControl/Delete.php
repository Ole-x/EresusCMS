<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2011, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mihalych@vsepofigu.ru>
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
 * @package UI
 *
 * $Id$
 */

/**
 * ЭУ "Удалить" для {@link Eresus_UI_Admin_List}
 *
 * @package UI
 *
 * @since 2.16
 */
class Eresus_UI_Admin_List_ItemControl_Delete extends Eresus_UI_Admin_List_ItemControl
{
	/**
	 * @see Eresus_UI_Admin_List_ItemControl::$action
	 */
	protected $action = 'delete';

	/**
	 * @see Eresus_UI_Admin_List_ItemControl::$icon
	 */
	protected $icon = 'item-delete.png';

	/**
	 * @see Eresus_UI_Admin_List_ItemControl::$alt
	 */
	protected $alt = '[x]';

	/**
	 * @see Eresus_UI_Admin_List_ItemControl::__construct()
	 */
	public function __construct()
	{
		$i18n = I18n::getInstance();
		$this->title = $i18n->getText('admDelete');
	}
	//-----------------------------------------------------------------------------
}
