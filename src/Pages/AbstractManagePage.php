<?php

namespace Phntm\Lib\Pages;

use Doctrine\ORM\EntityManager;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Http\Redirect;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;


abstract class AbstractManagePage extends AbstractPage implements Manageable
{
    protected string $entityClass;

    protected ?object $entity = null;

    final public function render(Request $request): StreamInterface
    {
        $this->getEntityInstance();

        if ($request->getMethod() === 'POST') {
            $this->entity = $this->updateEntity($request);
            
            throw new Redirect($request->getUri(), 302);
        }

        $entity = new \ReflectionClass($this->entityClass);
        $entity = $entity->getShortName();

        $this->renderWith(['entity' => $entity]);
        $this->renderWith(['form' => $this->formSchema()]);

        return parent::render($request);
    }

    protected function getEntityInstance(): object
    {
        $em = Db::getEntityManager();
        $repo = $em->getRepository($this->entityClass);

        if ($id = $this->resolveEntityId($em)) {
            $this->entity = $repo->find($id);
        }

        if (!$this->entity) {
            dump('Creating new entity');
            $this->entity = new $this->entityClass();

            $defaults = $this->getEntityDefaults($this->entity);
            dump($defaults);
            foreach ($defaults as $property => $value) {
                $property = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));

                $setter = 'set' . $property;
                $this->entity->$setter($value);
            }
            dd($this->entity);

            $em->persist($this->entity);
            $em->flush();
            $this->entity = $repo->find($this->resolveEntityId($em));
        }

        return $this->entity;
    }

    protected function getEntityDefaults(object $entity): array
    {
        $defaults = [];
        $reflection = new \ReflectionClass($entity);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $input = $property->getAttributes(\Phntm\Lib\Inputs\Input::class);
            if (empty($input)) {
                continue;
            }

            $input = $input[0]->newInstance();
            $defaults[$property->getName()] = $input->default;
        }

        return $defaults;
    }

    protected function resolveEntityId(EntityManager $em): ?int
    {
        return 1;
    }

    public function formSchema(): array
    {
        $form = [];
        $reflection = new \ReflectionClass($this->entityClass);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $input = $property->getAttributes(\Phntm\Lib\Inputs\Input::class);
            if (empty($input)) {
                continue;
            }

            $input = $input[0]->newInstance();
            $form[$property->getName()] = $input->getAttributes($property);
            $propertyGet = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property->getName())));
            $form[$property->getName()]['value'] = $this->entity->$propertyGet();
        }

        return $form;
    }

    protected function updateEntity(Request $request): object
    {
        $entity = $this->entity;

        $reflection = new \ReflectionClass($this->entityClass);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $input = $property->getAttributes(\Phntm\Lib\Inputs\Input::class);
            if (empty($input)) {
                continue;
            }

            $input = $input[0]->newInstance();
            $setter = 'set' . ucfirst($property->getName());
            $entity->$setter($request->request->get($property->getName()));
        }

        $em = Db::getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }
}
