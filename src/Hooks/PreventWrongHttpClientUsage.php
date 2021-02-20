<?php

declare(strict_types=1);

namespace Loot\Spinoza\Hooks;

use Loot\PhpDocReader\PhpDocReader;
use Loot\Spinoza\Issues\HttpClientUsed;
use Loot\Spinoza\SpinozaWriter;
use Psalm\Codebase;
use PhpParser\Node\Expr;
use Psalm\Plugin\Hook\AfterExpressionAnalysisInterface;
use Psalm\StatementsSource;
use Psalm\IssueBuffer;
use Psalm\Context;
use Psalm\CodeLocation;

final class PreventWrongHttpClientUsage implements AfterExpressionAnalysisInterface
{
    /**
     * @var array of http classes that Rahmet has.
     */
    private static $httpClasses = [
        'App\Core\Paloma365\Request',
    ];

    /**
     * {@inheritdoc}
     */
    public static function afterExpressionAnalysis(
        Expr $expr,
        Context $context,
        StatementsSource $statements_source,
        Codebase $codebase,
        array &$file_replacements = []
    ): ?bool {
        if ($expr instanceof Expr\MethodCall) {
            if ($context->hasVariable('$'.$expr->var->name)) {
                $class = $context->vars_in_scope['$'.$expr->var->name]->getId();

                [, $methodName] = explode('::', $context->calling_method_id);
                $reflection = new \ReflectionMethod($context->self, $methodName);
                $phpdocs = new PhpDocReader($reflection->getDocComment());

                if (self::isHttpClientCall($class) && !$phpdocs->hasAnnotation(SpinozaWriter::ROUTE_ANNOTATION)) {
                    IssueBuffer::accepts(
                        new HttpClientUsed(
                            new CodeLocation($statements_source, $expr)
                        ),
                        $statements_source->getSuppressedIssues()
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param $resolvedName
     *
     * @return bool
     */
    private static function isHttpClientCall($resolvedName): bool
    {
        if (in_array($resolvedName, array_merge(self::$httpClasses))) {
            return true;
        }

        try {
            $reflection = new \ReflectionClass($resolvedName);

            $parentsClass = [];

            while ($parent = $reflection->getParentClass()) {
                $parentsClass[] = $parent->getName();
                $reflection = $parent;
            }

            if (self::instanceOfHttpClient($parentsClass, self::$httpClasses)) {
                return true;
            }
        } catch (\ReflectionException $exception) {
        }

        return false;
    }

    /**
     * @param array $parents
     * @param array $declaringHttpClients
     *
     * @return bool
     */
    private static function instanceOfHttpClient(array $parents, array $declaringHttpClients): bool
    {
        $isHttpClient = false;

        foreach ($parents as $parent) {
            if (in_array($parent, $declaringHttpClients)) {
                $isHttpClient = true;
                break;
            }

            continue;
        }

        return $isHttpClient;
    }
}
