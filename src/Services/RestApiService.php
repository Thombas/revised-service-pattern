<?php

namespace Thombas\RevisedServicePattern\Services;

use ReflectionClass;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\PendingRequest;
use Symfony\Component\Finder\SplFileInfo;
use Thombas\RevisedServicePattern\Services\Traits\HasUrl;
use Thombas\RevisedServicePattern\Services\Traits\HasMethod;
use Thombas\RevisedServicePattern\Services\Traits\HasHeaders;
use Thombas\RevisedServicePattern\Services\Traits\HasParameters;
use Thombas\RevisedServicePattern\Services\Traits\CanMockResponse;
use Thombas\RevisedServicePattern\Services\Traits\HasCacheMethods;
use Thombas\RevisedServicePattern\Services\Traits\ImplementsServiceApiMethods;

abstract class RestApiService extends PendingRequest
{
    use ImplementsServiceApiMethods;

    use CanMockResponse;

    use HasMethod;

    use HasHeaders;

    use HasCacheMethods;

    use HasParameters;

    use HasUrl;

    protected ?array $validation = null;

    public function __construct(
        public ?string $stub = null
    ) {
        parent::__construct();
    }

    public function __invoke(
        bool $format = true
    ) {
        if ($format) {
            return $this->format(response: $this->getResponse());
        }

        return $this->getResponse();
    }

    public function getResponse(): Response
    {
        if ($this->getValidation()) {
            Validator::validate($this->getParameters(), $this->getValidation());
        }

        if ($before = $this->before()) {
            return $before;
        }

        $response = ($this->isMocking() ? $this->getStub() : $this->setup()
            ->withHeaders($this->getHeaders())
            ->{$this->getMethod()}(
                $this->getUrl(),
                $this->getParameters()
            ));

        if ($this->async) {
            $response = $response->wait();
        }

        $this->validate(response: $response);

        $this->after(response: $response);

        return $response;
    }

    abstract protected function setup(): static;

    abstract protected function before(): ?Response;

    abstract protected function validate(Response $response): void;

    abstract protected function after(Response $response): void;

    abstract protected function format(Response $response): mixed;

    protected function getAsJson(
        Response $response,
        ?string $key = null
    ): object|string|array|null {
        return json_decode(json_encode($response->json(key: $key)), FALSE);
    }

    protected function onFailedResponse(Response $response): void
    {
        $response->throw();
    }

    public function getValidation(): ?array
    {
        return $this->validation;
    }

    public function setValidation(?array $validation): static
    {
        $this->validation = $validation;
        return $this;
    }

    public function createClient($handlerStack)
    {
        if ($this->isMocking()) {
            return resolve(Client::class);
        }

        return parent::createClient($handlerStack);
    }

    public static function __callStatic($method, $parameters)
    {
        $methods = static::registerDefaultMethods();
        
        if (isset($method, $methods)) {
            return ($methods[$method])(...$parameters);
        }

        return (get_parent_class(static::class) && method_exists(get_parent_class(static::class), '__callStatic'))
            ? parent::__callStatic($method, $parameters)
            : throw new \Exception("Static method {$method} does not exist.");
    }

    private static function registerDefaultMethods()
    {
        $reflection = new \ReflectionClass(get_called_class());
        $directory = dirname($reflection->getFileName());

        $files = File::allFiles($directory);

        $methods = [];

        foreach ($files as $file) {
            $class = static::getClassNameFromFile(
                file: $file,
                baseNamespace: Str::beforeLast(static::class, '\\'),
            );
            
            if (!is_null($class) && (new ReflectionClass($class))->isInstantiable() && is_subclass_of($class, static::class)) {
                $relative = static::getRelativePath($file->getPathname(), $directory);
                $methodName = static::buildMethodName($relative);
                $methods[$methodName] = function (...$args) use ($class) {
                    return app($class, $args)->__invoke();
                };
            }
        }

        return $methods;
    }

    private static function getClassNameFromFile(
        SplFileInfo $file,
        string $baseNamespace,
    ): ?string {
        $relativePath = $file->getRelativePathname();

        if (substr($relativePath, -4) === '.php') {
            $relativePath = substr($relativePath, 0, -4);
        } else {
            return null;
        }

        $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        return $baseNamespace . '\\' . $classPath;
    }

    private static function getRelativePath(string $path, string $directory): string
    {
        return str_replace($directory . DIRECTORY_SEPARATOR, '', $path);
    }

    private static function buildMethodName(string $relative): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $relative);
        $fileName = pathinfo($parts[array_key_last($parts)], PATHINFO_FILENAME);
        return Str::camel($fileName);
    }
    
    private static function buildModelClassName(string $relative, string $baseFolder, string $basePath): string
    {
        $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', str_replace('.php', '', $relative));
        return "App\\{$baseFolder}\\Models\\{$basePath}\\" . $classPath;
    }

    private static function getClassFullNameFromFile($filePath)
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);
        $namespace = '';
        $class = '';
        $count = count($tokens);
        
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR) {
                        $namespace .= $tokens[$j][1];
                    } elseif ($tokens[$j] === ';') {
                        break;
                    }
                }
            }
            if ($tokens[$i][0] === T_CLASS) {
                if (isset($tokens[$i - 1]) && is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_DOUBLE_COLON) {
                    continue;
                }
                
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{') {
                        $class = $tokens[$i + 2][1];
                        break 2;
                    }
                }
            }
        }
        
        if ($namespace) {
            return $namespace . '\\' . $class;
        }
        
        return $class ?: null;
    }
}
