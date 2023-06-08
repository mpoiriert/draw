<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Draw\Bundle\SonataExtraBundle\ListPriorityAwareAdminInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFieldPriorityExtension extends AbstractAdminExtension
{
    public function __construct(private ?int $defaultMaxField = null, private array $defaultFieldPriorities = [])
    {
    }

    private function getOptions(AdminInterface $admin): array
    {
        $options = [];
        if ($admin instanceof ListPriorityAwareAdminInterface) {
            $options = $admin->getListFieldPriorityOptions();
        }

        return (new OptionsResolver())
            ->setDefaults([
                'defaultMaxField' => $this->defaultMaxField,
                'defaultFieldPriorities' => [],
            ])
            ->resolve($options);
    }

    public function configureListFields(ListMapper $list): void
    {
        $options = $this->getOptions($list->getAdmin());
        $defaultMaxField = $options['defaultMaxField'];

        if (null === $defaultMaxField) {
            return;
        }

        $defaultFieldPriorities = array_merge($this->defaultFieldPriorities, $options['defaultFieldPriorities']);

        $fieldPriorities = [];
        foreach ($list->keys() as $key) {
            $priority = $list->get($key)->getOption('priority');

            if ($this->isInFilter($key, $list->getAdmin())) {
                // Remove the key from default priority to make sure it show up
                unset($defaultFieldPriorities[$key]);
                continue;
            }

            if (null === $priority) {
                continue;
            }

            // Remove the key from default priority because it's set
            unset($defaultFieldPriorities[$key]);

            $fieldPriorities[(int) $priority][] = $key;
        }

        foreach ($defaultFieldPriorities as $key => $priority) {
            $fieldPriorities[(int) $priority][] = $key;
        }

        ksort($fieldPriorities);

        $fieldPriorities = array_merge(...$fieldPriorities);

        $count = \count($list->keys());

        while ($count > $defaultMaxField && \count($fieldPriorities) > 0) {
            $key = array_shift($fieldPriorities);
            if (!$list->has($key)) {
                continue;
            }

            $list->remove($key);
            --$count;
        }
    }

    private function isInFilter(string $key, AdminInterface $admin): bool
    {
        foreach ($admin->getFilterParameters() as $filter => $data) {
            if ($filter !== $key) {
                continue;
            }

            return true;
        }

        return false;
    }
}
