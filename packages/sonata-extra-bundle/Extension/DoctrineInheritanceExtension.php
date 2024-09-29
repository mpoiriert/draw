<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;

class DoctrineInheritanceExtension extends AbstractAdminExtension
{
    /**
     * @param ProxyQueryInterface&QueryBuilder $query
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void
    {
        if (!$this->hasSubClasses($admin)) {
            return;
        }

        if ($class = $this->getFilteredClass($admin)) {
            $rootAlias = $query->getRootAliases()[0];
            $query->resetDQLPart('from');
            $query->from($class, $rootAlias);
        }
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->filterOut($filter);
    }

    private function hasSubClasses(AdminInterface $admin): bool
    {
        return 0 !== \count($admin->getSubClasses());
    }

    private function getFilteredClass(AdminInterface $admin): ?string
    {
        $subClasses = $admin->getSubClasses();

        foreach ($admin->getFilterParameters() as $data) {
            if (!isset($data['value'])) {
                continue;
            }

            if (!\is_string($data['value'])) {
                continue;
            }

            if (!\in_array($data['value'], $subClasses, true)) {
                continue;
            }

            if (($data['type'] ?? EqualOperatorType::TYPE_EQUAL) !== EqualOperatorType::TYPE_EQUAL) {
                continue;
            }

            return $data['value'];
        }

        return null;
    }

    private function filterOut(ListMapper|DatagridMapper $mapper): void
    {
        $admin = $mapper->getAdmin();

        if (!$this->hasSubClasses($admin)) {
            return;
        }

        $subClass = $this->getFilteredClass($admin);

        foreach ($mapper->keys() as $key) {
            $forClasses = $mapper->get($key)->getOption('forClasses');

            if (null === $forClasses) {
                continue;
            }

            if (!\is_array($forClasses)) {
                $forClasses = [$forClasses];
            }

            if (!\in_array($subClass, $forClasses, true)) {
                $mapper->remove($key);
            }
        }
    }

    public function configureListFields(ListMapper $list): void
    {
        $this->filterOut($list);
    }
}
