<?php
/**
 * ${product.title}
 *
 * Запрос HTTP
 *
 * @version ${product.version}
 * @copyright ${product.copyright}
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
 * Запрос HTTP
 *
 * @package Eresus
 * @since 2.16
 */
class Eresus_HTTP_Request
{
	/**
	 * Версия протокола
	 * @var string
	 */
	private $httpVersion;

	/**
	 * Метод запроса
	 * @var string
	 */
	private $method;

	/**
	 * URI запроса
	 * @var Eresus_URI
	 * @since 2.16
	 */
	private $uri;

	/**
	 * Заголовки
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Аргументы GET
	 *
	 * @var Eresus_HTTP_Request_Arguments
	 */
	private $query;

	/**
	 * Аргументы POST
	 *
	 * @var Eresus_HTTP_Request_Arguments
	 */
	private $post;

	/**
	 * Создаёт объект из окружения приложения
	 *
	 * @param string $className  имя класса создаваемого объекта (должен быть потомком
	 *                           {@link Eresus_HTTP_Request})
	 *
	 * @throws RuntimeException если класса $className не существует
	 * @throws InvalidArgumentException если $className не является потомком
	 *         {@link Eresus_HTTP_Request}
	 *
	 * @return Eresus_HTTP_Request|null  экземпляр $className или null в случае неудачи
	 *
	 * @since 2.16
	 * @uses Eresus_WebServer::getInstance()
	 * @uses Eresus_WebServer::getRequestHeaders()
	 * @uses Eresus_URI::setPath()
	 * @uses Eresus_URI::setQuery()
	 */
	static public function fromEnv($className = 'Eresus_HTTP_Request')
	{
		if (!class_exists($className, true))
		{
			throw new RuntimeException("Class \"$className\" not exists");
		}

		$request = new $className();

		if (! ($request instanceof self))
		{
			throw new InvalidArgumentException("\"$className\" must be a descendent of " . __CLASS__);
		}

		$request->headers = Eresus_WebServer::getInstance()->getRequestHeaders();

		/*
		 * Определяем версию протокола
		 */
		if (isset($_SERVER['SERVER_PROTOCOL']) &&
			($dividerPosition = strpos($_SERVER['SERVER_PROTOCOL'], '/')))
		{
			$httpVersion = substr($_SERVER['SERVER_PROTOCOL'], $dividerPosition + 1);
		}
		else
		{
			$httpVersion = '1.0';
		}

		$request->setHttpVersion($httpVersion);

		/*
		 * Определяем метод запроса
		 */
		if (isset($_SERVER['REQUEST_METHOD']))
		{
			$request->setMethod(strtoupper($_SERVER['REQUEST_METHOD']));
		}
		else
		{
			$request->setMethod('GET');
		}

		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' && $_SERVER['HTTPS'] != 'off')
		{
			$scheme .= 's';
		}
		$request->setScheme($scheme);

		if (isset($request->headers['Host']))
		{
			$host = $request->headers['Host'];
		}
		else
		{
			$host = 'localhost';
		}
		$request->setHost($host);

		if (isset($_SERVER['REQUEST_URI']))
		{
			$rel = $_SERVER['REQUEST_URI'];
		}
		else
		{
			$rel = '/';
		}

		$rel = parse_url($rel);
		$request->uri->setPath(@$rel['path']);
		$request->uri->setQuery(@$rel['query']);

		return $request;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Конструктор
	 *
	 * @return Eresus_HTTP_Request
	 *
	 * @since 2.16
	 * @uses Eresus_URI
	 */
	public function __construct()
	{
		$this->uri = new Eresus_URI();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает версию HTTP
	 *
	 * @param string $version  версия протокола
	 *
	 * @return bool  возвращает true в случае успеха, и false если передан неправильный номер версии
	 *               (не 1.0 или 1.1)
	 *
	 * @see getHttpVersion()
	 */
	public function setHttpVersion($version)
	{
		if (!preg_match('~^1\.[01]$~', $version))
		{
			return false;
		}

		$this->httpVersion = $version;
		return true;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает версию протокола
	 *
	 * @return string  номер версии протокола
	 *
	 * @see setHttpVersion()
	 */
	public function getHttpVersion()
	{
		return $this->httpVersion;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает схему запроса
	 *
	 * @param string $scheme  схема
	 *
	 * @throws InvalidArgumentException  если схема не http или https
	 *
	 * @return void
	 *
	 * @see getScheme()
	 * @uses Eresus_URI::setScheme()
	 */
	public function setScheme($scheme)
	{
		if (!preg_match('/https?/', $scheme))
		{
			throw new InvalidArgumentException('Unsupported request scheme: ' . $scheme);
		}

		$this->uri->setScheme($scheme);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает схему запроса
	 *
	 * @return string
	 *
	 * @see setScheme()
	 * @uses Eresus_URI::getScheme()
	 */
	public function getScheme()
	{
		return $this->uri->getScheme();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает запрашиваемый хост
	 *
	 * @param string $host  хост
	 *
	 * @return void
	 *
	 * @see getHost()
	 * @uses Eresus_URI::setHost()
	 */
	public function setHost($host)
	{
		$this->uri->setHost($host);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает запрашиваемый хост
	 *
	 * @return string
	 *
	 * @see setHost()
	 * @uses Eresus_URI::getHost()
	 */
	public function getHost()
	{
		return $this->uri->getHost();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает метод запроса HTTP
	 *
	 * @param string $method  имя метода запроса. См. список имён в
	 *                        {@link http://tools.ietf.org/html/rfc2068#section-5.1.1
	 *                        RFC2068, раздел 5.1.1}
	 * @return bool  true в случае успеха или false если указано неправильное имя метода
	 *
	 * @see getMethod()
	 */
	public function setMethod($method)
	{
		$method = strtoupper($method);
		$REQUEST_METHODS = array('OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'TRACE');

		if (!in_array($method, $REQUEST_METHODS))
		{
			return false;
		}

		$this->method = $method;
		return true;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает метод запроса
	 *
	 * @return string
	 *
	 * @see setMethod()
	 */
	public function getMethod()
	{
		return $this->method;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает путь к запрошенному файлу
	 *
	 * Например, для URI «http://example.org/some/path/to/file?a=b» вернёт
	 * «/some/path/to/file».
	 *
	 * @return string  имя файла
	 * @uses Eresus_URI::getPath()
	 */
	public function getPath()
	{
		return $this->uri->getPath();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает запрошенный URL
	 *
	 * @return string
	 *
	 * @see setUri()
	 */
	public function getUri()
	{
		return strval($this->uri);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает URI запроса
	 *
	 * @param string $uri абсолютный или относительный URI
	 *
	 * @throws InvalidArgumentException  если $uri не строка
	 *
	 * @return void
	 *
	 * @see getUri()
	 * @uses Eresus_URI
	 */
	public function setUri($uri)
	{
		if (!is_string($uri))
		{
			throw new InvalidArgumentException('String expected but ' . gettype($uri) . 'given');
		}

		$this->uri = new Eresus_URI($uri);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает заголовок
	 *
	 * @param string $header  имя заголовка, например "Host".
	 *
	 * @return string|null  значение заголовка или null, если такой заголовк отсутствует
	 *
	 * @since 2.16
	 */
	public function getHeader($header)
	{
		if (!isset($this->headers[$header]))
		{
			return null;
		}
		return $this->headers[$header];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает коллекцию аргументов запроса GET
	 *
	 * @return Eresus_HTTP_Request_Arguments
	 *
	 * @since 2.16
	 * @uses Eresus_HTTP_Request_Arguments
	 */
	public function getQuery()
	{
		if (!$this->query)
		{
			$this->query = new Eresus_HTTP_Request_Arguments($_GET);
		}
		return $this->query;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает коллекцию аргументов запроса POST
	 *
	 * @return Eresus_HTTP_Request_Arguments
	 *
	 * @since 2.16
	 * @uses Eresus_HTTP_Request_Arguments
	 */
	public function getPost()
	{
		if (!$this->post)
		{
			$this->post = new Eresus_HTTP_Request_Arguments($_POST);
		}
		return $this->post;
	}
	//-----------------------------------------------------------------------------
}