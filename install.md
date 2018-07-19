[__НАЧАЛО__](README.md)

### Установка / Окружение

**Окружение:**
_Nginx,  Php 7.1_

**Платформа:**

_Phalcon 3.1_

**Локальные конфиги**

Конфигурация изменяемых классов-реализаторов операций над файлами задается в файле: _App/config/service.php_

Пример: 

      // Класс-реализатор функционала кропа   
      $di->setShared('imageCrop', function () {
            return new \App\Service\Classes\ImageCrop();
      });