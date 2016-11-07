<?php

namespace FYS\DatatableBundle\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class RequestHandler
{
    public $entityManager;
    public $search;
    public $orderDir;
    public $orderBy;
    public $start;
    public $length;
    public $columns = array();

    /**
     * Class Constructor, holds EntityManager object.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * process current datatable server side operation.
     *
     * @param Request $request
     * @param $entity Entity name
     * @param array $columns
     *
     * @return array
     */
    public function process(Request $request, $entity, array $columns)
    {
        $em = $this->entityManager;
        $allias = 'e';

        $this->columns = $columns;
        $this->buildParameter($request);

        $query = $em->getRepository($entity)->createQueryBuilder($allias)->select($allias);

        foreach ($this->columns as $column) {
            $query->orWhere("$allias.$column LIKE :search");
        }

        $query->setParameter('search', '%'.$this->search.'%');
        $query->orderBy("$allias.$this->orderBy", $this->orderDir);

        $recordsFiltered = $query->getQuery()->getResult();

        $query->setFirstResult($this->start);
        $query->setMaxResults($this->length);

        $unNormalizedData = $query->getQuery()->getResult();

        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers);

        $normalizedData = $serializer->normalize($unNormalizedData);

        $response = array(
            'data' => $normalizedData,
            'recordsTotal' => count($recordsFiltered),
            'recordsFiltered' => count($recordsFiltered),
        );

        return $response;
    }

    /**
     * Build parameters sent by Datatable Server Side.
     *
     * @param Request $request
     */
    private function buildParameter(Request $request)
    {
        $post = $request->request;
        $orderDirKey = $post->get('order')[0]['column'];

        $this->search = $post->get('search')['value'];
        $this->orderDir = $post->get('order')[0]['dir'];
        $this->orderBy = $this->columns[$orderDirKey];
        $this->start = $post->get('start');
        $this->length = $post->get('length');
    }
}
