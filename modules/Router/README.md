Miaox_Router
============

Использование
-------------

### Конфигурация XML ###
Пример конфигурации

    <?xml version="1.0" encoding="UTF-8" ?>
    <config>
        <libs>
            <Miaox>
                <modules>
                    <Router>
                        <route>
                            <url>/</url>
                            <view>Main</view>
                        </route>
                        <route>
                            <url>/save/:social/:id</url>
                            <action>Test</action>
                            <validator type="regexp" param="social" regexp="(test|test2)" />
                            <validator type="regexp" param="id" regexp="\d+" />
                        </route>
                        <route>
                            <url>/load/:social/:id</url>
                            <action>TestAction</action>
                            <validator type="regexp" param="social" regexp="(test|test2)" />
                        </route>
                        <route>
                            <url>/done/:social/:id/:var</url>
                            <view>TestView</view>
                            <validator type="NotEmpty" param="social" />
                            <validator type="Numeric" param="id" />
                            <validator type="Len" param="id" max="2" min="1" />
                            <validator param="var" />
                        </route>
                        <error code="404" view="404" />
                    </Router>
                </modules>
            </Miaox>
        </libs>
    </config>

#### Описание тэга *`<route>`* ####

Основной тэг с настройками правил маппинга view и action по урл

*`<url>`* - шаблон или урл целиком н-р: "/" , "/save/:social/:id", где social, id - переменные

*`<view>`* - view проекта

*`<action>`* - action проекта. Если определены и view и action, то значение будет выставляться только для view

*`<validator>`* - валидаторы для переменных атрибуты:

*type* - тип проверки, доступны следующие типы: regexp, notEmpty, numeric, если тип не определен, то notEmpty.

*regexp* - регулярное выражение PCRE, используется только для валидаторов type=regexp.

*param* - имя параметра, для которого применяется валидатор, если параметр не опеределен в url, то валидатор игнорируется

#### Описание тэга *`<error>`* ####

Описание правил обработки кодов http ошибок
code - код http ошибки
view - view проекта
action - action проекта

### Подключение к проекту ###

#### Перенаправление всех входящих запросов на один контролер ####

##### Для сервера apache в .htaccess #####

    RewriteBase /
    RewriteCond %{QUERY_STRING} _action [OR]
    RewriteCond %{QUERY_STRING} _view
    RewriteRule .* - [F] # генерируется 403 Forbidden

    RewriteCond %{REQUEST_FILENAME} !-f [NC]
    RewriteCond %{REQUEST_FILENAME} !-d [NC]
    RewriteRule .* index.php [NC,QSA,L]

Где 2-4 строчки блокируют перезапись внутренних переменных через прямые запросы

##### Для сервера nginx #####

    if ($args ~ (_view|_action)){
        return 403;
    }

    try_files $uri index.php;

Где 1-3 строки блокируют перезапись внутренних переменных через прямые запросы

Строку с директивой try_files, можно заменить на

    if (!-e $request_filename){
        rewrite .* /index.php;
    }

#### Пример интеграции с контроллером ####

    $res = Miao_Config::Libs('Miaox_Router')->toArray();
    $router = Miaox_Router::getInstance()->loadRoutesFromArray($res);
    try
    {
        $route = $router->getRoute();
    }
    catch (Miaox_Router_Exception_RouteNotFound $e)
    {
        $route = $router->getErrorRoute(404);
    }

    if ($view = $route->getView())
    {
        $params['_view'] = $view;
    }
    elseif ($action = $route->getAction())
    {
        $params['_action'] = $action;
    }

Где $params - переменные, из которых выбираются данные для отображения view или выполнения action

### Генерация url ###

Кроме получения view и action модуль позволяет генерировать url на роутинга и переданны параметров

Примеры:

    echo $router->genUrlByView('Main'); // вернет "/"
    echo $router->genUrlByView('TestView', array ('social' => 'test10', 'id' => 123, 'var' => 'testA')); // вернет "/done/test10/123/testA"
    echo $router->genUrlByView('TestView', array ('social' => 'test10', 'id' => 'a', 'var' => 'testA')); // вернет пустую строку - переменная id не прошла валидацию
    echo $router->genUrlByAction('TestAction', array ('social' => 'test')); // вернет "/load/test/"

При генерации проверяются переданные параметры, если значения параметров не валидно, то поиск продолжается.
В случае, если ничего не найдено, вместо url вернется пустая строка.