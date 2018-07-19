                                
[__НАЧАЛО__](README.md)

## Публичное API:
 Получение обработанной картинки
----
> POST **/get-image**
    
***Параметры запроса (`multipart/form-data`):***

| Поле          | Тип параметра             | Описание                                           | Обязательный  |
| ------------- | ------------------------- | -------------------------------------------------- | ------------- |
| `filePath`    | `string`                  | Путь к файлу-оригиналу                             | да            |
| `watermarkPath`| `string`                 | Путь к файлу-водяному знаку                        | да/нет см. примечание 1|
| `operations`  | `array`                   | Массив операций над файлом                         | да см. примечание 2    |

***Примечания:***

1 Параметр обязателен при условии, если в параметре запроса *'operations'* есть параметры операции *'watermark'*

Пример: *'operations[watermark]=2'*

2 Если есть параметр *watermarkPath*, тогда обязательно указать в массиве операций соответствующую операцию.

Пример: *'operations[watermark]=1'*

Параметры операций указываются в виде констант.

Пример: *[resize => 1, watermark => 2].*

**Описание параметров операций находится в services-stl/src/v2/Reference/Image/DefinitionImagePreviewParams.php**


***Успех:***
 
  * **Content:** `image/*:
                  schema:
                  type: string
                  format: binary`
 
***Ошибка:***

  * **Code:** 400 BadRequest <br />
    **Content:** `{ status : "error", error : "errorMessage" }`
    

***Примеры запросов (`multipart/form-data`):***

1 Изменение размера картинки

| Параметр      | Значение параметра        |
| ------------- | ------------------------- |
| `filePath`    | `original_file.jpg`       |
| `watermarkPath`| null                     |
| `operations`  | [resize=1]                |   
                              
2 Нанесение водяного знака

| Параметр      | Значение параметра        |
| ------------- | ------------------------- |
| `filePath`    | `original_file.jpg`       |
| `watermarkPath`| `watermark.png`          |
| `operations`  | [watermark = 2]           |   
                              
3 Изменение размера картинки и нанесение водяного знака

| Параметр      | Значение параметра        |
| ------------- | ------------------------- |
| `filePath`    | `original_file.jpg`       |
| `watermarkPath`| `watermark.png`          |
| `operations`  | [watermark = 2, resize=1] |   
                                

