<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Gedmo\Sluggable\Util\Urlizer;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\Category;
use PhpSpec\ObjectBehavior;

class CategoryNormalizerSpec extends ObjectBehavior
{
    protected $globalContext;

    function let(
        ChannelManager $channelManager,
        CategoryMappingManager $categoryMappingManager,
        CategoryRepository $categoryRepository,
        MappingCollection $categoryMapping,
        MappingCollection $storeViewMapping
    ) {
        $this->beConstructedWith($channelManager, $categoryMappingManager, $categoryRepository);

        $this->globalContext = [
            'magentoCategories' => [],
            'magentoUrl'        => 'soap_url',
            'defaultLocale'     => 'default_locale',
            'magentoStoreViews' => [],
            'categoryMapping'   => $categoryMapping,
            'storeViewMapping'  => $storeViewMapping,
            'defaultStoreView'  => 'default',
            'urlKey'            => false,
            'is_anchor'         => 1
        ];
    }

    function it_normalizes_a_new_category(
        Category $category,
        Category $parentCategory,
        $categoryMapping,
        $categoryMappingManager
    ) {
        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(null);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn([
            'create'    => [
                [
                    'magentoCategory' => [
                        '3',
                        [
                            'name'              => 'category_label',
                            'is_active'         => 1,
                            'include_in_menu'   => 1,
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                            'url_key'           => 'category-label-category-code',
                        ],
                        'default',
                    ],
                    'pimCategory' => $category,
                ],
            ],
            'update'    => [],
            'move'      => [],
            'variation' => [],
        ]);
    }

    function it_normalizes_a_new_category_without_generating_an_url_key(
        Category $category,
        Category $parentCategory,
        $categoryMapping,
        $categoryMappingManager
    ) {
        $this->globalContext['urlKey'] = true;

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(null);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn([
            'create'    => [
                [
                    'magentoCategory' => [
                        '3',
                        [
                            'name'              => 'category_label',
                            'is_active'         => 1,
                            'include_in_menu'   => 1,
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                        ],
                        'default',
                    ],
                    'pimCategory' => $category,
                ],
            ],
            'update'    => [],
            'move'      => [],
            'variation' => [],
        ]);
    }

    function it_normalizes_an_updated_category(
        Category $category,
        Category $parentCategory,
        $categoryMapping,
        $categoryMappingManager
    ) {
        $this->globalContext = array_merge(
            $this->globalContext,
            [
                'magentoCategories' => [
                    4 => ['parent_id' => 3],
                ],
                'magentoStoreView' => 'default'
            ]
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->getLeft()->willReturn(7);
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn([
            'create'    => [],
            'update'    => [
                [
                    4,
                    [
                        'name'              => 'category_label',
                        'available_sort_by' => 1,
                        'default_sort_by'   => 1,
                        'is_anchor'         => 1,
                        'position'          => 7,
                        'url_key'           => 'category-label-category-code',
                    ],
                    'default',
                ],
            ],
            'move'      => [],
            'variation' => [],
        ]);
    }

    function it_normalizes_an_updated_category_without_generating_url_key(
        Category $category,
        Category $parentCategory,
        $categoryMapping,
        $categoryMappingManager
    ) {
        $this->globalContext = array_merge(
            $this->globalContext,
            [
                'magentoCategories' => [
                    4 => ['parent_id' => 3],
                ],
                'magentoStoreView' => 'default'
            ]
        );

        $this->globalContext['urlKey'] = true;

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->getLeft()->willReturn(7);
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn([
            'create'    => [],
            'update'    => [
                [
                    4,
                    [
                        'name'              => 'category_label',
                        'available_sort_by' => 1,
                        'default_sort_by'   => 1,
                        'is_anchor'         => 1,
                        'position'          => 7,
                    ],
                    'default',
                ],
            ],
            'move'      => [],
            'variation' => [],
        ]);
    }

    function it_normalizes_an_updated_category_who_have_moved(
        Category $category,
        Category $parentCategory,
        Category $prevCategory,
        $categoryMapping,
        $categoryMappingManager,
        $categoryRepository
    ) {
        $this->globalContext = array_merge(
            $this->globalContext,
            [
                'magentoCategories' => [
                    4 => ['parent_id' => 3],
                ],
                'magentoStoreView' => 'default'
            ]
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');
        $category->getLeft()->willReturn(7);

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(9);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryRepository->getPrevSiblings($category)->willReturn([$prevCategory]);
        $categoryMappingManager->getIdFromCategory($prevCategory, 'soap_url', $categoryMapping)->willReturn(5);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(
            [
                'create'    => [],
                'update'    => [
                    [
                        4,
                        [
                            'name'              => 'category_label',
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                            'is_anchor'         => 1,
                            'position'          => 7,
                            'url_key'           => 'category-label-category-code',
                        ],
                        'default',
                    ],
                ],
                'move'      => [
                    [
                        4,
                        9,
                        5,
                    ],
                ],
                'variation' => [],
            ]
        );
    }

    function it_normalizes_an_updated_category_who_have_moved_without_generating_url_key(
        Category $category,
        Category $parentCategory,
        Category $prevCategory,
        $categoryMapping,
        $categoryMappingManager,
        $categoryRepository
    ) {
        $this->globalContext = array_merge(
            $this->globalContext,
            [
                'magentoCategories' => [
                    4 => ['parent_id' => 3],
                ],
                'magentoStoreView' => 'default'
            ]
        );

        $this->globalContext['urlKey'] = true;

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([]);
        $category->getCode()->willReturn('category_code');
        $category->getLeft()->willReturn(7);

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(9);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryRepository->getPrevSiblings($category)->willReturn([$prevCategory]);
        $categoryMappingManager->getIdFromCategory($prevCategory, 'soap_url', $categoryMapping)->willReturn(5);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(
            [
                'create'    => [],
                'update'    => [
                    [
                        4,
                        [
                            'name'              => 'category_label',
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                            'is_anchor'         => 1,
                            'position'          => 7,
                        ],
                        'default',
                    ],
                ],
                'move'      => [
                    [
                        4,
                        9,
                        5,
                    ],
                ],
                'variation' => [],
            ]
        );
    }

    function it_normalizes_category_variations(
        Category $category,
        Category $parentCategory,
        CategoryTranslation $translation,
        $categoryMapping, $storeViewMapping,
        $categoryMappingManager
    ) {
        $this->globalContext = array_merge(
            $this->globalContext,
            [
                'magentoStoreViews' => [
                    ['code' => 'fr_fr'],
                ],
                'magentoStoreView' => 'default'
            ]
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn([$translation]);
        $category->getCode()->willReturn('category_code');

        $translation->getLocale()->willReturn('fr_FR');

        $storeViewMapping->getTarget('fr_FR')->willReturn('fr_fr');
        $category->setLocale('fr_FR')->shouldBeCalled();
        $category->getLabel()->willReturn('Libélé de la catégorie');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(null);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn([
            'create'    => [
                [
                    'magentoCategory' => [
                        '3',
                        [
                            'name'              => 'Libélé de la catégorie',
                            'is_active'         => 1,
                            'include_in_menu'   => 1,
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                            'url_key'           => 'libele-de-la-categorie-category-code',
                        ],
                        'default',
                    ],
                    'pimCategory' => $category,
                ],
            ],
            'update'    => [],
            'move'      => [],
            'variation' => [
                [
                    'magentoCategory' => [
                        null,
                        [
                            'name'              => 'Libélé de la catégorie',
                            'url_key'           => 'libele-de-la-categorie-category-code',
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                        ],
                        'fr_fr',
                    ],
                    'pimCategory' => $category,
                ],
            ],
        ]);
    }
}
