<?php

namespace RoarProj\controllers\middlewares;

use Neomerx\JsonApi\Contracts\Document\DocumentInterface as Doc;
use Neomerx\JsonApi\Contracts\Http\Query\QueryCheckerInterface;
use Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface as QueryParam;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\JsonApi\Http\Request as JsonApiRequest;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as V;

class JsonApiValidator extends RequestValidator
{

    const ATTR_FIELDS = 'jsonapi_fields';
    const ATTR_JSONAPI_PARAMS = 'jsonapi_params';

    const FIELD_DEFAULTS = 'defaultFieldValues';

    const SCHEMA_ATTRIBUTES = 'attributesSchema';
    const SCHEMA_RELATIONSHIPS = 'relationshipsSchema';
    const SCHEMA_ID = 'idSchema';

    const SCHEMA_QUERY_FILTER = 'queryFilterSchema';
    const SCHEMA_QUERY_PAGINATION = 'queryPaginationSchema';
    const SCHEMA_QUERY_INCLUDES = 'queryIncludesSchema';
    const SCHEMA_QUERY_SORTS = 'querySortsSchema';

    const EXT_BULK_ENABLED = 'extensionBulkEnabled';

    const ALLOWED_INCLUDES = 'allowedIncludes';
    const ALLOWED_FIELDS = 'allowedFields';
    const ALLOWED_FILTERS = 'allowedFilters';
    const ALLOWED_SORT = 'allowedSort';

    /**
     * JsonApiValidator constructor.
     *
     * @param string $type JSONAPI resource type name
     * @param array $options
     */
    public function __construct($type, $options = [])
    {
        //Type
        $jsonapiType = $type;
        $jsonapiId = self::getOption($options, self::SCHEMA_ID);
        $jsonapiAttr = self::getOption($options, self::SCHEMA_ATTRIBUTES);
        $jsonapiRel = self::getOption($options, self::SCHEMA_RELATIONSHIPS);
        $jsonapiBulk = self::getOption($options, self::EXT_BULK_ENABLED, false);
        $jsonapiFilters = self::getOption($options, self::SCHEMA_QUERY_FILTER);
        $jsonapiPages = self::getOption($options, self::SCHEMA_QUERY_PAGINATION);
        $jsonapiIncludes = self::getOption($options, self::SCHEMA_QUERY_INCLUDES);
        $jsonapiSorts = self::getOption($options, self::SCHEMA_QUERY_SORTS);

        $bodySchema = self::generateBodyValidator(
            $jsonapiType,
            $jsonapiId,
            $jsonapiAttr,
            $jsonapiRel,
            $jsonapiBulk
        );

        $options[RequestValidator::BODY_SCHEMA] = $bodySchema;

        $querySchema = self::generateQueryValidator(
            $jsonapiFilters,
            $jsonapiPages,
            $jsonapiIncludes,
            $jsonapiSorts
        );

        $options[RequestValidator::QUERY_SCHEMA] = $querySchema;

        $this->isBulk = $jsonapiBulk;

        $this->queryChecker = (new Factory())->createQueryChecker(
            false,
            self::getOption($options, self::ALLOWED_INCLUDES),
            self::getOption($options, self::ALLOWED_FIELDS),
            self::getOption($options, self::ALLOWED_SORT),
            [],
            self::getOption($options, self::ALLOWED_FILTERS)
        );

        $this->defaultFieldValues = self::getOption(
            $options,
            self::FIELD_DEFAULTS,
            []
        );

        parent::__construct($options);
    }

    public function __invoke(Request $request, Application $app)
    {
        $jsonapiRequest = new JsonApiRequest(
        //HTTP Method adapter
            function () use ($request) {
                return $request->getMethod();
            },
            //Header adapter
            function ($name) use ($request) {
                return $request->headers->get($name);
            },
            //MetricQuery parameters adapter
            function () use ($request) {
                return $request->query->all();
            }
        );

        $params = (new Factory())
            ->createQueryParametersParser()
            ->parse($jsonapiRequest);

        //This will throw if the query is invalid somehow.
        $this->queryChecker->checkQuery($params);

        //If query string is valid for jsonapi, set it inside a special
        //parameter that the jsonapi serializer can access.
        $request->attributes->set(self::ATTR_JSONAPI_PARAMS, $params);

        //Yield for the rest of the validation.
        parent::__invoke($request, $app);

        //Add the "fields"
        $request->attributes->set(
            self::ATTR_FIELDS,
            $this->toFieldValues($request->request->all())
        );
    }

    /**
     * Helper method that returns a validation schema for a resource handle.
     *
     * @param string|null $type validates that the related resource has this
     *                          type if set.
     *
     * @return V\Collection
     */
    static public function ResourceHandle($type = null, $idSchema = NUL)
    {
        $typeSchema = isset($type) ? new V\IdenticalTo($type) :
            new V\Type('string');

        $idSchema = $idSchema ? $idSchema : new V\Type('string');

        return new V\Collection(
            [
                Doc::KEYWORD_TYPE => $typeSchema,
                Doc::KEYWORD_ID   => $idSchema
            ]
        );
    }

    /**
     *
     * Helper method that returns a validation schema for a one to one
     * relationship.
     *
     * @param string|null $type validates that the related resource has this
     *                          type if set.
     *
     * @return V\Collection
     */
    static public function OneToOne($type = null, $idSchema = null)
    {
        return new V\Collection(
            [
                Doc::KEYWORD_DATA => self::ResourceHandle($type, $idSchema)
            ]
        );
    }

    /**
     *
     * Helper method that returns a validation schema for a one to many
     * relationship.
     *
     * @param string|null $type validates that the related resources have this
     *                          type if set.
     *
     * @return V\Collection
     */
    static public function OneToMany($type = null, $idSchema = null)
    {
        return new V\Collection(
            [
                Doc::KEYWORD_DATA => new V\All(
                    self::ResourceHandle($type, $idSchema)
                )
            ]
        );
    }

    /**
     * This method takes the nested JSON body structure of the JSONAPI
     * request and flattens it into a fields array. (ie: per the JSONAPI
     * spec, it combines the attributes, relationships, types and id together.
     *
     *
     * @param array $body
     *
     * @return array The flattened fields array
     */
    private function toFieldValues(array $body = [])
    {
        //Can't flatten sh*t if there's nothing to flatten...
        //bail out in this case.
        if (!isset($body[Doc::KEYWORD_DATA])) {
            return [];
        }

        $data = $body[Doc::KEYWORD_DATA];

        if (!$this->isBulk) {
            return $this->flattenResource($data);
        } else {
            return array_map(
                function ($resource) {
                    return $this->flattenResource($resource);
                },
                $data
            );
        }
    }

    private function flattenResource(array $data = [])
    {
        $fields = [];

        //Id
        if (isset($data[Doc::KEYWORD_ID])) {
            $fields[Doc::KEYWORD_ID] = $data[Doc::KEYWORD_ID];
        }

        //Type
        $fields[Doc::KEYWORD_TYPE] = $data[Doc::KEYWORD_TYPE];

        //Attributes
        if (isset($data[Doc::KEYWORD_ATTRIBUTES]) &&
            count($data[Doc::KEYWORD_ATTRIBUTES]) > 0) {
            $fields = array_merge($fields, $data[Doc::KEYWORD_ATTRIBUTES]);
        }

        //Relationships - extract the data part of the relationship only.
        if (isset($data[Doc::KEYWORD_RELATIONSHIPS]) &&
            count($data[Doc::KEYWORD_RELATIONSHIPS]) > 0) {
            $rels = $data[Doc::KEYWORD_RELATIONSHIPS];

            foreach ($rels as $relName => $relValue) {
                if (isset($relValue[Doc::KEYWORD_DATA])) {
                    $fields[$relName] = $relValue[Doc::KEYWORD_DATA];
                }
            }
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults($this->defaultFieldValues);
        $resolver->setDefined(array_keys($fields));

        //Set default values if any fields are missing.
        return $resolver->resolve($fields);
    }

    private static function generateBodyValidator(
        $type,
        $id = null,
        $attributesSchema = null,
        $relationshipsSchema = null,
        $bulkExt = false
    ) {
        //Only return a validation schema if one of the overridable parts of
        //the schema were specified.
        if ($id === null &&
            $attributesSchema === null &&
            $relationshipsSchema === null) {
            return null;
        }

        $dataSchema = new V\Collection(
            [
                Doc::KEYWORD_TYPE => new V\IdenticalTo($type),

                Doc::KEYWORD_ID => $id !== null ? $id : new V\Optional(),

                Doc::KEYWORD_ATTRIBUTES => $attributesSchema !== null ?
                    $attributesSchema : new V\Optional(),

                Doc::KEYWORD_RELATIONSHIPS => $relationshipsSchema !== null ?
                    $relationshipsSchema : new V\Optional(),

                Doc::KEYWORD_META => new V\Optional()
            ]
        );

        return new V\Collection(
            [
                Doc::KEYWORD_DATA => $bulkExt ? new V\All($dataSchema) :
                    $dataSchema,
                Doc::KEYWORD_META => new V\Optional()
            ]
        );
    }

    private static function generateQueryValidator(
        $filterSchema,
        $paginationSchema,
        $includesSchema,
        $sortsSchema
    ) {
        return new V\Collection(
            [
                'fields' => [
                    QueryParam::PARAM_FILTER => $filterSchema !== null ?
                        [new V\Type('array'), $filterSchema] :
                        new V\Optional(),

                    QueryParam::PARAM_PAGE => $paginationSchema !== null ?
                        new V\Optional([new V\Type('array'), $paginationSchema]) :
                        new V\Optional(),

                    QueryParam::PARAM_SORT => $sortsSchema !== null ?
                        new V\Optional([new V\Type('array'), $sortsSchema]) :
                        new V\Optional(),

                    QueryParam::PARAM_INCLUDE => $includesSchema !== null ?
                        new V\Optional([new V\Type('array'), $includesSchema]) :
                        new V\Optional(),

                    QueryParam::PARAM_FIELDS => new V\Optional(new V\Type('array'))
                ],

                'allowExtraFields'   => false,
                'allowMissingFields' => false
            ]
        );
    }

    /**
     * Small utility that checks if an option is set and returns null
     * otherwise, then unsets the option from the option map. Moved in a
     * private static helper method because we have a lot of code doing this
     * in the constructor.
     *
     * @param array $options
     * @param string $key
     * @param mixed $default The default value to insert if not set.
     *
     * @return null
     */
    private static function getOption(array &$options, $key, $default = null)
    {
        $val = isset($options[$key]) ? $options[$key] : $default;
        unset($options[$key]);

        return $val;
    }

    /**
     * @var QueryCheckerInterface
     */
    private $queryChecker;

    /**
     * @var boolean
     */
    private $isBulk;

    /**
     * @var array
     */
    private $defaultFieldValues;
}