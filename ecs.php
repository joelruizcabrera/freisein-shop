<?php declare(strict_types=1);

use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\FixerFactory;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/custom/static-plugins']);
    $ecsConfig->cacheDirectory('.ecs_cache');

    /**
     * Load and apply symfony rule sets from PhpCsFixer
     */
    $fixerFactory = new FixerFactory();
    $fixerFactory->registerBuiltInFixers();
    $ruleSet = new RuleSet([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ]);
    $fixerFactory->useRuleSet($ruleSet);
    $services = $ecsConfig->services();
    foreach ($fixerFactory->getFixers() as $fixer) {
        $configurator = $services->set($fixer::class);

        if ($fixer->getName() === "phpdoc_to_comment") {
            $configurator->call('configure', [['ignored_tags' => ['psalm-suppress', 'phpstan-ignore-next-line']]]);
            continue;
        }

        if ($ruleConfiguration = $ruleSet->getRuleConfiguration($fixer->getName())) {
            $configurator->call('configure', [$ruleConfiguration]);
        }
    }

    $ecsConfig->sets([
        SetList::ARRAY,
//        SetList::CONTROL_STRUCTURES, // AssignmentInConditionSniff
        SetList::STRICT,
        SetList::PSR_12,
    ]);

    /**
     * When using the SetList CONTROL_STRUCTURES the AssignmentInConditionSniff is also applied.
     * We can't use the skip mechanic because it overwrites all the SetList skips.
     *
     * So below we create our own CONTROL_STRUCTURES and do without AssignmentInConditionSniff.
     */
    $ecsConfig->rules([PhpUnitMethodCasingFixer::class, FunctionToConstantFixer::class, ExplicitStringVariableFixer::class, ExplicitIndirectVariableFixer::class, NewWithBracesFixer::class, StandardizeIncrementFixer::class, SelfAccessorFixer::class, MagicConstantCasingFixer::class, NoUselessElseFixer::class, SingleQuoteFixer::class, OrderedClassElementsFixer::class]);
    $ecsConfig->ruleWithConfiguration(SingleClassElementPerStatementFixer::class, ['elements' => ['const', 'property']]);
    $ecsConfig->ruleWithConfiguration(ClassDefinitionFixer::class, ['single_line' => \true]);
    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, ['equal' => \false, 'identical' => \false, 'less_and_greater' => \false]);
};
