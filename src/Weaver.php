<?php

declare(strict_types=1);

namespace Ray\Aop;

final class Weaver
{
    /**
     * @var BindInterface
     */
    private $bind;

    /**
     * @var string
     */
    private $bindName;

    /**
     * @var string
     */
    private $classDir;

    /**
     * @var AopClassName
     */
    private $aopClassName;

    /**
     * @var Compiler
     */
    private $compiler;

    public function __construct(BindInterface $bind, string $classDir)
    {
        $this->bind = $bind;
        $this->bindName = $bind->toString('');
        $this->compiler = new Compiler($classDir);
        $this->classDir = $classDir;
        $this->aopClassName = new AopClassName;
        $this->regsterLoader();
    }

    public function __wakeup()
    {
        $this->regsterLoader();
    }

    public function newInstance(string $class, array $args)
    {
        $aopClass = $this->weave($class);
        $instance = (new ReflectionClass($aopClass))->newInstanceArgs($args);
        $instance->bindings = $this->bind->getBindings();

        return $instance;
    }

    public function weave(string $class) : string
    {
        $aopClass = ($this->aopClassName)($class, $this->bindName);
        if (! class_exists($aopClass)) {
            $this->compiler->compile($class, $this->bind);
            assert(class_exists($aopClass));
        }

        return $aopClass;
    }

    private function regsterLoader()
    {
        spl_autoload_register(
            function (string $class) {
                $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $class));
                if (file_exists($file)) {
                    include $file; //@codeCoverageIgnore
                }
            }
        );
    }
}
