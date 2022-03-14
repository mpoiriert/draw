<?php

namespace Draw\Bundle\ApplicationBundle\Configuration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\ApplicationBundle\Configuration\Entity\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;

class ConfigRepository extends ServiceEntityRepository implements ConfigurationRegistryInterface
{
    /**
     * @var Config[]
     */
    private $configs = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    public function get(string $name, $default = null)
    {
        if (!isset($this->configs[$name])) {
            $this->configs[$name] = $this->find($name);
        } else {
            if (UnitOfWork::STATE_MANAGED !== $this->_em->getUnitOfWork()->getEntityState($this->configs[$name])) {
                unset($this->configs[$name]);

                return $this->get($name, $default);
            }
            $this->_em->refresh($this->configs[$name]);
        }

        return isset($this->configs[$name]) ? $this->configs[$name]->getValue() : $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->configs) || null !== $this->find($name);
    }

    public function set(string $name, $value): void
    {
        $config = $this->find($name);
        if (null === $config) {
            $config = new Config();
            $config->setId($name);
            $this->_em->persist($config);
        }

        $config->setValue($value);

        $this->_em->flush();
    }

    public function delete(string $name): void
    {
        $config = $this->find($name);
        if ($config) {
            $this->_em->remove($config);
            $this->_em->flush();
        }
        unset($this->configs[$name]);
    }
}
