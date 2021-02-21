<?php

declare(strict_types=1);

namespace Loot\Spinoza\Hooks;

use App\Services\Microservices\User\UserConcrete;
use Loot\PhpDocReader\PhpDocReader;
use Loot\Spinoza\Issues\HttpClientUsed;
use Loot\Spinoza\Parsers\RouteParser;
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
                $class = $statements_source->getSource()->getFQCLN();
                if ($method = $statements_source->getSource()->getMethodName()) {
                    $reflection = new \ReflectionMethod($class, $method);
                    $phpdocs = new PhpDocReader($reflection->getDocComment() ?: '');

                    if (self::isHttpClientCall($class) && !$phpdocs->hasAnnotation(RouteParser::ROUTE_ANNOTATION)) {
                        IssueBuffer::accepts(
                            new HttpClientUsed(
                                new CodeLocation($statements_source, $expr)
                            ),
                            $statements_source->getSuppressedIssues()
                        );
                    }
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
        try {
            $reflection = new \ReflectionClass($resolvedName);

            $parentsClass = [];

            while ($parent = $reflection->getParentClass()) {
                $parentsClass[] = $parent->getName();
                $reflection = $parent;
            }

            if (self::instanceOfHttpClient($parentsClass, config('app.httpClasses', []))) {
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
