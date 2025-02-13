<?php

namespace Core\Router;

use App;
use ArrayObject;
use Core\Controller\PageNotFoundController;
use Core\Config\Config;
use Core\Log\Log;

class Router
{
    /**
     * @var ArrayObject
     */
    // @phpstan-ignore missingType.generics
    private ArrayObject $routes;

    public function __construct()
    {
        $yaml = yaml_parse_file(App::BASE_APP_DIR . '/routes/routes.yaml');
        if ($yaml === false) {
            $yaml = [];
        }
        $this->routes = new ArrayObject($yaml);
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $result = $this->match($path);
        if ($result) {
            try {
                $controllerInstance = new $result['file']();
                $controllerInstance->{$result['method']}();
                return;
            } catch (\Throwable $e) {
                Log::write($e->getMessage());
            }
        } else {
            try {
                $controllerInstance = new PageNotFoundController();
                $controllerInstance->index();
            } catch (\Throwable $e) {
                Log::write($e->getMessage());
            }
        }
    }

    /**
     * @param string $path
     *
     * @return null|array<string, string>
     */
    private function match(string $path): ?array
    {
        //Check is path in routes file
        $routerIterator = new RouterFilter($this->routes->getIterator(), $path);
        foreach ($routerIterator as $route) {
            $controllerInfo = explode('::', $route['controller']);
            $controllerFile = $controllerInfo[0];
            $controllerMethod = $controllerInfo[1];
            if ($this->isValidController($controllerFile, $controllerMethod)) {
                return ['file' => $controllerFile, 'method' => $controllerMethod];
            }
        }

        //Check default routing module/controller/action

        $moduleList = Config::getConfig('modules');
        $test = explode('/', trim($path, '/'));
        $moduleName = $test[0];

        if (!empty($moduleList) && array_key_exists($moduleName, $moduleList)) {
            $moduleDir = $moduleList[$moduleName];
            $controllerFile = $moduleDir . '\\Controller\\';
            if (!isset($test[1])) {
                $controllerFile .= 'IndexController';
            } else {
                $controllerFile .= ucfirst($test[1]) . 'Controller';
            }
            if (!isset($test[2])) {
                $controllerMethod = 'index';
            } else {
                $controllerMethod = $test[2];
            }

            if ($this->isValidController($controllerFile, $controllerMethod)) {
                return ['file' => $controllerFile, 'method' => $controllerMethod];
            }
        }

        return null;
    }

    /**
     * @param string $controllerFile
     * @param string $controllerMethod
     *
     * @return bool
     */
    private function isValidController(string $controllerFile, string $controllerMethod): bool
    {
        return class_exists($controllerFile) && method_exists($controllerFile, $controllerMethod);
    }
}
