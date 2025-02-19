<?php

declare(strict_types=1);

namespace Core\Router;

use ArrayObject;
use Core\App\App;
use Core\App\Superglobals\Variables;
use Core\Config\Config;
use Core\Controller\PageNotFoundController;
use Core\Log\Log;

class Router
{
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

    public function dispatch(): void
    {
        $path = (string) parse_url(Variables::getParamValue(Variables::TYPE_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
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
        //Check if path is in routes file
        $routerIterator = new RouterFilter($path, $this->routes->getIterator());

        foreach ($routerIterator as $route) {
            $controllerInfo = explode('::', $route['controller']);
            $controllerFile = $controllerInfo[0];
            $controllerMethod = $controllerInfo[1];

            if ($this->isValidController($controllerFile, $controllerMethod)) {
                return ['file' => $controllerFile, 'method' => $controllerMethod];
            }
        }

        //Check default routing module/controller/action

        $moduleList = Config::get('modules');
        $pathParts = explode('/', trim($path, '/'));
        $moduleName = $pathParts[0];

        if (!empty($moduleList) && array_key_exists($moduleName, $moduleList)) {
            $moduleDir = $moduleList[$moduleName];
            $controllerFile = $moduleDir . '\\Controller\\';

            $controllerFile .= isset($pathParts[1]) ? ucfirst($pathParts[1]) . 'Controller' : 'IndexController';
            $controllerMethod = $pathParts[2] ?? 'index';

            if ($this->isValidController($controllerFile, $controllerMethod)) {
                return ['file' => $controllerFile, 'method' => $controllerMethod];
            }
        }

        return null;
    }

    private function isValidController(string $controllerFile, string $controllerMethod): bool
    {
        return class_exists($controllerFile) && method_exists($controllerFile, $controllerMethod);
    }
}
