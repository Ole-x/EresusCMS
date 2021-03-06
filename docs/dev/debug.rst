Отладка
=======

В начале знакомства с любым средством разработки ПО, разработчик неизбежно делает ошибки. Время на обнаружение и понимание причин возникновения таких ошибок может быть очень большим. Чтобы его уменьшить, существуют различные средства отладки.

Журнал (лог)
------------

Начиная с версии 2.14 в Eresus CMS появились функции журналирования. Сообщения записываются в журнал,
расположенный в ``var/log/eresus.log``.

Уровень детализации (и расположение журнала) можно изменить в файле ``index.php`` в корне сайта:

.. code-block:: php

   <?php
   /*
    * Установка имени файла журнала
    * ВАЖНО! Путь должен существовать быть доступен для записи скриптам PHP.
    */
   ini_set('error_log', dirname(__FILE__) . '/var/log/eresus.log');

   /**
    * Уровень детализации журнала
    */
   define('ERESUS_LOG_LEVEL' , LOG_WARNING);
