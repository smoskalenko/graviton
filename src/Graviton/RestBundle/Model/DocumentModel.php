<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Graviton\SchemaBundle\Model\SchemaModel;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\RqlParserBundle\Factory;
use Graviton\I18nBundle\Service\I18nUtils;

/**
 * Use doctrine odm as backend
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentModel extends SchemaModel implements ModelInterface
{
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string[]
     */
    protected $fieldTitles;
    /**
     * @var string[]
     */
    protected $fieldDescriptions;
    /**
     * @var string[]
     */
    protected $requiredFields = array();
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * @var Factory
     */
    private $rqlFactory;

    /**
     * @var I18nUtils
     */
    private $translatorUtils;

    /**
     * @param Factory   $rqlFactory      factory object to use
     * @param I18nUtils $translatorUtils translations utils, optional for rare cases that would be circular else
     */
    public function __construct(Factory $rqlFactory, I18nUtils $translatorUtils = null)
    {
        parent::__construct();
        $this->rqlFactory = $rqlFactory;
        $this->translatorUtils = $translatorUtils;
    }

    /**
     * get repository instance
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * create new app model
     *
     * @param \Doctrine\Common\Persistence\ObjectRepository $repository Repository of countries
     *
     * @return \Graviton\RestBundle\Model\DocumentModel
     */
    public function setRepository(ObjectRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return array
     */
    public function findAll(Request $request)
    {
        $pageNumber = $request->query->get('page', 1);
        $numberPerPage = (int) $request->query->get('perPage', 10);
        $startAt = ($pageNumber - 1) * $numberPerPage;

        /** @var \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder */
        $queryBuilder = $this->repository
            ->createQueryBuilder();

        // *** do we have an RQL expression, do we need to filter data?
        $filter = $request->query->get('q');
        if (!empty($filter)) {
            // set filtering attributes on request
            $request->attributes->set('filtering', true);

            $queryBuilder = $this->doRqlQuery($queryBuilder, $filter);

        } else {
            // @todo [lapistano]: seems the offset is missing for this query.
            /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
            $queryBuilder->find($this->repository->getDocumentName());
        }

        // define offset and limit
        if (!array_key_exists('skip', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->skip($startAt);
        }

        if (!array_key_exists('limit', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->limit($numberPerPage);
        } else {
            $numberPerPage = (int) $queryBuilder->getQuery()->getQuery()['limit'];
        }

        /**
         * make sure we search using the english variant in strings
         */
        $this->translateQuery($queryBuilder);

        /**
         * add a default sort on id if none was specified earlier
         *
         * not specifying something to sort on leads to very weird cases when fetching references
         */
        if (!array_key_exists('sort', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->sort('_id');
        }

        // run query
        $query = $queryBuilder->getQuery();
        $records = array_values($query->execute()->toArray());

        $totalCount = $query->count();
        $numPages = (int) ceil($totalCount / $numberPerPage);
        if ($numPages > 1) {
            $request->attributes->set('paging', true);
            $request->attributes->set('numPages', $numPages);
            $request->attributes->set('perPage', $numberPerPage);
        }

        return $records;
    }

    /**
     * @param \Graviton\I18nBundle\Document\Translatable $entity entityy to insert
     *
     * @return Object
     */
    public function insertRecord($entity)
    {
        $manager = $this->repository->getDocumentManager();
        $manager->persist($entity);
        $manager->flush();

        return $this->find($entity->getId());
    }

    /**
     * @param string $documentId id of entity to find
     *
     * @return Object
     */
    public function find($documentId)
    {
        return $this->repository->find($documentId);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $documentId id of entity to update
     * @param Object $entity     new entity
     *
     * @return Object
     */
    public function updateRecord($documentId, $entity)
    {
        $manager = $this->repository->getDocumentManager();
        $entity = $manager->merge($entity);
        $manager->flush();

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $documentId id of entity to delete
     *
     * @return null|Object
     */
    public function deleteRecord($documentId)
    {
        $manager = $this->repository->getDocumentManager();
        $entity = $this->find($documentId);

        $return = $entity;
        if ($entity) {
            $manager->remove($entity);
            $manager->flush();
            $return = null;
        }

        return $return;
    }

    /**
     * get classname of entity
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->repository->getDocumentName();
    }

    /**
     * {@inheritDoc}
     *
     * Currently this is being used to build the route id used for redirecting
     * to newly made documents. It might benefit from having a different name
     * for those purposes.
     *
     * We might use a convention based mapping here:
     * Graviton\CoreBundle\Document\App -> mongodb://graviton_core
     * Graviton\CoreBundle\Entity\Table -> mysql://graviton_core
     *
     * @todo implement this in a more convention based manner
     *
     * @return string
     */
    public function getConnectionName()
    {
        $bundle = strtolower(substr(explode('\\', get_class($this))[1], 0, -6));

        return 'graviton.' . $bundle;
    }

    /**
     * pretranslate query strings so we can search by their inglish variant
     *
     * @param Builder $queryBuilder doctrine query builder
     *
     * @return void
     */
    public function translateQuery(Builder &$queryBuilder)
    {
        if (is_null($this->translatorUtils)) {
            return;
        }
        if (in_array(
            'Graviton\I18nBundle\Document\TranslatableDocumentInterface',
            class_implements($this->repository->getDocumentName())
        )) {
            $docName = $this->repository->getDocumentName();
            $docInstance = new $docName;
            $translatableFields = $docInstance->getTranslatableFields();

            foreach ($queryBuilder->getQuery()->getFieldsInQuery() as $queryField) {
                if (!in_array($queryField, $translatableFields)) {
                    continue;
                }
                /**
                 * @todo rewrite the query builder to get the actual value we should search by (not sure how)
                 *
                 * I'll probably use the translatable search part and apply it to the translatable doc and
                 * then just do a plain search on the current model.
                 *
                 * This would mean that we always search using fully string on the doc and I need to figure
                 * out what rammifications this has on thinks like 'like()' and 'in()' queries...
                 */
            }
        }
    }

    /**
     * Does the actual query using the RQL Bundle.
     *
     * @param Builder $queryBuilder Doctrine ODM QueryBuilder
     * @param string  $rqlQuery     raw query string
     *
     * @return array
     */
    protected function doRqlQuery($queryBuilder, $rqlQuery)
    {
        $factory = $this->rqlFactory;

        $query = $factory
            ->create('MongoOdm', $rqlQuery, $queryBuilder);

        return $query->buildQuery();
    }
}
