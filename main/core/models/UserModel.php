<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * Модель пользователя
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-${build.year}, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 * @package EresusCMS
 *
 * $Id$
 */


/**
 * Модель пользователя
 *
 * Модель описывает пользователя сайта.
 *
 * @package EresusCMS
 */
class UserModel extends GenericModel implements IAclRole {

	/**
	 * Имя таблицы пользователей
	 *
	 * @var string
	 */
	protected $dbTable = 'users';

	/**
	 * Экземпляр модели текущего пользователя
	 *
	 * @var UserModel
	 * @see getCurrent
	 */
	private static $current;

	/**
	 * Получение модели текущего пользователя
	 *
	 * Метод реализует паттерн "Одиночка" для получения
	 * экземпляра модели пользователя, работающего в данный
	 * момент с сайтом.
	 *
	 * @return UserModel
	 * @see $current
	 */
	public static function getCurrent()
	{
		if (!self::$current) {

			$user = ecArrayValue($_SESSION, 'user');
			$id = $user ? $user['id'] : null;
			self::$current = new UserModel($id);

		}

		return self::$current;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Установка модели текущего пользователя
	 *
	 * @param UserModel $model
	 *
	 * @see function getCurrent
	 */
	public static function setCurrent(UserModel $model)
	{
		self::$current = $model;
		$_SESSION['user']['id'] = $model->id;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Поиск пользователя по имени и паролю
	 *
	 * @param string $username  Имя пользователя
	 * @param string $password  Пароль
	 * @return UserModel|null  Модель пользователя или null если пользователь не найден
	 */
	public static function findByCredentials($username, $password)
	{
		$list = new UserListModel();
		$list->filterUsername = $username;
		$list->filterPassword = $password;

		return $list->count() ? $list->item(0) : null;
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see main/core/classes/IAclRole#getRoleId()
	 */
	public function getRoleId()
	{
		#FIXME Устаревшие константы
		switch ($this->access) {
			case ROOT: return 'root';
			case ADMIN: return 'admin';
			case EDITOR: return 'editor';
			case USER: return 'user';
			case GUEST: return 'guest';
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see main/core/classes/IAclRole#getParentRoles()
	 */
	public function getParentRoles()
	{
		return array();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Хэширует строку
	 *
	 * @param string $source
	 * @return string
	 */
	public static function hash($source)
	{
		#FIXME Метод использует устаревший глобальный объект Eresus
		$result = md5($source);
		if (!$GLOBALS['Eresus']->conf['backward']['weak_password']) $result = md5($result);
		return $result;
	}
	//-----------------------------------------------------------------------------
}
