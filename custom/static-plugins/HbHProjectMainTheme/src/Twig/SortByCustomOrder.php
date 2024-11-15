<?php
namespace HbH\ProjectMainTheme\Twig;

use Shopware\Core\Framework\Struct\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SortByCustomOrder extends AbstractExtension
{

    public function getName(): string
    {
        return 'SortByCustomOrder';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sortByCustomOrder', [$this, 'sortByCustomOrder'])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sortByCustomOrder', [$this, 'sortByCustomOrder'])
        ];
    }

    public static function sortByCustomOrder(Collection $collection, array $archetype = [], String $fieldname = 'name'): Collection
    {

        // Example Twig call in for loop:
        // {% for option in group.options|sortByCustomOrder(['XXS','XS','S','S/M','M','M/L','L','XL','XXL','XXXL','XXXXL','XXXXXL'], 'name') %}
        // Or in a function
        // {% set sortedOptions = sortByCustomOrder(group.options,['XXS','XS','S','S/M','M','M/L','L','XL','XXL','XXXL','XXXXL','XXXXXL'] ,'name') %}
        // Take values from archetype array and flip them with numeric order values.
        $archetype = array_flip(array_values($archetype));
        $arrayElements = $collection->getElements();
        uasort($arrayElements, function($a, $b) use($archetype, $fieldname) {

            $aPos = $archetype[$a->get($fieldname)] ?? null;
            $bPos = $archetype[$b->get($fieldname)] ?? null;

            if ($aPos === null && $bPos !== null)
            {
                return 1;
            }

            if ($aPos !== null && $bPos === null)
            {
                return -1;
            }

            return (int)$aPos - (int)$bPos;

        });

        /* @var Collection $collection */
        $collection = new (get_class($collection))($arrayElements);

        return $collection;

    }

}
